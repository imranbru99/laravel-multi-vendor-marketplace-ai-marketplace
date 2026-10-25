<?php

namespace Tests\Feature;

use App\Domain\Carts;
use App\Domain\Inventory;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\TenantModule;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Tests\MarketplaceTestCase;

class CustomerApiTest extends MarketplaceTestCase
{
    private function payload(string $key): array
    {
        return ['idempotency_key' => $key, 'email' => $this->customer->email, 'address' => ['name' => 'Test Customer', 'line1' => '100 Example', 'city' => 'Demo', 'postal_code' => '1000', 'country' => 'US']];
    }

    public function test_customer_can_checkout_retry_pay_and_read_only_own_order(): void
    {
        Sanctum::actingAs($this->customer, ['cart:write', 'orders:write', 'orders:read']);
        $this->postJson('/api/v1/cart/items', ['offer_id' => $this->offers[0]->id, 'quantity' => 2])->assertOk()->assertJsonPath('items.0.quantity', 2);
        $key = (string) Str::uuid();
        $number = $this->postJson('/api/v1/checkout', $this->payload($key))->assertCreated()->assertJsonPath('total_minor', 21502)->json('number');
        $this->getJson('/api/v1/cart')->assertOk()->assertJsonCount(0, 'items');
        $this->postJson('/api/v1/checkout', $this->payload($key))->assertOk()->assertJsonPath('number', $number);
        $this->postJson('/api/v1/orders/'.$number.'/sandbox-payment')->assertOk()->assertJsonPath('status', 'confirmed');
        $this->postJson('/api/v1/orders/'.$number.'/sandbox-payment')->assertOk();
        $this->getJson('/api/v1/orders/'.$number)->assertOk()->assertJsonPath('items.0.quantity', 2);
        $other = User::create(['name' => 'Other', 'email' => 'other@example.test', 'password' => 'password123', 'tenant_id' => $this->tenant->id, 'role' => 'customer']);
        Sanctum::actingAs($other, ['orders:read', 'orders:write']);
        $this->getJson('/api/v1/orders/'.$number)->assertNotFound();
        $this->postJson('/api/v1/checkout', $this->payload($key))->assertUnprocessable();
        $this->context();
        $this->assertSame(1, Order::count());
        $this->assertSame(8, $this->stocks[0]->fresh()->quantity);
    }

    public function test_api_enforces_token_abilities_and_validation(): void
    {
        $this->getJson('/api/v1/cart')->assertUnauthorized();
        Sanctum::actingAs($this->customer, ['orders:read']);
        $this->postJson('/api/v1/cart/items', ['offer_id' => $this->offers[0]->id, 'quantity' => 1])->assertForbidden();
        Sanctum::actingAs($this->customer, ['cart:write', 'orders:write']);
        $this->postJson('/api/v1/cart/items', ['offer_id' => $this->offers[0]->id, 'quantity' => -1])->assertUnprocessable();
        $this->postJson('/api/v1/checkout', $this->payload('bad-key'))->assertUnprocessable();
    }

    public function test_token_login_is_tenant_bound_hashed_and_revocable(): void
    {
        $this->postJson('/api/v1/tokens', ['email' => $this->customer->email, 'password' => 'wrong', 'device_name' => 'Test'])->assertUnprocessable();
        $response = $this->postJson('/api/v1/tokens', ['email' => $this->customer->email, 'password' => 'TestPassword123', 'device_name' => 'Test'])->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $token = $response->json('token');
        $this->assertNotSame($token, $this->customer->tokens()->first()->token);
        $this->withToken($token)->getJson('/api/v1/cart')->assertOk();
        $this->withToken($token)->deleteJson('/api/v1/tokens/current')->assertNoContent();
        $this->assertSame(0, $this->customer->tokens()->count());
    }

    public function test_guest_cart_merges_once_and_account_cart_survives_another_session(): void
    {
        $account = $this->cart(2, false);
        $guest = Cart::create(['token' => (string) Str::uuid()]);
        CartItem::create(['cart_id' => $guest->id, 'offer_id' => $this->offers[0]->id, 'quantity' => 3]);
        CartItem::create(['cart_id' => $guest->id, 'offer_id' => $this->offers[1]->id, 'quantity' => 1]);
        $service = app(Carts::class);
        $this->assertSame($account->id, $service->current($this->customer, $guest->token)->id);
        $service->current($this->customer, $guest->token);
        $this->assertSame(5, $account->items()->where('offer_id', $this->offers[0]->id)->first()->quantity);
        $this->assertSame('merged', $guest->fresh()->status);
        $this->assertSame($account->id, $service->current($this->customer)->id);
        $stranger = $service->current(null, $account->token);
        $this->assertNotSame($account->id, $stranger->id);
    }

    public function test_api_return_honors_purchased_rights_after_module_is_disabled_and_rejects_duplicate_quantity(): void
    {
        $order = $this->place();
        $this->capture($order);
        $this->deliver($order);
        TenantModule::where('key', 'returns')->update(['enabled' => false]);
        Sanctum::actingAs($this->customer, ['returns:write']);
        $item = $order->items()->first();
        $this->postJson('/api/v1/order-items/'.$item->id.'/returns', ['quantity' => 1, 'reason' => 'Arrived damaged'])->assertCreated()->assertJsonPath('status', 'requested');
        $this->postJson('/api/v1/order-items/'.$item->id.'/returns', ['quantity' => 1, 'reason' => 'Arrived damaged'])->assertUnprocessable();
    }

    public function test_inventory_idempotency_rejects_reused_reference_with_different_payload(): void
    {
        $inventory = app(Inventory::class);
        $inventory->change($this->stocks[0], 2, 0, 'adjust', 'repeat-test');
        $inventory->change($this->stocks[0], 2, 0, 'adjust', 'repeat-test');
        $this->assertSame(12, $this->stocks[0]->fresh()->quantity);
        $this->expectException(ValidationException::class);
        $inventory->change($this->stocks[0], 3, 0, 'adjust', 'repeat-test');
    }
}
