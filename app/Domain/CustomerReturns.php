<?php

namespace App\Domain;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReturnRequest;
use App\Models\ShipmentItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CustomerReturns
{
    public function request(int $userId, int $itemId, int $quantity, string $reason): ReturnRequest
    {
        // Return rights belong to the original purchase and survive plan/module changes.
        return DB::transaction(function () use ($userId, $itemId, $quantity, $reason) {
            $item = OrderItem::whereHas('order', fn ($q) => $q->where('user_id', $userId))->findOrFail($itemId);
            Order::whereKey($item->order_id)->lockForUpdate()->firstOrFail();
            $item = OrderItem::whereKey($itemId)->lockForUpdate()->firstOrFail();
            $pending = ReturnRequest::where('order_item_id', $itemId)->whereIn('status', ['requested', 'authorized', 'received'])->sum('quantity');
            $eligible = ShipmentItem::where('order_item_id', $itemId)->whereHas('shipment', fn ($q) => $q->where('status', 'delivered')->where('delivered_at', '>=', now()->subDays(14)))->sum('quantity');
            if ($quantity < 1 || $quantity > 100 || $eligible < 1 || $item->vendorOrder->settled_minor !== 0 || $quantity > $eligible - $item->refunded_quantity - $pending) {
                throw ValidationException::withMessages(['quantity' => 'Item is outside the 14-day return window or the requested quantity is unavailable.']);
            }
            $return = ReturnRequest::create(['user_id' => $userId, 'vendor_id' => $item->vendor_id, 'order_item_id' => $itemId, 'quantity' => $quantity, 'reason' => $reason]);
            Audit::record('return.requested', (string) $return->id);

            return $return;
        }, 5);
    }
}
