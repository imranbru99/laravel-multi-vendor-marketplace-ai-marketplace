<?php

namespace App\Domain;

use App\Models\InventoryBalance;
use App\Models\Offer;
use App\Models\ShippingRule;

final class ShippingEngine
{
    public function calculate(array $lines, string $country): array
    {
        $physical = collect($lines)->where('product_type', 'physical');
        if ($physical->isEmpty()) {
            return ['amount_minor' => 0, 'snapshot' => []];
        }
        $allocations = [];
        foreach ($physical as $line) {
            $stock = InventoryBalance::with('warehouse')->findOrFail($line['inventory_balance_id']);
            $key = $line['vendor_id'].':'.$stock->warehouse->branch_id;
            if (! isset($allocations[$key])) {
                $allocations[$key] = ['vendor_id' => $line['vendor_id'], 'branch_id' => $stock->warehouse->branch_id, 'quantity' => 0, 'grams' => 0, 'net_minor' => 0];
            }$allocations[$key]['quantity'] += $line['quantity'];
            $allocations[$key]['grams'] += (Offer::with('product', 'variant')->findOrFail($line['offer_id'])->variant?->weight_grams ?? Offer::with('product')->findOrFail($line['offer_id'])->product->weight_grams) * $line['quantity'];
            $allocations[$key]['net_minor'] += $line['total_minor'] - $line['tax_minor'];
        }
        $total = 0;
        $snapshot = [];
        $fallback = false;
        foreach ($allocations as $allocation) {
            $rules = ShippingRule::where('enabled', true)->where(fn ($q) => $q->whereNull('vendor_id')->orWhere('vendor_id', $allocation['vendor_id']))->where(fn ($q) => $q->whereNull('branch_id')->orWhere('branch_id', $allocation['branch_id']))->where(fn ($q) => $q->whereNull('country')->orWhere('country', strtoupper($country)))->get();
            $rule = $rules->sort(fn ($a, $b) => [$b->priority, ($b->branch_id ? 4 : 0) + ($b->vendor_id ? 2 : 0) + ($b->country ? 1 : 0), $b->id] <=> [$a->priority, ($a->branch_id ? 4 : 0) + ($a->vendor_id ? 2 : 0) + ($a->country ? 1 : 0), $a->id])->first();
            if (! $rule) {
                $fallback = true;

                continue;
            }
            $amount = $rule->base_minor + match ($rule->method) {
                'per_item' => $rule->rate_minor * $allocation['quantity'],'weight' => $rule->rate_minor * intdiv($allocation['grams'] + 999, 1000),default => $rule->rate_minor
            };
            if ($rule->free_from_minor !== null && $allocation['net_minor'] >= $rule->free_from_minor) {
                $amount = 0;
            }$total += $amount;
            $snapshot[] = $allocation + ['rule_id' => $rule->id, 'name' => $rule->name, 'method' => $rule->method, 'base_minor' => $rule->base_minor, 'rate_minor' => $rule->rate_minor, 'free_from_minor' => $rule->free_from_minor, 'calculated_minor' => $amount];
        }
        if ($fallback) {
            $amount = app(TenantContext::class)->get()->shipping_minor;
            $total += $amount;
            $snapshot[] = ['name' => 'Configured order fallback', 'method' => 'flat', 'calculated_minor' => $amount];
        }

return ['amount_minor' => $total, 'snapshot' => $snapshot];
    }
}
