<?php

namespace App\Domain;

use App\Models\CommissionRule;
use App\Models\Offer;

final class CommissionEngine
{
    public function calculate(Offer $offer, int $net, int $quantity): array
    {
        $tenant = app(TenantContext::class)->get();
        $rules = CommissionRule::where('enabled', true)->where('effective_from', '<=', now())->where(fn ($q) => $q->whereNull('effective_until')->orWhere('effective_until', '>', now()))->where(fn ($q) => $q->whereNull('vendor_id')->orWhere('vendor_id', $offer->vendor_id))->where(fn ($q) => $q->whereNull('product_id')->orWhere('product_id', $offer->product_id))->where(fn ($q) => $q->whereNull('category_id')->orWhere('category_id', $offer->product->category_id))->where(fn ($q) => $q->whereNull('plan_slug')->orWhere('plan_slug', $tenant->plan))->where('from_subtotal_minor', '<=', $net)->where(fn ($q) => $q->whereNull('to_subtotal_minor')->orWhere('to_subtotal_minor', '>=', $net))->get();
        $rule = $rules->sort(fn ($a, $b) => [$b->priority, ($b->product_id ? 8 : 0) + ($b->vendor_id ? 4 : 0) + ($b->category_id ? 2 : 0) + ($b->plan_slug ? 1 : 0), $b->effective_from->getTimestamp(), $b->id] <=> [$a->priority, ($a->product_id ? 8 : 0) + ($a->vendor_id ? 4 : 0) + ($a->category_id ? 2 : 0) + ($a->plan_slug ? 1 : 0), $a->effective_from->getTimestamp(), $a->id])->first();
        $bps = $rule?->percentage_bps ?? $offer->vendor->commission_bps ?? $tenant->commission_bps;
        $fixed = $rule?->fixed_per_unit_minor ?? 0;
        $minimum = $rule?->minimum_minor ?? 0;
        $maximum = $rule?->maximum_minor;
        $amount = Money::percentage($net, $bps) + $fixed * $quantity;
        $amount = max($minimum, $amount);
        if ($maximum !== null) {
            $amount = min($amount, $maximum);
        }$amount = min($net, $amount);

        return ['rule_id' => $rule?->id, 'bps' => $bps, 'amount_minor' => $amount, 'snapshot' => ['rule_id' => $rule?->id, 'name' => $rule?->name ?? 'Tenant/vendor default', 'percentage_bps' => $bps, 'fixed_per_unit_minor' => $fixed, 'minimum_minor' => $minimum, 'maximum_minor' => $maximum, 'base_minor' => $net, 'quantity' => $quantity, 'allocation' => 'warehouse line', 'calculated_minor' => $amount]];
    }
}
