<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_rules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('vendor_id')->nullable()->constrained();
            $t->foreignId('product_id')->nullable()->constrained();
            $t->foreignId('category_id')->nullable()->constrained();
            $t->string('plan_slug')->nullable();
            $t->string('name');
            $t->unsignedInteger('priority')->default(0);
            $t->unsignedInteger('percentage_bps')->default(0);
            $t->unsignedBigInteger('fixed_per_unit_minor')->default(0);
            $t->unsignedBigInteger('minimum_minor')->default(0);
            $t->unsignedBigInteger('maximum_minor')->nullable();
            $t->unsignedBigInteger('from_subtotal_minor')->default(0);
            $t->unsignedBigInteger('to_subtotal_minor')->nullable();
            $t->timestamp('effective_from');
            $t->timestamp('effective_until')->nullable();
            $t->boolean('enabled')->default(true);
            $t->timestamps();
        });
        Schema::create('tax_rules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('product_id')->nullable()->constrained();
            $t->foreignId('category_id')->nullable()->constrained();
            $t->string('name');
            $t->string('country', 2)->nullable();
            $t->unsignedInteger('priority')->default(0);
            $t->unsignedInteger('percentage_bps');
            $t->timestamp('effective_from');
            $t->timestamp('effective_until')->nullable();
            $t->boolean('enabled')->default(true);
            $t->timestamps();
        });
        Schema::create('shipping_rules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('vendor_id')->nullable()->constrained();
            $t->foreignId('branch_id')->nullable()->constrained();
            $t->string('name');
            $t->string('country', 2)->nullable();
            $t->string('method')->default('flat');
            $t->unsignedInteger('priority')->default(0);
            $t->unsignedBigInteger('base_minor')->default(0);
            $t->unsignedBigInteger('rate_minor')->default(0);
            $t->unsignedBigInteger('free_from_minor')->nullable();
            $t->boolean('enabled')->default(true);
            $t->timestamps();
        });
        Schema::table('products', fn (Blueprint $t) => $t->unsignedInteger('weight_grams')->default(0));
        Schema::table('order_items', function (Blueprint $t) {
            $t->foreignId('commission_rule_id')->nullable()->constrained();
            $t->foreignId('tax_rule_id')->nullable()->constrained();
            $t->json('commission_snapshot')->nullable();
            $t->json('tax_snapshot')->nullable();
        });
        Schema::table('orders', fn (Blueprint $t) => $t->json('shipping_snapshot')->nullable());
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $t) => $t->dropColumn('shipping_snapshot'));
        Schema::table('order_items', function (Blueprint $t) {
            $t->dropConstrainedForeignId('commission_rule_id');
            $t->dropConstrainedForeignId('tax_rule_id');
            $t->dropColumn(['commission_snapshot', 'tax_snapshot']);
        });
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('weight_grams'));
        Schema::dropIfExists('shipping_rules');
        Schema::dropIfExists('tax_rules');
        Schema::dropIfExists('commission_rules');
    }
};
