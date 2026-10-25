<?php

namespace App\Domain;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\DigitalAsset;
use App\Models\InventoryBalance;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\VendorOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class Checkout
{
    public function place(Cart $cart, array $customer, string $key, ?string $couponCode = null): Order
    {
        return DB::transaction(function () use ($cart, $customer, $key, $couponCode) {
            $tenant = Tenant::whereKey(app(TenantContext::class)->id())->lockForUpdate()->firstOrFail();
            app(ModuleManager::class)->require('checkout');
            app(ModuleManager::class)->require('payments');
            if ($existing = Order::where('idempotency_key', $key)->first()) {
                if ($existing->cart_id !== $cart->id) {
                    throw ValidationException::withMessages(['checkout' => 'Idempotency key belongs to another checkout.']);
                }

                return $existing;
            }
            $cart = Cart::whereKey($cart->id)->lockForUpdate()->firstOrFail();
            if ($cart->status !== 'active' || ! $cart->items()->exists()) {
                throw ValidationException::withMessages(['cart' => 'Your cart is empty or already checked out.']);
            }
            $coupon = null;
            if ($couponCode) {
                app(ModuleManager::class)->require('marketing');
                $coupon = Coupon::where('code', strtoupper($couponCode))->lockForUpdate()->first();
                if (! $coupon || ! $coupon->active || $coupon->used >= $coupon->usage_limit || ($coupon->expires_at && $coupon->expires_at->isPast())) {
                    throw ValidationException::withMessages(['coupon' => 'Coupon is invalid or exhausted.']);
                }
            }
            if ($cart->items()->count() > 100) {
                throw ValidationException::withMessages(['cart' => 'A cart supports up to 100 distinct offers.']);
            }
            $lines = [];
            foreach ($cart->items()->orderBy('offer_id')->get() as $item) {
                if ($item->quantity < 1 || $item->quantity > 100) {
                    throw ValidationException::withMessages(['quantity' => 'Quantity must be between 1 and 100.']);
                }
                $offer = Offer::with('product', 'vendor')->whereKey($item->offer_id)->firstOrFail();
                if ($offer->status !== 'active' || $offer->product->status !== 'published' || $offer->vendor->status !== 'approved') {
                    throw ValidationException::withMessages(['cart' => 'An item is no longer available.']);
                }
                // One line can be allocated across warehouses without overselling.
                if ($offer->product->type === 'service') {
                    throw ValidationException::withMessages(['cart' => 'Please book service appointments from the product page.']);
                }
                if ($offer->price_minor < 0 || $offer->price_minor > 1000000000) {
                    throw ValidationException::withMessages(['price' => 'Offer price is outside the supported range.']);
                }
                app(ModuleManager::class)->require('checkout', $offer->vendor_id);
                $asset = null;
                if ($offer->product->type === 'digital') {
                    app(ModuleManager::class)->require('digital', $offer->vendor_id);
                    if (! $cart->user_id) {
                        throw ValidationException::withMessages(['account' => 'Please sign in before purchasing digital products.']);
                    }
                    $asset = DigitalAsset::where('product_id', $offer->product_id)->where('rights_confirmed', true)->latest('id')->first();
                    if (! $asset || ! Storage::disk('local')->exists($asset->path)) {
                        throw ValidationException::withMessages(['digital' => 'This digital asset is not available.']);
                    }
                }
                $remaining = $item->quantity;
                foreach (InventoryBalance::where('offer_id', $offer->id)->orderBy('id')->lockForUpdate()->get() as $stock) {
                    if (! app(ModuleManager::class)->enabled('inventory', $offer->vendor_id, $stock->warehouse->branch_id)) {
                        continue;
                    }
                    $take = min($remaining, $stock->quantity - $stock->reserved);
                    if ($take <= 0) {
                        continue;
                    }
                    $base = $offer->price_minor * $take;
                    $discount = Money::percentage($base, $coupon?->discount_bps ?? 0);
                    $net = $base - $discount;
                    $taxQuote = app(TaxEngine::class)->calculate($offer, $net, $customer['address']['country']);
                    $tax = $taxQuote['amount_minor'];
                    $commission = app(CommissionEngine::class)->calculate($offer, $net, $take);
                    $bps = $commission['bps'];
                    $lines[] = ['variant_snapshot' => $offer->variant ? ['id' => $offer->variant->id, 'name' => $offer->variant->name, 'attributes' => $offer->variant->attributes, 'barcode' => $offer->variant->barcode] : null, 'product_type' => $offer->product->type, 'digital_asset_id' => $asset?->id, 'vendor_id' => $offer->vendor_id, 'offer_id' => $offer->id, 'inventory_balance_id' => $stock->id, 'name' => $offer->product->name, 'sku' => $offer->sku, 'quantity' => $take, 'unit_minor' => $offer->price_minor, 'discount_minor' => $discount, 'tax_minor' => $tax, 'total_minor' => $net + $tax, 'commission_bps' => $bps, 'commission_minor' => $commission['amount_minor'], 'commission_rule_id' => $commission['rule_id'], 'commission_snapshot' => $commission['snapshot'], 'tax_rule_id' => $taxQuote['rule_id'], 'tax_snapshot' => $taxQuote['snapshot']];
                    app(Inventory::class)->change($stock, 0, $take, 'checkout reservation', $key.':reserve:'.$stock->id);
                    $remaining -= $take;
                    if (! $remaining) {
                        break;
                    }
                }
                if ($remaining) {
                    throw ValidationException::withMessages(['stock' => "Not enough stock for {$offer->product->name}."]);
                }
            }
            $subtotal = array_sum(array_map(fn ($l) => $l['unit_minor'] * $l['quantity'], $lines));
            $discount = array_sum(array_column($lines, 'discount_minor'));
            $tax = array_sum(array_column($lines, 'tax_minor'));
            $shippingQuote = app(ShippingEngine::class)->calculate($lines, $customer['address']['country']);
            $shipping = $shippingQuote['amount_minor'];
            $order = Order::create(['user_id' => $cart->user_id, 'cart_id' => $cart->id, 'number' => 'MM-'.strtoupper(Str::random(10)), 'idempotency_key' => $key, 'currency' => $tenant->currency, 'email' => $customer['email'], 'address' => $customer['address'], 'subtotal_minor' => $subtotal, 'discount_minor' => $discount, 'tax_minor' => $tax, 'shipping_minor' => $shipping, 'shipping_snapshot' => $shippingQuote['snapshot'], 'total_minor' => $subtotal - $discount + $tax + $shipping, 'expires_at' => now()->addMinutes(30)]);
            foreach (collect($lines)->groupBy('vendor_id') as $vendorId => $group) {
                $net = $group->sum(fn ($l) => $l['total_minor'] - $l['tax_minor']);
                $commission = $group->sum('commission_minor');
                $vendorOrder = VendorOrder::create(['vendor_id' => $vendorId, 'order_id' => $order->id, 'subtotal_minor' => $net, 'commission_minor' => $commission, 'payable_minor' => $net - $commission]);
                foreach ($group as $line) {
                    OrderItem::create($line + ['order_id' => $order->id, 'vendor_order_id' => $vendorOrder->id]);
                }
            }
            Payment::create(['order_id' => $order->id, 'reference' => 'sandbox_'.Str::uuid(), 'amount_minor' => $order->total_minor, 'currency' => $order->currency]);
            $cart->update(['status' => 'checked_out']);
            if ($coupon) {
                $coupon->increment('used');
            }
            Audit::record('order.placed', $order->number);

            return $order;
        }, 5);
    }
}
