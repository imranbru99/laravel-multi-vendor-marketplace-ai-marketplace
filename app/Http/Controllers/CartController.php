<?php

namespace App\Http\Controllers;

use App\Domain\Carts;
use App\Domain\Checkout;
use App\Domain\ModuleManager;
use App\Domain\OrderWorkflow;
use App\Domain\SandboxGateway;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use ImranDev\UnicodePdf\Facades\UnicodePdf;

class CartController extends Controller
{
    public function cart(Request $request): Cart
    {
        $cart = app(Carts::class)->current($request->user(), $request->session()->get('cart_token'));
        $request->session()->put('cart_token', $cart->token);

        return $cart;
    }

    public function index(Request $request)
    {
        return view('store.cart', ['cart' => $this->cart($request)->load('items.offer.product', 'items.offer.vendor')]);
    }

    public function add(Request $request)
    {
        $data = $request->validate(['offer_id' => 'required|integer', 'quantity' => 'required|integer|min:1|max:100']);
        app(Carts::class)->add($this->cart($request), (int) $data['offer_id'], (int) $data['quantity']);

        return redirect('/cart')->with('success', 'Added to your cart.');
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate(['quantity' => 'required|integer|min:0|max:100']);
        app(Carts::class)->update($this->cart($request), $id, (int) $data['quantity']);

        return back()->with('success', 'Cart updated.');
    }

    public function checkout(Request $request)
    {
        app(ModuleManager::class)->require('checkout');

        return view('store.checkout', ['cart' => $this->cart($request)->load('items.offer.product'), 'key' => (string) Str::uuid()]);
    }

    public function place(Request $request)
    {
        $data = $request->validate(['email' => 'required|email|max:255', 'name' => 'required|string|max:100', 'line1' => 'required|string|max:200', 'city' => 'required|string|max:100', 'postal_code' => 'required|string|max:20', 'country' => 'required|string|size:2', 'key' => 'required|uuid', 'coupon' => 'nullable|string|max:50']);
        $token = $request->session()->get('cart_token');
        $cart = Cart::where('token', $token)->firstOrFail();
        abort_if($cart->user_id && $cart->user_id !== auth()->id(), 403);
        if (auth()->check() && ! $cart->user_id) {
            $cart->update(['user_id' => auth()->id()]);
        }
        $order = app(Checkout::class)->place($cart, ['email' => $data['email'], 'address' => collect($data)->only(['name', 'line1', 'city', 'postal_code', 'country'])->all()], $data['key'], $data['coupon'] ?? null);
        $request->session()->push('guest_order_ids', $order->id);

        return redirect('/orders/'.$order->number);
    }

    public function authorizeOrder(Request $request, string $number): Order
    {
        $order = Order::where('number', $number)->firstOrFail();
        abort_unless(($order->user_id && $order->user_id === auth()->id()) || in_array($order->id, $request->session()->get('guest_order_ids', [])), 404);

        return $order;
    }

    public function order(Request $request, string $number)
    {
        return view('account.order', ['order' => $this->authorizeOrder($request, $number)->load('items.vendorOrder.vendor', 'vendorOrders.shipments', 'payments')]);
    }

    public function pay(Request $request, string $number)
    {
        $order = $this->authorizeOrder($request, $number);
        $payment = $order->payments()->firstOrFail();
        app(SandboxGateway::class)->confirm($payment, 'browser:'.$payment->reference, $payment->amount_minor, $payment->currency);

        return back()->with('success', 'Sandbox payment confirmed. No money was charged.');
    }

    public function cancel(Request $request, string $number)
    {
        app(OrderWorkflow::class)->cancel($this->authorizeOrder($request, $number));

        return back()->with('success', 'Order cancelled and inventory released.');
    }

    public function invoice(Request $request, string $number)
    {
        $order = $this->authorizeOrder($request, $number)->load('items');

        return UnicodePdf::view('account.invoice', compact('order'))->download($order->number.'.pdf');
    }
}
