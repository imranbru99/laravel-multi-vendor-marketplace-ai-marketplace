<?php

namespace Tests\Feature;

use App\Domain\OrderWorkflow;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use Illuminate\Validation\ValidationException;
use Tests\MarketplaceTestCase;

class ShipmentIdempotencyTest extends MarketplaceTestCase
{
    public function test_repeating_same_shipment_does_not_consume_another_unit(): void
    {
        $order = $this->place($this->cart(3, false));
        $this->capture($order);
        $sub = $order->vendorOrders()->first();
        $line = $order->items()->first();
        $selected = [['order_item_id' => $line->id, 'quantity' => 1]];
        $workflow = app(OrderWorkflow::class);
        $shipment = $workflow->ship($sub, 'Demo carrier', 'TRACK-123', $selected, 'repeat-shipment');
        $again = $workflow->ship($sub, 'Demo carrier', 'TRACK-123', $selected, 'repeat-shipment');
        $this->assertSame($shipment->id, $again->id);
        $this->assertSame(1, Shipment::count());
        $this->assertSame(1, ShipmentItem::sum('quantity'));
        $this->assertSame(1, $line->fresh()->shipped_quantity);
        $this->expectException(ValidationException::class);
        $workflow->ship($sub, 'Demo carrier', 'DIFFERENT', $selected, 'repeat-shipment');
    }
}
