<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_subscriptions', fn (Blueprint $table) => $table->unsignedInteger('period_days')->default(30));
        foreach (DB::table('subscription_plans')->select('id', 'period_days')->get() as $plan) {
            DB::table('tenant_subscriptions')->where('subscription_plan_id', $plan->id)->update(['period_days' => $plan->period_days]);
        }
    }

    public function down(): void
    {
        Schema::table('tenant_subscriptions', fn (Blueprint $table) => $table->dropColumn('period_days'));
    }
};
