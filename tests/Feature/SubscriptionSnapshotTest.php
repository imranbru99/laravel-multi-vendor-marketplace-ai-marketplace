<?php

namespace Tests\Feature;

use App\Domain\Subscriptions;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use Database\Seeders\SubscriptionPlanSeeder;
use Tests\MarketplaceTestCase;

class SubscriptionSnapshotTest extends MarketplaceTestCase
{
    public function test_same_plan_activation_is_idempotent_and_renewal_uses_snapshot_terms(): void
    {
        $this->seed(SubscriptionPlanSeeder::class);
        $plan = SubscriptionPlan::where('slug', 'growth')->firstOrFail();
        $service = app(Subscriptions::class);
        $first = $service->change($plan, true);
        $again = $service->change($plan, true);
        $this->assertSame($first->id, $again->id);
        $this->assertSame(1, SubscriptionInvoice::count());
        $plan->update(['price_minor' => 99999, 'period_days' => 7]);
        $this->travel(31)->days();
        $service->renewDue();
        $service->renewDue();
        $this->assertSame(2, SubscriptionInvoice::count());
        $this->assertSame(9900, SubscriptionInvoice::latest('id')->first()->amount_minor);
        $this->assertSame(30, (int) $first->fresh()->period_starts_at->diffInDays($first->fresh()->period_ends_at));
        $this->travelBack();
    }
}
