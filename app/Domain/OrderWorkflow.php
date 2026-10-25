<?php

namespace App\Domain;

use App\Models\DigitalEntitlement;
use App\Models\InventoryBalance;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\ReturnRequest;
use App\Models\Settlement;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\ShipmentItem;
use App\Models\VendorOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class OrderWorkflow
{
    public function cancel(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($order->status === 'cancelled') {
                return;
            }
            if ($order->status !== 'pending_payment') {
                throw ValidationException::withMessages(['order' => 'Only unpaid orders can be cancelled.']);
            }
            foreach ($order->items as $item) {
                app(Inventory::class)->change(InventoryBalance::findOrFail($item->inventory_balance_id), 0, -$item->quantity, 'reservation released', 'cancel:'.$item->id);
            }
            $order->vendorOrders()->update(['status' => 'cancelled']);
            $order->payments()->where('status', 'pending')->update(['status' => 'cancelled']);
            $order->update(['status' => 'cancelled']);
            Audit::record('order.cancelled', $order->number);
        }, 5);
    }

    public function ship(VendorOrder $sub, string $carrier, string $tracking, array $quantities = [], ?string $key = null): Shipment
    {
        app(ModuleManager::class)->require('shipping', $sub->vendor_id);
        if ($key !== null && (strlen($key) < 8 || strlen($key) > 100)) {
            throw ValidationException::withMessages(['key' => 'Shipment key must have 8 to 100 characters.']);
        }
        $canonical = $quantities;
        usort($canonical, fn ($a, $b) => ($a['order_item_id'] ?? 0) <=> ($b['order_item_id'] ?? 0));
        $hash = hash('sha256', json_encode([$sub->id, $carrier, $tracking, $canonical], JSON_THROW_ON_ERROR));

        return DB::transaction(function () use ($sub, $carrier, $tracking, $quantities, $key, $hash) {
            $order = Order::whereKey($sub->order_id)->lockForUpdate()->firstOrFail();
            $sub = VendorOrder::whereKey($sub->id)->lockForUpdate()->firstOrFail();
            if ($key && ($existing = Shipment::where('idempotency_key', $key)->first())) {
                if ($existing->vendor_order_id !== $sub->id || $existing->request_hash !== $hash) {
                    throw ValidationException::withMessages(['key' => 'This shipment key belongs to a different request.']);
                }

                return $existing;
            }
            if (! in_array($sub->status, ['confirmed', 'processing', 'partially_fulfilled'])) {
                throw ValidationException::withMessages(['shipment' => 'Order is not ready to ship.']);
            }
            $lines = $sub->items()->orderBy('id')->lockForUpdate()->get();
            $selected = [];
            if (! $quantities) {
                foreach ($lines as $line) {
                    if ($line->product_type === 'physical') {
                        $remaining = max(0, $line->quantity - $line->refunded_quantity - $line->shipped_quantity);
                        if ($remaining) {
                            $selected[$line->id] = $remaining;
                        }
                    }
                }
            } else {
                foreach ($quantities as $entry) {
                    $id = (int) ($entry['order_item_id'] ?? 0);
                    $quantity = (int) ($entry['quantity'] ?? 0);
                    if (isset($selected[$id]) || $quantity < 1) {
                        throw ValidationException::withMessages(['items' => 'Choose distinct items with positive quantities.']);
                    }$selected[$id] = $quantity;
                }
            }
            if (! $selected) {
                throw ValidationException::withMessages(['items' => 'No remaining physical items to ship.']);
            }
            foreach ($selected as $id => $quantity) {
                $line = $lines->firstWhere('id', $id);
                if (! $line || $line->product_type !== 'physical' || $quantity > $line->quantity - $line->refunded_quantity - $line->shipped_quantity) {
                    throw ValidationException::withMessages(['items' => 'A shipment quantity exceeds the remaining eligible units.']);
                }
            }
            $shipment = Shipment::create(['idempotency_key' => $key, 'request_hash' => $hash, 'vendor_id' => $sub->vendor_id, 'vendor_order_id' => $sub->id, 'carrier' => $carrier, 'tracking' => $tracking]);
            foreach ($selected as $id => $quantity) {
                ShipmentItem::create(['vendor_id' => $sub->vendor_id, 'shipment_id' => $shipment->id, 'order_item_id' => $id, 'quantity' => $quantity]);
                $lines->firstWhere('id', $id)->increment('shipped_quantity', $quantity);
            }
            ShipmentEvent::create(['vendor_id' => $sub->vendor_id, 'shipment_id' => $shipment->id, 'reference' => 'shipment:'.$shipment->id.':shipped', 'status' => 'shipped', 'occurred_at' => now()]);
            $this->syncFulfillment($sub, $order);
            Audit::record('shipment.created', (string) $shipment->id);

            return $shipment;
        }, 5);
    }

    public function deliver(Shipment $shipment): void
    {
        DB::transaction(function () use ($shipment) {
            $sub = VendorOrder::whereKey($shipment->vendor_order_id)->firstOrFail();
            $order = Order::whereKey($sub->order_id)->lockForUpdate()->firstOrFail();
            $sub = VendorOrder::whereKey($sub->id)->lockForUpdate()->firstOrFail();
            $shipment = Shipment::whereKey($shipment->id)->lockForUpdate()->firstOrFail();
            if ($shipment->status === 'delivered') {
                return;
            }
            $shipment->update(['status' => 'delivered', 'delivered_at' => now()]);
            ShipmentEvent::create(['vendor_id' => $sub->vendor_id, 'shipment_id' => $shipment->id, 'reference' => 'shipment:'.$shipment->id.':delivered', 'status' => 'delivered', 'occurred_at' => now()]);
            $this->syncFulfillment($sub, $order);
            Audit::record('shipment.delivered', (string) $shipment->id);
        }, 5);
    }

    private function syncFulfillment(VendorOrder $sub, Order $order): void
    {
        $remaining = $sub->items()->whereRaw('shipped_quantity < quantity - refunded_quantity')->exists();
        $openShipments = $sub->shipments()->where('status', '!=', 'delivered')->exists();
        $status = $remaining ? 'partially_fulfilled' : ($openShipments ? 'shipped' : 'completed');
        $sub->update(['status' => $status]);
        if ($order->refunded_minor) {
            $order->update(['status' => $order->refunded_minor === $order->total_minor - $order->shipping_minor ? 'refunded' : 'partially_refunded']);

            return;
        }
        $open = $order->vendorOrders()->whereNotIn('status', ['completed', 'refunded', 'cancelled'])->exists();
        $unshipped = $order->vendorOrders()->whereIn('status', ['confirmed', 'processing', 'partially_fulfilled'])->exists();
        $order->update(['status' => ! $open ? 'completed' : ($unshipped ? 'partially_fulfilled' : 'fulfilled')]);
    }

    public function authorizeReturn(ReturnRequest $return): void
    {
        DB::transaction(function () use ($return) {
            Order::whereKey($return->orderItem->order_id)->lockForUpdate()->firstOrFail();
            $return = ReturnRequest::whereKey($return->id)->lockForUpdate()->firstOrFail();
            if ($return->status !== 'requested') {
                throw ValidationException::withMessages(['return' => 'Only requested returns can be authorized.']);
            }$return->update(['status' => 'authorized', 'authorized_at' => now()]);
            Audit::record('return.authorized', (string) $return->id);
        }, 5);
    }

    public function receiveReturn(ReturnRequest $return, string $notes): void
    {
        DB::transaction(function () use ($return, $notes) {
            Order::whereKey($return->orderItem->order_id)->lockForUpdate()->firstOrFail();
            $return = ReturnRequest::whereKey($return->id)->lockForUpdate()->firstOrFail();
            if ($return->status !== 'authorized') {
                throw ValidationException::withMessages(['return' => 'Authorize this return before recording receipt.']);
            }$return->update(['status' => 'received', 'received_at' => now(), 'decision_notes' => $notes]);
            Audit::record('return.received', (string) $return->id);
        }, 5);
    }

    public function rejectReturn(ReturnRequest $return, string $notes): void
    {
        DB::transaction(function () use ($return, $notes) {
            Order::whereKey($return->orderItem->order_id)->lockForUpdate()->firstOrFail();
            $return = ReturnRequest::whereKey($return->id)->lockForUpdate()->firstOrFail();
            if (! in_array($return->status, ['requested', 'authorized'])) {
                throw ValidationException::withMessages(['return' => 'Received/refunded returns cannot be rejected.']);
            }$return->update(['status' => 'rejected', 'decision_notes' => $notes]);
            Audit::record('return.rejected', (string) $return->id);
        }, 5);
    }

    public function refund(ReturnRequest $return): Refund
    {
        abort_unless(config('marketplace.sandbox'), 403, 'Only sandbox refunds are implemented.');

        return DB::transaction(function () use ($return) {
            $item = OrderItem::findOrFail($return->order_item_id);
            $order = Order::whereKey($item->order_id)->lockForUpdate()->firstOrFail();
            $item = OrderItem::whereKey($item->id)->lockForUpdate()->firstOrFail();
            $sub = VendorOrder::whereKey($item->vendor_order_id)->lockForUpdate()->firstOrFail();
            $return = ReturnRequest::whereKey($return->id)->lockForUpdate()->firstOrFail();
            $key = 'return:'.$return->id;
            if ($existing = Refund::where('idempotency_key', $key)->first()) {
                return $existing;
            }
            if (! in_array($return->status, ['requested', 'authorized', 'received']) || ! in_array($sub->status, ['completed', 'shipped', 'partially_fulfilled']) || $sub->settled_minor > 0 || $return->quantity > $item->quantity - $item->refunded_quantity) {
                throw ValidationException::withMessages(['refund' => 'Refund not eligible; check remaining quantity and settlement hold.']);
            }
            // Cumulative allocation ensures the final unit reverses rounding remainders exactly.
            $old = $item->refunded_quantity;
            $new = $old + $return->quantity;
            $amount = intdiv($item->total_minor * $new, $item->quantity) - intdiv($item->total_minor * $old, $item->quantity);
            $commission = intdiv($item->commission_minor * $new, $item->quantity) - intdiv($item->commission_minor * $old, $item->quantity);
            $tax = intdiv($item->tax_minor * $new, $item->quantity) - intdiv($item->tax_minor * $old, $item->quantity);
            $vendor = $amount - $tax - $commission;
            $refund = Refund::create(['vendor_id' => $item->vendor_id, 'order_id' => $order->id, 'order_item_id' => $item->id, 'return_request_id' => $return->id, 'idempotency_key' => $key, 'amount_minor' => $amount, 'commission_minor' => $commission]);
            app(Ledger::class)->post('refund:'.$refund->id, 'Sandbox refund '.$order->number, $order->currency, [['account' => 'sandbox_cash', 'amount_minor' => -$amount], ['account' => 'vendor_payable', 'vendor_id' => $sub->vendor_id, 'amount_minor' => $vendor], ['account' => 'commission_revenue', 'vendor_id' => $sub->vendor_id, 'amount_minor' => $commission], ['account' => 'tax_payable', 'amount_minor' => $tax]]);
            app(Inventory::class)->change(InventoryBalance::findOrFail($item->inventory_balance_id), $return->quantity, 0, 'return received (sandbox)', 'return:'.$return->id);
            $item->update(['refunded_quantity' => $new]);
            if ($item->product_type === 'digital' && $new === $item->quantity) {
                DigitalEntitlement::where('order_item_id', $item->id)->update(['revoked_at' => now()]);
            }
            $sub->increment('refunded_minor', $vendor);
            $sub->increment('commission_refunded_minor', $commission);
            $order->increment('refunded_minor', $amount);
            $order->refresh();
            $order->update(['status' => $order->refunded_minor === $order->total_minor - $order->shipping_minor ? 'refunded' : 'partially_refunded']);
            $return->update(['status' => 'refunded', 'amount_minor' => $amount]);
            Audit::record('refund.sandbox_confirmed', (string) $refund->id);

            return $refund;
        }, 5);
    }

    public function settle(VendorOrder $sub): Settlement
    {
        abort_unless(config('marketplace.sandbox'), 403);

        return DB::transaction(function () use ($sub) {
            Order::whereKey($sub->order_id)->lockForUpdate()->firstOrFail();
            $sub = VendorOrder::whereKey($sub->id)->lockForUpdate()->firstOrFail();
            if ($existing = Settlement::where('vendor_order_id', $sub->id)->first()) {
                return $existing;
            }
            $itemIds = $sub->items()->pluck('id');
            $digitalOnly = ! $sub->items()->where('product_type', '!=', 'digital')->exists();
            $lastDelivery = Shipment::where('vendor_order_id', $sub->id)->where('status', 'delivered')->max('delivered_at');
            $delivered = $digitalOnly ? Payment::where('order_id', $sub->order_id)->where('status', 'captured')->where('updated_at', '<=', now()->subDays(14))->exists() : ($lastDelivery && Carbon::parse($lastDelivery)->lte(now()->subDays(14)));
            if ($sub->status !== 'completed' || ! $delivered || ReturnRequest::whereIn('order_item_id', $itemIds)->whereIn('status', ['requested', 'authorized', 'received'])->exists()) {
                throw ValidationException::withMessages(['settlement' => 'Requires delivery, 14-day return window, and no unresolved returns.']);
            }
            $amount = $sub->payable_minor - $sub->refunded_minor;
            if ($amount <= 0) {
                throw ValidationException::withMessages(['settlement' => 'No eligible balance.']);
            }
            $settlement = Settlement::create(['vendor_id' => $sub->vendor_id, 'vendor_order_id' => $sub->id, 'reference' => 'sandbox_settlement_'.$sub->id, 'amount_minor' => $amount]);
            app(Ledger::class)->post('settlement:'.$sub->id, 'Sandbox vendor settlement', $sub->order->currency, [['account' => 'vendor_payable', 'vendor_id' => $sub->vendor_id, 'amount_minor' => $amount], ['account' => 'sandbox_cash', 'amount_minor' => -$amount]]);
            $sub->update(['settled_minor' => $amount]);
            Audit::record('settlement.sandbox_paid',(string) $settlement->id);

            return $settlement;
        }, 5);
    }
}
