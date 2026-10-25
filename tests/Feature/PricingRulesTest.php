<?php

namespace Tests\Feature;

use App\Domain\CommissionEngine;
use App\Domain\OrderWorkflow;
use App\Models\CommissionRule;
use App\Models\LedgerEntry;
use App\Models\ReturnRequest;
use App\Models\ShippingRule;
use App\Models\TaxRule;
use Illuminate\Validation\ValidationException;
use Tests\MarketplaceTestCase;

class PricingRulesTest extends MarketplaceTestCase
{
    private function rule(array $extra = []): CommissionRule
    {
        return CommissionRule::create($extra + ['name' => 'Versioned rule', 'percentage_bps' => 500, 'fixed_per_unit_minor' => 100, 'effective_from' => now()->subDay()]);
    }

    public function test_hybrid_commission_and_snapshots_survive_rule_changes(): void
    {
        $r = $this->rule(['vendor_id' => $this->offers[0]->vendor_id]);
        $order = $this->place();
        $item = $order->items()->where('offer_id', $this->offers[0]->id)->first();
        $this->assertSame(600, $item->commission_minor);
        $this->assertSame($r->id, $item->commission_rule_id);
        $this->assertSame('Versioned rule', $item->commission_snapshot['name']);
        $r->update(['enabled' => false]);
        $this->assertSame(600, $item->fresh()->commission_minor);
        $this->assertSame(1000, app(CommissionEngine::class)->calculate($this->offers[0], 10001, 1)['amount_minor']);
    }

    public function test_specific_tier_and_commission_cap_are_applied(): void
    {
        $this->rule(['name' => 'Global', 'percentage_bps' => 1000, 'fixed_per_unit_minor' => 0]);
        $specific = $this->rule(['product_id' => $this->offers[0]->product_id, 'from_subtotal_minor' => 10000, 'maximum_minor' => 400]);
        $quote = app(CommissionEngine::class)->calculate($this->offers[0], 10001, 1);
        $this->assertSame($specific->id, $quote['rule_id']);
        $this->assertSame(400, $quote['amount_minor']);
        $this->assertLessThanOrEqual(50, app(CommissionEngine::class)->calculate($this->offers[0], 50, 1)['amount_minor']);
    }

    public function test_future_rule_is_not_applied_and_versions_are_immutable(): void
    {
        $r = $this->rule(['effective_from' => now()->addDay()]);
        $this->assertNull(app(CommissionEngine::class)->calculate($this->offers[0], 10001, 1)['rule_id']);
        $this->expectException(\LogicException::class);
        $r->update(['percentage_bps' => 2000]);
    }

    public function test_country_tax_rule_is_snapshotted(): void
    {
        $rule = TaxRule::create(['name' => 'US sample tax', 'country' => 'us', 'percentage_bps' => 825, 'effective_from' => now()->subDay()]);
        $order = $this->place($this->cart(1, false));
        $item = $order->items->first();
        $this->assertSame(825, $item->tax_minor);
        $this->assertSame($rule->id, $item->tax_rule_id);
        $this->assertSame('US', $item->tax_snapshot['country']);
    }

    public function test_weight_shipping_and_free_threshold_use_integer_math(): void
    {
        $this->offers[0]->product->update(['weight_grams' => 1501]);
        $rule = ShippingRule::create(['name' => 'Sample weight rate', 'vendor_id' => $this->offers[0]->vendor_id, 'country' => 'US', 'method' => 'weight', 'base_minor' => 200, 'rate_minor' => 100]);
        $order = $this->place($this->cart(1, false));
        $this->assertSame(400, $order->shipping_minor);
        $this->assertSame(1501, $order->shipping_snapshot[0]['grams']);
        $rule->update(['free_from_minor' => 10000]);
        $order = $this->place($this->cart(1, false));
        $this->assertSame(0, $order->shipping_minor);
    }

    public function test_historical_order_line_prices_cannot_be_edited(): void
    {
        $order = $this->place();
        $this->expectException(ValidationException::class);
        $order->items->first()->update(['commission_minor' => 1]);
    }

    public function test_refund_reverses_the_applied_hybrid_commission(): void
    {
        $this->rule(['vendor_id' => $this->offers[0]->vendor_id]);
        $order = $this->place($this->cart(2, false));
        $this->capture($order);
        $this->deliver($order);
        $item = $order->items->first();
        $r = ReturnRequest::create(['vendor_id' => $item->vendor_id, 'order_item_id' => $item->id, 'user_id' => $this->customer->id, 'quantity' => 2, 'reason' => 'All units received']);
        $refund = app(OrderWorkflow::class)->refund($r);
        $this->assertSame($item->commission_minor, $refund->commission_minor);
        $this->assertSame(0,(int) LedgerEntry::sum('amount_minor'));
    }
}
