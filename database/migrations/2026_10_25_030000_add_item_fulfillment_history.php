<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('vendor_id')->constrained();
            $t->foreignId('shipment_id')->constrained();
            $t->foreignId('order_item_id')->constrained();
            $t->unsignedInteger('quantity');
            $t->unique(['shipment_id', 'order_item_id']);
            $t->timestamps();
        });
        Schema::create('shipment_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('vendor_id')->constrained();
            $t->foreignId('shipment_id')->constrained();
            $t->string('reference')->unique();
            $t->string('status');
            $t->string('source')->default('operator');
            $t->timestamp('occurred_at');
            $t->timestamps();
        });
        DB::table('shipments')->orderBy('id')->chunkById(100, function ($shipments) {
            foreach ($shipments as $shipment) {
                foreach (DB::table('order_items')->where('vendor_order_id', $shipment->vendor_order_id)->get() as $item) {
                    DB::table('shipment_items')->insert(['tenant_id' => $shipment->tenant_id, 'vendor_id' => $shipment->vendor_id, 'shipment_id' => $shipment->id, 'order_item_id' => $item->id, 'quantity' => $item->quantity, 'created_at' => $shipment->created_at, 'updated_at' => $shipment->created_at]);
                }DB::table('shipment_events')->insert(['tenant_id' => $shipment->tenant_id, 'vendor_id' => $shipment->vendor_id, 'shipment_id' => $shipment->id, 'reference' => 'shipment:'.$shipment->id.':backfill', 'status' => $shipment->status, 'source' => 'historical operator record', 'occurred_at' => $shipment->delivered_at ?? $shipment->created_at, 'created_at' => $shipment->created_at, 'updated_at' => $shipment->created_at]);
            }
        });
        foreach (['shipment_items', 'shipment_events', 'subscription_invoices'] as $table) {
            if (DB::getDriverName() === 'pgsql') {
                DB::unprepared("CREATE TRIGGER immutable_{$table} BEFORE UPDATE OR DELETE ON {$table} FOR EACH ROW EXECUTE FUNCTION marketplace_immutable_event()");
            } elseif (DB::getDriverName() === 'sqlite') {
                foreach (['UPDATE', 'DELETE'] as $verb) {
                    $key = strtolower($verb);
                    DB::unprepared("CREATE TRIGGER immutable_{$table}_{$key} BEFORE {$verb} ON {$table} BEGIN SELECT RAISE(ABORT, 'Marketplace event history is immutable'); END");
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_events');
        Schema::dropIfExists('shipment_items');
    }
};
