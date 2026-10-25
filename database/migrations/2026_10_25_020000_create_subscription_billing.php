<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->unsignedBigInteger('price_minor');
            $t->string('currency', 3)->default('USD');
            $t->unsignedInteger('period_days')->default(30);
            $t->json('limits');
            $t->json('modules');
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
        Schema::create('tenant_subscriptions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('subscription_plan_id')->constrained();
            $t->string('status')->default('active');
            $t->unsignedBigInteger('price_minor');
            $t->string('currency', 3);
            $t->json('limits');
            $t->json('modules');
            $t->timestamp('period_starts_at');
            $t->timestamp('period_ends_at');
            $t->boolean('auto_renew')->default(false);
            $t->timestamp('cancelled_at')->nullable();
            $t->timestamps();
        });
        if (in_array(DB::getDriverName(), ['sqlite', 'pgsql'])) {
            DB::statement("CREATE UNIQUE INDEX one_active_tenant_subscription ON tenant_subscriptions (tenant_id) WHERE status = 'active'");
        }
        Schema::create('subscription_invoices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tenant_id')->constrained();
            $t->foreignId('tenant_subscription_id')->constrained();
            $t->string('reference')->unique();
            $t->unsignedBigInteger('amount_minor');
            $t->string('currency', 3);
            $t->string('status')->default('sandbox_paid');
            $t->timestamp('paid_at');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_invoices');
        Schema::dropIfExists('tenant_subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};
