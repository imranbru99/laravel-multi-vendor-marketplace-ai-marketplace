<?php

namespace Tests;

use App\Domain\Checkout;
use App\Domain\Inventory;
use App\Domain\OrderWorkflow;
use App\Domain\SandboxGateway;
use App\Domain\TenantContext;
use App\Domain\TenantSetup;
use App\Models\Branch;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\InventoryBalance;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

abstract class MarketplaceTestCase extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $customer;

    protected User $admin;

    protected array $offers = [];

    protected array $stocks = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['marketplace.demo' => true, 'marketplace.sandbox' => true]);
        $this->tenant = Tenant::create(['name' => 'Test Marketplace', 'slug' => 'atlas', 'tax_bps' => 500]);
        $this->context();
        app(TenantSetup::class)->initialize($this->tenant);
        $this->customer = User::create(['name' => 'Test Customer', 'email' => 'customer@example.test', 'password' => 'TestPassword123', 'tenant_id' => $this->tenant->id, 'role' => 'customer']);
        $this->admin = User::create(['name' => 'Test Admin', 'email' => 'admin@example.test', 'password' => 'TestPassword123', 'tenant_id' => $this->tenant->id, 'role' => 'admin']);
        $category = Category::create(['name' => 'Home', 'slug' => 'home-living']);
        for ($i = 0; $i < 2; $i++) {
            $vendor = Vendor::create(['name' => 'Vendor '.$i, 'slug' => 'vendor-'.$i, 'email' => 'vendor'.$i.'@example.test', 'status' => 'approved']);
            $branch = Branch::create(['vendor_id' => $vendor->id, 'name' => 'Branch '.$i, 'address' => 'Example']);
            $warehouse = Warehouse::create(['vendor_id' => $vendor->id, 'branch_id' => $branch->id, 'name' => 'Warehouse '.$i, 'location' => 'A1']);
            $product = Product::create(['category_id' => $category->id, 'name' => 'Product '.$i, 'slug' => 'product-'.$i, 'description' => 'A sample product', 'status' => 'published', 'image' => '/images/chair.svg']);
            $offer = Offer::create(['vendor_id' => $vendor->id, 'product_id' => $product->id, 'sku' => 'SKU-'.$i, 'price_minor' => 10001]);
            $this->offers[] = $offer;
            $stock = InventoryBalance::create(['vendor_id' => $vendor->id, 'offer_id' => $offer->id, 'warehouse_id' => $warehouse->id]);
            app(Inventory::class)->change($stock, 10, 0, 'initial', 'initial:'.$stock->id);
            $this->stocks[] = $stock;
        }
    }

    protected function context(): void
    {
        app(TenantContext::class)->set($this->tenant);
    }

    protected function cart(int $quantity = 1, bool $multi = true): Cart
    {
        $cart = Cart::create(['user_id' => $this->customer->id, 'token' => (string) Str::uuid()]);
        foreach ($multi ? $this->offers : [$this->offers[0]] as $offer) {
            CartItem::create(['cart_id' => $cart->id, 'offer_id' => $offer->id, 'quantity' => $quantity]);
        }

        return $cart;
    }

    protected function place(?Cart $cart = null, ?string $key = null, ?string $coupon = null): Order
    {
        return app(Checkout::class)->place($cart ?? $this->cart(), ['email' => $this->customer->email, 'address' => ['name' => 'Test Customer', 'line1' => '100 Example', 'city' => 'Demo', 'postal_code' => '1000', 'country' => 'US']], $key ?? (string) Str::uuid(), $coupon);
    }

    protected function capture(Order $order): void
    {
        $payment = $order->payments()->firstOrFail();
        app(SandboxGateway::class)->confirm($payment, 'test:'.$payment->id, $payment->amount_minor, $payment->currency);
    }

    protected function deliver(Order $order): void
    {
        foreach ($order->vendorOrders as $sub) {
            $shipment = app(OrderWorkflow::class)->ship($sub, 'Test carrier', 'TRACK-'.$sub->id);
            app(OrderWorkflow::class)->deliver($shipment);
        }
    }
}
