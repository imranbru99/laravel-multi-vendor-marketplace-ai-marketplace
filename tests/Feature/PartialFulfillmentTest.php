<?php

namespace Tests\Feature;

use App\Domain\OrderWorkflow;
use App\Models\ReturnRequest;
use App\Models\ShipmentEvent;
use Illuminate\Validation\ValidationException;
use Tests\MarketplaceTestCase;

class PartialFulfillmentTest extends MarketplaceTestCase
{
    public function test_partial_shipments_complete_only_after_all_units_are_delivered(): void
    {
        $order = $this->place($this->cart(3, false));
        $this->capture($order);
        $sub = $order->vendorOrders->first();
        $item = $order->items->first();
        $first = app(OrderWorkflow::class)->ship($sub, 'Manual carrier', 'FIRST', [['order_item_id' => $item->id, 'quantity' => 1]]);
        app(OrderWorkflow::class)->deliver($first);
        $this->assertSame('partially_fulfilled', $sub->fresh()->status);
        $this->assertSame(1, $item->fresh()->shipped_quantity);
        $this->assertSame(1, $first->items()->sum('quantity'));
        $second = app(OrderWorkflow::class)->ship($sub, 'Manual carrier', 'SECOND', [['order_item_id' => $item->id, 'quantity' => 2]]);
        $this->assertSame('shipped', $sub->fresh()->status);
        app(OrderWorkflow::class)->deliver($second);
        $this->assertSame('completed', $sub->fresh()->status);
        $this->assertSame('completed', $order->fresh()->status);
        $this->assertSame(4, ShipmentEvent::count());
    }

    public function test_shipment_cannot_exceed_remaining_units(): void
    {
        $order = $this->place($this->cart(1, false));
        $this->capture($order);
        $this->expectException(ValidationException::class);
        app(OrderWorkflow::class)->ship($order->vendorOrders->first(), 'Manual', 'BAD', [['order_item_id' => $order->items->first()->id, 'quantity' => 2]]);
    }

    public function test_latest_delivery_controls_settlement_hold(): void
    {
        $order = $this->place($this->cart(2, false));
        $this->capture($order);
        $sub = $order->vendorOrders->first();
        $item = $order->items->first();
        $first = app(OrderWorkflow::class)->ship($sub, 'Manual', 'FIRST', [['order_item_id' => $item->id, 'quantity' => 1]]);
        app(OrderWorkflow::class)->deliver($first);
        $first->update(['delivered_at' => now()->subDays(20)]);
        $second = app(OrderWorkflow::class)->ship($sub, 'Manual', 'SECOND');
        app(OrderWorkflow::class)->deliver($second);
        $this->expectException(ValidationException::class);
        app(OrderWorkflow::class)->settle($sub);
    }

    public function test_return_can_be_authorized_received_and_refunded(): void
    {
        $order = $this->place();
        $this->capture($order);
        $this->deliver($order);
        $item = $order->items->first();
        $return = ReturnRequest::create(['vendor_id' => $item->vendor_id, 'order_item_id' => $item->id, 'user_id' => $this->customer->id, 'quantity' => 1, 'reason' => 'Received damaged']);
        app(OrderWorkflow::class)->authorizeReturn($return);
        $this->assertSame('authorized', $return->fresh()->status);
        app(OrderWorkflow::class)->receiveReturn($return, 'Inspected and accepted');
        $this->assertSame('received', $return->fresh()->status);
        app(OrderWorkflow::class)->refund($return);
        $this->assertSame('refunded', $return->fresh()->status);
    }

    public function test_rejected_return_does_not_change_stock_or_money(): void
    {
        $order = $this->place();
        $this->capture($order);
        $this->deliver($order);
        $item = $order->items->first();
        $return = ReturnRequest::create(['vendor_id' => $item->vendor_id, 'order_item_id' => $item->id, 'user_id' => $this->customer->id, 'quantity' => 1, 'reason' => 'Review eligibility']);
        app(OrderWorkflow::class)->rejectReturn($return, 'Reason reviewed, outside agreed criteria');
        $this->assertSame('rejected', $return->fresh()->status);
        $this->assertSame(0, $order->fresh()->refunded_minor);
        $this->assertSame(9,$this->stocks[0]->fresh()->quantity);
    }
}
