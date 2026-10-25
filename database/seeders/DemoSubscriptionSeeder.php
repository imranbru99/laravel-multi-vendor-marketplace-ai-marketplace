<?php

namespace Database\Seeders;

use App\Domain\Subscriptions;
use App\Domain\TenantContext;
use App\Models\SubscriptionPlan;
use App\Models\TenantSubscription;
use Illuminate\Database\Seeder;

class DemoSubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing') || ! config('marketplace.demo')) {
            throw new \RuntimeException('Demo only.');
        }
        $this->call(SubscriptionPlanSeeder::class);
        if (TenantSubscription::exists()) {
            return;
        }$plan = SubscriptionPlan::where('slug', 'growth')->firstOrFail();
        $history = app(TenantContext::class)->get()->slug === 'atlas' ? 2 : 1;
        for ($i = $history; $i > 0; $i--) {
            TenantSubscription::create(['subscription_plan_id' => $plan->id, 'status' => 'expired', 'price_minor' => $plan->price_minor, 'currency' => $plan->currency, 'limits' => $plan->limits, 'modules' => $plan->modules, 'period_starts_at' => now()->subDays(($i + 1) * 30), 'period_ends_at' => now()->subDays($i * 30)]);
        }
        app(Subscriptions::class)->change($plan, false);
    }
}
