<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $all = array_keys(config('marketplace.modules'));
        SubscriptionPlan::firstOrCreate(['slug' => 'starter'], ['name' => 'Starter (sandbox example)', 'price_minor' => 2900, 'currency' => 'USD', 'period_days' => 30, 'limits' => ['branch_limit' => 2, 'vendor_limit' => 5, 'warehouse_limit' => 4, 'product_limit' => 250], 'modules' => array_values(array_diff($all, ['b2b', 'bookings', 'procurement', 'workforce', 'ai']))]);
        SubscriptionPlan::firstOrCreate(['slug' => 'growth'], ['name' => 'Growth (sandbox example)', 'price_minor' => 9900, 'currency' => 'USD', 'period_days' => 30, 'limits' => ['branch_limit' => 25, 'vendor_limit' => 100, 'warehouse_limit' => 50, 'product_limit' => 10000], 'modules' => $all]);
    }
}
