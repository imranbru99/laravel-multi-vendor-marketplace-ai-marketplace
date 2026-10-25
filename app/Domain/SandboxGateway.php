<?php

namespace App\Domain;

use App\Jobs\RecordOrderConfirmation;
use App\Models\DigitalEntitlement;
use App\Models\InventoryBalance;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SandboxGateway implements PaymentGateway
{
    public function confirm(Payment $payment, string $eventId, int $amount, string $currency): void
    {
        abort_unless(config('marketplace.sandbox'), 403, 'Sandbox payments are disabled.');
        DB::transaction(function () use ($payment, $eventId, $amount, $currency) {
            $order = Order::whereKey($payment->order_id)->lockForUpdate()->firstOrFail();
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($amount !== $payment->amount_minor || $currency !== $payment->currency) {
                throw ValidationException::withMessages(['payment' => 'Payment amount or currency mismatch.']);
            }
            if ($payment->status === 'captured') {
                return;
            }
            if ($order->status !== 'pending_payment' || $order->expires_at->isPast()) {
                throw ValidationException::withMessages(['payment' => 'Order is no longer payable.']);
            }
            if (PaymentEvent::where('event_id', $eventId)->exists()) {
                throw ValidationException::withMessages(['payment' => 'Payment event already used.']);
            }
            PaymentEvent::create(['event_id' => $eventId, 'payment_id' => $payment->id, 'type' => 'captured', 'payload_hash' => hash('sha256', $payment->reference.':'.$amount.':'.$currency)]);
            foreach ($order->items as $item) {
                app(Inventory::class)->change(InventoryBalance::findOrFail($item->inventory_balance_id), -$item->quantity, -$item->quantity, 'sandbox payment capture', 'capture:'.$item->id);
            }
            $entries = [['account' => 'sandbox_cash', 'amount_minor' => $order->total_minor], ['account' => 'tax_payable', 'amount_minor' => -$order->tax_minor], ['account' => 'shipping_revenue', 'amount_minor' => -$order->shipping_minor]];
            foreach ($order->vendorOrders as $sub) {
                $entries[] = ['account' => 'vendor_payable', 'vendor_id' => $sub->vendor_id, 'amount_minor' => -$sub->payable_minor];
                $entries[] = ['account' => 'commission_revenue', 'vendor_id' => $sub->vendor_id, 'amount_minor' => -$sub->commission_minor];
                $digitalOnly = true;
                foreach ($sub->items as $item) {
                    if ($item->product_type === 'digital') {
                        DigitalEntitlement::firstOrCreate(['order_item_id' => $item->id], ['user_id' => $order->user_id, 'digital_asset_id' => $item->digital_asset_id, 'expires_at' => now()->addDays(30)]);
                        $item->update(['shipped_quantity' => $item->quantity]);
                    } else {
                        $digitalOnly = false;
                    }
                }
                $sub->update(['status' => $digitalOnly ? 'completed' : 'confirmed']);
            }
            app(Ledger::class)->post('capture:'.$payment->id, 'Sandbox payment '.$order->number, $order->currency, $entries);
            $payment->update(['status' => 'captured']);
            $order->update(['status' => $order->vendorOrders()->where('status', '!=', 'completed')->exists() ? 'confirmed' : 'completed']);
            Audit::record('payment.sandbox_captured', $payment->reference);
            RecordOrderConfirmation::dispatch($order->tenant_id, $order->id)->afterCommit();
        }, 5);
    }
}
