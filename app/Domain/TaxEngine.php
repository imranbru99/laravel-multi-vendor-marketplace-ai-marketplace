<?php

namespace App\Domain;

use App\Models\Offer;
use App\Models\TaxRule;

final class TaxEngine
{
    public function calculate(Offer $offer, int $net, string $country): array
    {
        $rules = TaxRule::where('enabled', true)->where('effective_from', '<=', now())->where(fn ($q) => $q->whereNull('effective_until')->orWhere('effective_until', '>', now()))->where(fn ($q) => $q->whereNull('country')->orWhere('country', strtoupper($country)))->where(fn ($q) => $q->whereNull('product_id')->orWhere('product_id', $offer->product_id))->where(fn ($q) => $q->whereNull('category_id')->orWhere('category_id', $offer->product->category_id))->get();
        $rule = $rules->sort(fn ($a, $b) => [$b->priority, ($b->product_id ? 4 : 0) + ($b->category_id ? 2 : 0) + ($b->country ? 1 : 0), $b->effective_from->getTimestamp(), $b->id] <=> [$a->priority, ($a->product_id ? 4 : 0) + ($a->category_id ? 2 : 0) + ($a->country ? 1 : 0), $a->effective_from->getTimestamp(), $a->id])->first();
        $bps = $rule?->percentage_bps ?? app(TenantContext::class)->get()->tax_bps;
        $amount = Money::percentage($net, $bps);

        return ['rule_id' => $rule?->id, 'amount_minor' => $amount, 'snapshot' => ['rule_id' => $rule?->id, 'name' => $rule?->name ?? 'Tenant default', 'country' => strtoupper($country), 'percentage_bps' => $bps, 'base_minor' => $net, 'calculated_minor' => $amount, 'basis' => 'exclusive merchandise after discount']];
    }
}
