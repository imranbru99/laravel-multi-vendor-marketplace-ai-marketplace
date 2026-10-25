<?php

namespace App\Domain;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Offer;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class Carts
{
    /** Tenant lock serializes account-cart selection, guest merging and checkout. */
    public function current(?User $user, ?string $guestToken = null): Cart
    {
        return DB::transaction(function () use ($user, $guestToken) {
            Tenant::whereKey(app(TenantContext::class)->id())->lockForUpdate()->firstOrFail();
            $guest = $guestToken ? Cart::where('token', $guestToken)->where('status', 'active')->lockForUpdate()->first() : null;
            if ($guest?->user_id && $guest->user_id !== $user?->id) {
                $guest = null;
            }
            if (! $user) {
                return $guest ?? Cart::create(['token' => (string) Str::uuid()]);
            }
            abort_unless($user->tenant_id === app(TenantContext::class)->id(), 403);
            $cart = Cart::where('user_id', $user->id)->where('status', 'active')->orderBy('id')->lockForUpdate()->first();
            if (! $cart) {
                $cart = $guest ?? Cart::create(['token' => (string) Str::uuid(), 'user_id' => $user->id]);
                if (! $cart->user_id) {
                    $cart->update(['user_id' => $user->id]);
                }

                return $cart;
            }
            if ($guest && $guest->id !== $cart->id) {
                foreach ($guest->items as $item) {
                    $target = CartItem::firstOrNew(['cart_id' => $cart->id, 'offer_id' => $item->offer_id]);
                    $target->quantity = min(100, ($target->quantity ?? 0) + $item->quantity);
                    $target->save();
                }
                $guest->update(['status' => 'merged', 'user_id' => $user->id]);
                Audit::record('cart.merged', (string) $cart->id);
            }

            return $cart;
        }, 5);
    }

    public function add(Cart $cart, int $offerId, int $quantity): CartItem
    {
        return app(ModuleManager::class)->transaction('checkout', function () use ($cart, $offerId, $quantity) {
            $cart = Cart::whereKey($cart->id)->lockForUpdate()->firstOrFail();
            if ($cart->status !== 'active') {
                throw ValidationException::withMessages(['cart' => 'This cart has already checked out.']);
            }
            $offer = Offer::where('status', 'active')->whereHas('product', fn ($q) => $q->where('status', 'published'))->whereHas('vendor', fn ($q) => $q->where('status', 'approved'))->findOrFail($offerId);
            app(ModuleManager::class)->require('checkout', $offer->vendor_id);
            if ($quantity < 1 || $quantity > 100) {
                throw ValidationException::withMessages(['quantity' => 'Choose between 1 and 100 units.']);
            }
            $item = CartItem::firstOrNew(['cart_id' => $cart->id, 'offer_id' => $offerId]);
            if (! $item->exists && $cart->items()->count() >= 100) {
                throw ValidationException::withMessages(['cart' => 'A cart can contain up to 100 different offers.']);
            }
            $item->quantity = min(100, ($item->quantity ?? 0) + $quantity);
            $item->save();

            return $item;
        });
    }

    public function update(Cart $cart, int $itemId, int $quantity): void
    {
        DB::transaction(function () use ($cart, $itemId, $quantity) {
            $cart = Cart::whereKey($cart->id)->lockForUpdate()->firstOrFail();
            if ($cart->status !== 'active' || $quantity < 0 || $quantity > 100) {
                throw ValidationException::withMessages(['cart' => 'Invalid cart or quantity.']);
            }
            $item = $cart->items()->findOrFail($itemId);
            $quantity ? $item->update(['quantity' => $quantity]) : $item->delete();
        }, 5);
    }
}
