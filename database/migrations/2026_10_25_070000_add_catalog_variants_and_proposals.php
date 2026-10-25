<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('product_id')->constrained();
            $t->string('name');
            $t->string('code');
            $t->string('barcode')->nullable();
            $t->json('attributes');
            $t->unsignedInteger('weight_grams')->nullable();
            $t->unique(['tenant_id', 'code']);
            $t->timestamps();
        });
        Schema::table('offers', fn (Blueprint $t) => $t->foreignId('product_variant_id')->nullable()->constrained());
        Schema::table('products', fn (Blueprint $t) => $t->json('gallery')->nullable());
        Schema::table('order_items', fn (Blueprint $t) => $t->json('variant_snapshot')->nullable());
        Schema::create('product_proposals', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('vendor_id')->constrained();
            $t->foreignId('category_id')->constrained();
            $t->foreignId('brand_id')->nullable()->constrained();
            $t->foreignId('warehouse_id')->constrained();
            $t->foreignId('product_id')->nullable()->constrained();
            $t->string('name');
            $t->text('description');
            $t->string('sku');
            $t->unsignedBigInteger('price_minor');
            $t->unsignedInteger('initial_quantity')->default(0);
            $t->string('image')->nullable();
            $t->boolean('rights_confirmed')->default(false);
            $t->string('status')->default('draft');
            $t->text('review_notes')->nullable();
            $t->timestamps();
        });
        Schema::create('catalog_imports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('vendor_id')->constrained();
            $t->foreignId('warehouse_id')->constrained();
            $t->foreignId('user_id')->constrained();
            $t->string('name');
            $t->string('path');
            $t->boolean('rights_confirmed');
            $t->string('status')->default('uploaded');
            $t->json('rows')->nullable();
            $t->json('errors')->nullable();
            $t->unsignedInteger('row_count')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_imports');
        Schema::dropIfExists('product_proposals');
        Schema::table('order_items', fn (Blueprint $t) => $t->dropColumn('variant_snapshot'));
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('gallery'));
        Schema::table('offers', fn (Blueprint $t) => $t->dropConstrainedForeignId('product_variant_id'));
        Schema::dropIfExists('product_variants');
    }
};
