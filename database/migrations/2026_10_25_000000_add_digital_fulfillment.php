<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_assets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('product_id')->constrained();
            $t->string('name');
            $t->string('path');
            $t->boolean('rights_confirmed');
            $t->timestamps();
        });
        Schema::table('order_items', function (Blueprint $t) {
            $t->string('product_type')->default('physical');
            $t->foreignId('digital_asset_id')->nullable()->constrained();
        });
        Schema::create('digital_entitlements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('user_id')->constrained();
            $t->foreignId('order_item_id')->constrained()->unique();
            $t->foreignId('digital_asset_id')->constrained();
            $t->unsignedInteger('downloads')->default(0);
            $t->unsignedInteger('download_limit')->default(3);
            $t->timestamp('expires_at');
            $t->timestamp('revoked_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_entitlements');
        Schema::table('order_items', function (Blueprint $t) {
            $t->dropConstrainedForeignId('digital_asset_id');
            $t->dropColumn('product_type');
        });
        Schema::dropIfExists('digital_assets');
    }
};
