<?php

namespace App\Http\Controllers;

use App\Domain\SandboxGateway;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function products()
    {
        return Product::with(['offers' => fn ($q) => $q->select('id', 'tenant_id', 'product_id', 'vendor_id', 'price_minor')->where('status', 'active')->whereHas('vendor', fn ($v) => $v->where('status', 'approved'))])->select('id', 'tenant_id', 'name', 'slug', 'description', 'category_id')->where('status', 'published')->paginate(24);
    }

    public function orders()
    {
        return Order::where('user_id', auth()->id())->select('number', 'status', 'currency', 'total_minor', 'refunded_minor', 'created_at')->paginate(20);
    }

    public function webhook(Request $request)
    {
        $secret = config('marketplace.webhook_secret');
        abort_unless($secret, 503, 'Webhook secret is not configured.');
        abort_unless(hash_equals(hash_hmac('sha256', $request->getContent(), $secret), (string) $request->header('X-Sandbox-Signature')), 401);
        $data = $request->validate(['event_id' => 'required|string|max:100', 'reference' => 'required|string|max:100', 'amount_minor' => 'required|integer|min:0', 'currency' => 'required|string|size:3']);
        $payment = Payment::where('reference', $data['reference'])->firstOrFail();
        app(SandboxGateway::class)->confirm($payment, $data['event_id'], (int) $data['amount_minor'], $data['currency']);

        return ['received' => true, 'sandbox' => true];
    }
}
