<?php

namespace App\Http\Controllers;

use App\Domain\Carts;
use App\Domain\Checkout;
use App\Domain\CustomerReturns;
use App\Domain\OrderWorkflow;
use App\Domain\SandboxGateway;
use App\Domain\TenantContext;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApiCommerceController extends Controller
{
    public const ABILITIES = ['catalog:read', 'cart:write', 'orders:read', 'orders:write', 'returns:write'];

    public function token(Request $request)
    {
        $data = $request->validate(['email' => 'required|email|max:255', 'password' => 'required|string|max:500', 'device_name' => 'required|string|max:80']);
        $user = User::where('tenant_id', app(TenantContext::class)->id())->where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => 'The provided credentials are incorrect.']);
        }
        // Customer API grants never confer operations or administrative permissions.
        $token = $user->createToken($data['device_name'], self::ABILITIES, now()->addDays(30));

        return response()->json(['token' => $token->plainTextToken, 'token_type' => 'Bearer', 'expires_at' => $token->accessToken->expires_at, 'abilities' => self::ABILITIES])->header('Cache-Control', 'no-store');
    }

    public function revoke(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->noContent();
    }

    public function cart(Request $request)
    {
        return $this->cartResponse(app(Carts::class)->current($request->user()));
    }

    public function add(Request $request)
    {
        $data = $request->validate(['offer_id' => 'required|integer', 'quantity' => 'required|integer|min:1|max:100']);
        $cart = app(Carts::class)->current($request->user());
        app(Carts::class)->add($cart, (int) $data['offer_id'], (int) $data['quantity']);

        return $this->cartResponse($cart);
    }

    public function update(Request $request, int $item)
    {
        $data = $request->validate(['quantity' => 'required|integer|min:0|max:100']);
        $cart = app(Carts::class)->current($request->user());
        app(Carts::class)->update($cart, $item, (int) $data['quantity']);

        return $this->cartResponse($cart);
    }

    private function cartResponse($cart): array
    {
        return ['token' => $cart->token, 'status' => $cart->status, 'currency' => app(TenantContext::class)->get()->currency, 'items' => $cart->items()->with('offer.product')->get()->map(fn ($item) => ['id' => $item->id, 'offer_id' => $item->offer_id, 'quantity' => $item->quantity, 'unit_price_minor' => $item->offer->price_minor, 'name' => $item->offer->product->name])->all(), 'notice' => 'Prices, discounts, shipping, taxes and availability are confirmed at checkout.'];
    }

    public function checkout(Request $request)
    {
        $data = $request->validate(['idempotency_key' => 'required|uuid', 'email' => 'required|email|max:255', 'address' => 'required|array:name,line1,city,postal_code,country', 'address.name' => 'required|string|max:100', 'address.line1' => 'required|string|max:200', 'address.city' => 'required|string|max:100', 'address.postal_code' => 'required|string|max:20', 'address.country' => 'required|string|size:2', 'coupon' => 'nullable|string|max:50']);

        return DB::transaction(function () use ($request, $data) {
            Tenant::whereKey(app(TenantContext::class)->id())->lockForUpdate()->firstOrFail();
            // A retry resolves the original cart even after a new active cart exists.
            $existing = Order::where('idempotency_key', $data['idempotency_key'])->first();
            if ($existing) {
                abort_unless($existing->user_id === $request->user()->id, 422, 'Idempotency key belongs to another checkout.');

                return $this->orderResponse($existing);
            }
            $order = app(Checkout::class)->place(app(Carts::class)->current($request->user()), ['email' => $data['email'], 'address' => $data['address']], $data['idempotency_key'], $data['coupon'] ?? null);

            return response()->json($this->orderResponse($order), 201);
        }, 5);
    }

    public function order(Request $request, string $number)
    {
        return $this->orderResponse($this->ownOrder($request, $number));
    }

    public function pay(Request $request, string $number)
    {
        $order = $this->ownOrder($request, $number);
        $payment = $order->payments()->firstOrFail();
        app(SandboxGateway::class)->confirm($payment, 'customer-api:'.$payment->reference, $payment->amount_minor, $payment->currency);

        return $this->orderResponse($order->fresh());
    }

    public function cancel(Request $request, string $number)
    {
        $order = $this->ownOrder($request, $number);
        app(OrderWorkflow::class)->cancel($order);

        return $this->orderResponse($order->fresh());
    }

    public function returnItem(Request $request, int $item)
    {
        $data = $request->validate(['quantity' => 'required|integer|min:1|max:100', 'reason' => 'required|string|min:5|max:2000']);
        $return = app(CustomerReturns::class)->request($request->user()->id, $item, (int) $data['quantity'], $data['reason']);

        return response()->json(['id' => $return->id, 'status' => $return->fresh()->status, 'quantity' => $return->quantity], 201);
    }

    private function ownOrder(Request $request, string $number): Order
    {
        return Order::where('user_id', $request->user()->id)->where('number', $number)->firstOrFail();
    }

    private function orderResponse(Order $order): array
    {
        return ['number' => $order->number, 'status' => $order->status, 'currency' => $order->currency, 'subtotal_minor' => $order->subtotal_minor, 'discount_minor' => $order->discount_minor, 'tax_minor' => $order->tax_minor, 'shipping_minor' => $order->shipping_minor, 'total_minor' => $order->total_minor, 'refunded_minor' => $order->refunded_minor, 'expires_at' => $order->expires_at, 'sandbox' => true, 'items' => $order->items()->get()->map(fn ($line) => collect($line)->only(['id', 'name', 'sku', 'quantity', 'unit_minor', 'shipped_quantity', 'refunded_quantity', 'variant_snapshot'])->all())->all(), 'shipments' => $order->vendorOrders()->with('shipments.items')->get()->flatMap(fn ($sub) => $sub->shipments->map(fn ($s) => ['carrier' => $s->carrier, 'tracking' => $s->tracking, 'status' => $s->status, 'delivered_at' => $s->delivered_at, 'items' => $s->items->map(fn ($i) => ['order_item_id' => $i->order_item_id, 'quantity' => $i->quantity])->all()]))->all()];
    }
}
