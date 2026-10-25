<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('domain')->nullable()->unique();
            $t->string('status')->default('active');
            $t->string('currency', 3)->default('USD');
            $t->string('locale')->default('en');
            $t->string('timezone')->default('UTC');
            $t->string('plan')->default('starter');
            $t->unsignedInteger('branch_limit')->default(2);
            $t->unsignedInteger('commission_bps')->default(1000);
            $t->unsignedInteger('tax_bps')->default(0);
            $t->unsignedBigInteger('shipping_minor')->default(500);
            $t->json('settings')->nullable();
            $t->timestamps();
        });
        Schema::create('vendors', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->string('name');
            $t->string('slug');
            $t->string('email');
            $t->string('status')->default('pending');
            $t->text('description')->nullable();
            $t->unsignedInteger('commission_bps')->nullable();
            $t->unique(['tenant_id', 'slug']);
            $t->timestamps();
        });
        Schema::create('branches', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('vendor_id')->constrained();
            $t->string('name');
            $t->string('address');
            $t->string('status')->default('active');
            $t->timestamps();
        });
        Schema::create('warehouses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('vendor_id')->constrained();
            $t->foreignId('branch_id')->constrained();
            $t->string('name');
            $t->string('location');
            $t->timestamps();
        });
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->string('name');
            $t->string('slug');
            $t->foreignId('parent_id')->nullable()->constrained('categories');
            $t->unique(['tenant_id', 'slug']);
            $t->timestamps();
        });
        Schema::create('brands', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->string('name');
            $t->string('slug');
            $t->unique(['tenant_id', 'slug']);
            $t->timestamps();
        });
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('category_id')->constrained();
            $t->foreignId('brand_id')->nullable()->constrained();
            $t->string('name');
            $t->string('slug');
            $t->text('description');
            $t->string('status')->default('draft');
            $t->string('type')->default('physical');
            $t->string('image')->nullable();
            $t->string('accent')->default('#e8e6df');
            $t->json('specifications')->nullable();
            $t->unique(['tenant_id', 'slug']);
            $t->timestamps();
        });
        Schema::create('offers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('vendor_id')->constrained();
            $t->foreignId('product_id')->constrained();
            $t->string('sku');
            $t->unsignedBigInteger('price_minor');
            $t->unsignedBigInteger('compare_minor')->nullable();
            $t->string('status')->default('active');
            $t->unique(['tenant_id', 'sku']);
            $t->timestamps();
        });
        Schema::create('inventory_balances', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('vendor_id')->constrained();
            $t->foreignId('offer_id')->constrained();
            $t->foreignId('warehouse_id')->constrained();
            $t->unsignedInteger('quantity')->default(0);
            $t->unsignedInteger('reserved')->default(0);
            $t->unsignedInteger('reorder_point')->default(5);
            $t->unique(['offer_id', 'warehouse_id']);
            $t->timestamps();
        });
        Schema::create('inventory_movements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('vendor_id')->constrained();
            $t->foreignId('inventory_balance_id')->constrained();
            $t->integer('quantity_delta');
            $t->integer('reserved_delta')->default(0);
            $t->string('reason');
            $t->string('reference')->unique();
            $t->timestamps();
        });
        Schema::create('carts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('user_id')->nullable()->constrained();
            $t->string('token')->unique();
            $t->string('status')->default('active');
            $t->timestamps();
        });
        Schema::create('cart_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('cart_id')->constrained();
            $t->foreignId('offer_id')->constrained();
            $t->unsignedInteger('quantity');
            $t->unique(['cart_id', 'offer_id']);
            $t->timestamps();
        });
        Schema::create('coupons', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->string('code');
            $t->unsignedInteger('discount_bps');
            $t->unsignedInteger('usage_limit')->default(100);
            $t->unsignedInteger('used')->default(0);
            $t->timestamp('expires_at')->nullable();
            $t->boolean('active')->default(true);
            $t->unique(['tenant_id', 'code']);
            $t->timestamps();
        });
        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('user_id')->nullable()->constrained();
            $t->string('number')->unique();
            $t->string('idempotency_key')->unique();
            $t->foreignId('cart_id')->nullable()->constrained();
            $t->string('status')->default('pending_payment');
            $t->string('currency', 3);
            $t->string('email');
            $t->json('address');
            $t->unsignedBigInteger('subtotal_minor');
            $t->unsignedBigInteger('discount_minor')->default(0);
            $t->unsignedBigInteger('tax_minor')->default(0);
            $t->unsignedBigInteger('shipping_minor')->default(0);
            $t->unsignedBigInteger('total_minor');
            $t->unsignedBigInteger('refunded_minor')->default(0);
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();
        });
        Schema::create('vendor_orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('vendor_id')->constrained();
            $t->foreignId('order_id')->constrained();
            $t->string('status')->default('pending_payment');
            $t->unsignedBigInteger('subtotal_minor');
            $t->unsignedBigInteger('commission_minor');
            $t->unsignedBigInteger('payable_minor');
            $t->unsignedBigInteger('refunded_minor')->default(0);
            $t->unsignedBigInteger('commission_refunded_minor')->default(0);
            $t->unsignedBigInteger('settled_minor')->default(0);
            $t->unique(['order_id', 'vendor_id']);
            $t->timestamps();
        });
        Schema::create('order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('vendor_id')->constrained();
            $t->foreignId('order_id')->constrained();
            $t->foreignId('vendor_order_id')->constrained();
            $t->foreignId('offer_id')->constrained();
            $t->foreignId('inventory_balance_id')->constrained();
            $t->string('name');
            $t->string('sku');
            $t->unsignedInteger('quantity');
            $t->unsignedBigInteger('unit_minor');
            $t->unsignedBigInteger('discount_minor')->default(0);
            $t->unsignedBigInteger('tax_minor')->default(0);
            $t->unsignedBigInteger('total_minor');
            $t->unsignedInteger('commission_bps');
            $t->unsignedBigInteger('commission_minor');
            $t->unsignedInteger('refunded_quantity')->default(0);
            $t->unsignedInteger('shipped_quantity')->default(0);
            $t->timestamps();
        });
        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('order_id')->constrained();
            $t->string('provider')->default('sandbox');
            $t->string('reference')->unique();
            $t->string('status')->default('pending');
            $t->unsignedBigInteger('amount_minor');
            $t->string('currency', 3);
            $t->timestamps();
        });
        Schema::create('payment_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->string('event_id')->unique();
            $t->foreignId('payment_id')->constrained();
            $t->string('type');
            $t->string('payload_hash');
            $t->timestamps();
        });
        Schema::create('shipments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('vendor_id')->constrained();
            $t->foreignId('vendor_order_id')->constrained();
            $t->string('carrier');
            $t->string('tracking');
            $t->string('status')->default('shipped');
            $t->timestamp('delivered_at')->nullable();
            $t->timestamps();
        });
        Schema::create('return_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('vendor_id')->constrained();
            $t->foreignId('order_item_id')->constrained();
            $t->foreignId('user_id')->constrained();
            $t->unsignedInteger('quantity');
            $t->text('reason');
            $t->string('status')->default('requested');
            $t->unsignedBigInteger('amount_minor')->default(0);
            $t->timestamps();
        });
        Schema::create('refunds', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('vendor_id')->constrained();
            $t->foreignId('order_id')->constrained();
            $t->foreignId('order_item_id')->constrained();
            $t->foreignId('return_request_id')->nullable()->constrained();
            $t->string('idempotency_key')->unique();
            $t->unsignedBigInteger('amount_minor');
            $t->unsignedBigInteger('commission_minor');
            $t->string('status')->default('sandbox_confirmed');
            $t->timestamps();
        });
        Schema::create('ledger_transactions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->string('reference')->unique();
            $t->string('description');
            $t->string('currency', 3);
            $t->timestamps();
        });
        Schema::create('ledger_entries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('ledger_transaction_id')->constrained();
            $t->foreignId('vendor_id')->nullable()->constrained();
            $t->string('account');
            $t->bigInteger('amount_minor');
            $t->timestamps();
        });
        Schema::create('settlements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('vendor_id')->constrained();
            $t->foreignId('vendor_order_id')->constrained();
            $t->string('reference')->unique();
            $t->unsignedBigInteger('amount_minor');
            $t->string('status')->default('sandbox_paid');
            $t->timestamps();
        });
        Schema::create('reviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('user_id')->constrained();
            $t->foreignId('product_id')->constrained();
            $t->foreignId('order_item_id')->constrained();
            $t->unsignedTinyInteger('rating');
            $t->string('title');
            $t->text('body');
            $t->unique(['user_id', 'product_id']);
            $t->timestamps();
        });
        Schema::create('wishlists', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('user_id')->constrained();
            $t->foreignId('product_id')->constrained();
            $t->unique(['user_id', 'product_id']);
            $t->timestamps();
        });
        Schema::create('addresses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('user_id')->constrained();
            $t->string('name');
            $t->string('line1');
            $t->string('city');
            $t->string('postal_code');
            $t->string('country', 2);
            $t->timestamps();
        });
        Schema::create('support_tickets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('user_id')->constrained();
            $t->string('subject');
            $t->text('message');
            $t->string('status')->default('open');
            $t->text('reply')->nullable();
            $t->timestamps();
        });
        Schema::create('quotations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('user_id')->constrained();
            $t->foreignId('vendor_id')->constrained();
            $t->foreignId('offer_id')->constrained();
            $t->unsignedInteger('quantity');
            $t->text('notes')->nullable();
            $t->unsignedBigInteger('quoted_minor')->nullable();
            $t->string('status')->default('requested');
            $t->timestamps();
        });
        Schema::create('bookings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('user_id')->constrained();
            $t->foreignId('vendor_id')->constrained();
            $t->foreignId('offer_id')->constrained();
            $t->timestamp('starts_at');
            $t->string('status')->default('confirmed');
            $t->unique(['offer_id', 'starts_at']);
            $t->timestamps();
        });
        Schema::create('purchase_orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('vendor_id')->constrained();
            $t->string('supplier');
            $t->foreignId('offer_id')->constrained();
            $t->foreignId('inventory_balance_id')->constrained();
            $t->unsignedInteger('quantity');
            $t->unsignedBigInteger('unit_cost_minor');
            $t->string('status')->default('draft');
            $t->timestamps();
        });
        Schema::create('employees', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('vendor_id')->constrained();
            $t->foreignId('branch_id')->constrained();
            $t->string('name');
            $t->string('email');
            $t->string('job_title');
            $t->timestamps();
        });
        Schema::create('attendance_records', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('vendor_id')->constrained();
            $t->foreignId('employee_id')->constrained();
            $t->timestamp('clocked_in_at');
            $t->timestamp('clocked_out_at')->nullable();
            $t->string('source')->default('manual');
            $t->timestamps();
        });
        Schema::create('campaigns', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->string('name');
            $t->text('content');
            $t->string('status')->default('draft');
            $t->timestamps();
        });
        Schema::create('ai_drafts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('product_id')->constrained();
            $t->foreignId('user_id')->constrained();
            $t->text('content');
            $t->string('provider')->default('sandbox');
            $t->string('status')->default('pending_review');
            $t->timestamps();
        });
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('user_id')->nullable()->constrained();
            $t->string('action');
            $t->string('subject');
            $t->json('metadata')->nullable();
            $t->timestamps();
        });
        Schema::create('tenant_modules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->string('key');
            $t->boolean('enabled')->default(true);
            $t->json('configuration')->nullable();
            $t->unique(['tenant_id', 'key']);
            $t->timestamps();
        });
        Schema::create('contact_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->string('name');
            $t->string('email');
            $t->string('subject');
            $t->text('message');
            $t->timestamps();
        });
        Schema::create('newsletter_subscribers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->string('email');
            $t->unique(['tenant_id', 'email']);
            $t->timestamps();
        });
        Schema::create('content_pages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->string('slug');
            $t->string('title');
            $t->string('description');
            $t->text('body');
            $t->string('group')->default('company');
            $t->unique(['tenant_id', 'slug']);
            $t->timestamps();
        });
        Schema::table('users', function (Blueprint $t) {
            $t->foreignId('tenant_id')->nullable()->constrained();
            $t->foreignId('vendor_id')->nullable()->constrained();
            $t->foreignId('branch_id')->nullable()->constrained();
            $t->string('role')->default('customer');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['tenant_id', 'vendor_id', 'branch_id', 'role']);
        });
        Schema::dropIfExists('content_pages');
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('tenant_modules');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('ai_drafts');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('settlements');
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('ledger_transactions');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('return_requests');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('payment_events');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('vendor_orders');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('inventory_balances');
        Schema::dropIfExists('offers');
        Schema::dropIfExists('products');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('tenants');
    }
};
