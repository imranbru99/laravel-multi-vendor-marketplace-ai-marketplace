<?php

namespace Tests\Feature;

use App\Domain\ModuleManager;
use App\Domain\TenantContext;
use App\Filament\Resources\OfferResource;
use App\Models\InventoryMovement;
use App\Models\LedgerEntry;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\MarketplaceTestCase;

class SecurityAndHttpTest extends MarketplaceTestCase
{
    public function test_public_home_catalog_product_and_demo_render(): void
    {
        foreach (['/', '/shop', '/shop?q=Product', '/shop?sort=price-asc', '/products/product-0', '/demo', '/platform', '/login', '/register'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_customer_registration_and_login(): void
    {
        $this->post('/register', ['name' => 'New Customer', 'email' => 'new@example.test', 'password' => 'VerySecure123', 'password_confirmation' => 'VerySecure123'])->assertRedirect('/account');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'new@example.test', 'role' => 'customer', 'tenant_id' => $this->tenant->id]);
    }

    public function test_bad_login_returns_validation_error(): void
    {
        $this->post('/login', ['email' => $this->customer->email, 'password' => 'incorrect'])->assertSessionHasErrors('email');
    }

    public function test_customer_can_complete_http_checkout(): void
    {
        $this->actingAs($this->customer)->post('/cart', ['offer_id' => $this->offers[0]->id, 'quantity' => 1])->assertRedirect('/cart');
        $this->get('/cart')->assertOk()->assertSee('Product 0');
        $this->get('/checkout')->assertOk();
        $this->post('/checkout', ['email' => $this->customer->email, 'name' => 'Test', 'line1' => '100 Example', 'city' => 'Example', 'postal_code' => '1000', 'country' => 'US', 'key' => (string) Str::uuid()])->assertRedirect();
        $this->context();
        $order = Order::firstOrFail();
        $this->get('/orders/'.$order->number)->assertOk()->assertSee('Confirm sandbox payment');
        $this->post('/orders/'.$order->number.'/pay')->assertRedirect();
        $this->context();
        $this->assertSame('confirmed', $order->fresh()->status);
        $this->assertSame(0, (int) LedgerEntry::sum('amount_minor'));
    }

    public function test_guest_order_session_controls_access(): void
    {
        $order = $this->place();
        $this->get('/orders/'.$order->number)->assertNotFound();
    }

    public function test_other_customer_cannot_read_order_or_invoice(): void
    {
        $order = $this->place();
        $other = User::create(['name' => 'Other', 'email' => 'other@example.test', 'password' => 'VerySecure123', 'tenant_id' => $this->tenant->id]);
        $this->actingAs($other)->get('/orders/'.$order->number)->assertNotFound();
        $this->get('/orders/'.$order->number.'/invoice')->assertNotFound();
    }

    public function test_invoice_is_actual_pdf(): void
    {
        $order = $this->place();
        $response = $this->actingAs($this->customer)->get('/orders/'.$order->number.'/invoice')->assertOk();
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_tenant_scope_excludes_another_marketplace(): void
    {
        $tenant = Tenant::create(['name' => 'Second', 'slug' => 'second']);
        app(TenantContext::class)->set($tenant);
        $this->assertSame(0, Product::count());
        $this->assertNull(Product::find($this->offers[0]->product_id));
    }

    public function test_cross_tenant_foreign_key_write_is_rejected(): void
    {
        $tenant = Tenant::create(['name' => 'Second', 'slug' => 'second']);
        app(TenantContext::class)->set($tenant);
        $this->expectException(ValidationException::class);
        Offer::create(['vendor_id' => $this->offers[0]->vendor_id, 'product_id' => $this->offers[0]->product_id, 'sku' => 'LEAK', 'price_minor' => 100]);
    }

    public function test_missing_tenant_context_fails_closed(): void
    {
        app(TenantContext::class)->clear();
        $this->expectException(\LogicException::class);
        Product::count();
    }

    public function test_vendor_resource_cannot_query_other_vendor(): void
    {
        $user = User::create(['name' => 'Seller', 'email' => 'seller@example.test', 'password' => 'VerySecure123', 'tenant_id' => $this->tenant->id, 'vendor_id' => $this->offers[0]->vendor_id, 'role' => 'vendor']);
        $this->actingAs($user);
        $this->assertSame(1, OfferResource::getEloquentQuery()->count());
        $this->assertFalse(OfferResource::canEdit($this->offers[1]));
    }

    public function test_customer_cannot_access_operational_panels(): void
    {
        foreach (['/admin', '/tenant', '/vendor', '/branch'] as $path) {
            $this->actingAs($this->customer)->get($path)->assertForbidden();
        }
    }

    public function test_admin_dashboard_and_resource_render(): void
    {
        foreach (['/tenant', '/tenant/products', '/tenant/vendor-orders', '/tenant/modules', '/tenant/marketplace-settings'] as $path) {
            $this->actingAs($this->admin)->get($path)->assertOk();
        }
    }

    public function test_disabled_module_blocks_new_quotes(): void
    {
        app(ModuleManager::class)->toggle('b2b', false);
        $this->actingAs($this->customer)->post('/quotations', ['offer_id' => $this->offers[0]->id, 'quantity' => 10])->assertForbidden();
    }

    public function test_review_requires_completed_purchase(): void
    {
        $this->actingAs($this->customer)->post('/reviews/'.$this->offers[0]->product_id, ['rating' => 5, 'title' => 'Test review', 'body' => 'A long enough review.'])->assertNotFound();
    }

    public function test_completed_purchase_can_review(): void
    {
        $order = $this->place();
        $this->capture($order);
        $this->deliver($order);
        $this->actingAs($this->customer)->post('/reviews/'.$this->offers[0]->product_id, ['rating' => 5, 'title' => 'Test review', 'body' => 'A long enough review.'])->assertRedirect();
        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_api_order_authentication_required(): void
    {
        $this->getJson('/api/v1/orders')->assertUnauthorized();
    }

    public function test_api_only_returns_authenticated_customer_orders(): void
    {
        $order = $this->place();
        $token = $this->customer->createToken('test', ['orders:read'])->plainTextToken;
        $this->withToken($token)->getJson('/api/v1/orders')->assertOk()->assertJsonPath('data.0.number', $order->number);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        config(['marketplace.webhook_secret' => 'test-secret']);
        $this->postJson('/api/v1/webhooks/sandbox', ['event_id' => 'bad'])->assertUnauthorized();
    }

    public function test_signed_webhook_confirms_once(): void
    {
        config(['marketplace.webhook_secret' => 'test-secret']);
        $order = $this->place();
        $payment = $order->payments->first();
        $payload = ['event_id' => 'external-test', 'reference' => $payment->reference, 'amount_minor' => $payment->amount_minor, 'currency' => 'USD'];
        $body = json_encode($payload);
        $signature = hash_hmac('sha256', $body, 'test-secret');
        $this->call('POST', '/api/v1/webhooks/sandbox', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json', 'HTTP_X_SANDBOX_SIGNATURE' => $signature], $body)->assertOk();
        $this->context();
        $this->assertSame('captured', $payment->fresh()->status);
    }

    public function test_demo_seeder_rejected_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        $this->artisan('marketplace:demo-seed')->assertFailed();
    }

    public function test_existing_offer_cannot_change_product_ownership(): void
    {
        $this->expectException(ValidationException::class);
        $this->offers[0]->update(['product_id' => $this->offers[1]->product_id]);
    }

    public function test_vendor_cannot_submit_another_vendor_in_a_mutation(): void
    {
        $seller = User::create(['name' => 'Seller', 'email' => 'mutation-seller@example.test', 'password' => 'TestPassword123', 'tenant_id' => $this->tenant->id, 'role' => 'vendor', 'vendor_id' => $this->offers[0]->vendor_id]);
        $this->actingAs($seller);
        $this->expectException(ValidationException::class);
        OfferResource::guardMutation(['vendor_id' => $this->offers[1]->vendor_id]);
    }

    public function test_database_trigger_rejects_raw_event_history_mutation(): void
    {
        $movement = InventoryMovement::first();
        $this->expectException(QueryException::class);
        DB::table('inventory_movements')->where('id', $movement->id)->update(['quantity_delta' => 999]);
    }
}
