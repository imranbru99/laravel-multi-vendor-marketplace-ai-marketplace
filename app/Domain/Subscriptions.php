<?php

namespace App\Domain;

use App\Models\Branch;
use App\Models\Product;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\TenantSubscription;
use App\Models\Vendor;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class Subscriptions
{
    public function current(): ?TenantSubscription
    {
        return TenantSubscription::where('status', 'active')->latest('id')->first();
    }

    public function entitled(string $module): bool
    {
        if (config('marketplace.modules.'.$module.'.core')) {
            return true;
        }
        $subscription = $this->current();
        if (! $subscription) {
            return ! TenantSubscription::exists();
        }

        return $subscription->period_ends_at->isFuture() && in_array($module, $subscription->modules, true);
    }

    public function quota(string $entity): void
    {
        $subscription = $this->current();
        if (! $subscription) {
            return;
        }
        $model = match ($entity) {
            'Vendor' => Vendor::class,'Warehouse' => Warehouse::class,'Product' => Product::class,'Branch' => Branch::class,default => null
        };
        if ($model) {
            $key = strtolower($entity).'_limit';
            $limit = $subscription->limits[$key] ?? null;
            if ($limit !== null && $model::count() >= (int) $limit) {
                throw ValidationException::withMessages(['name' => 'The subscription '.$key.' has been reached.']);
            }
        }
    }

    public function change(SubscriptionPlan $plan, bool $autoRenew = false): TenantSubscription
    {
        abort_unless(config('marketplace.sandbox'), 403, 'Only sandbox subscription billing is configured.');

        return DB::transaction(function () use ($plan, $autoRenew) {
            foreach ($plan->modules as $key) {
                $definition = config('marketplace.modules.'.$key);
                if (! $definition || array_diff($definition['dependencies'], $plan->modules)) {
                    throw ValidationException::withMessages(['plan' => 'Plan modules must include their dependencies.']);
                }
            }
            if ($plan->period_days < 1 || $plan->period_days > 366 || $plan->price_minor < 0 || $plan->price_minor > 1000000000) {
                throw ValidationException::withMessages(['plan' => 'Invalid billing period or amount.']);
            }
            $tenant = Tenant::whereKey(app(TenantContext::class)->id())->lockForUpdate()->firstOrFail();
            if (! $plan->active || $plan->currency !== $tenant->currency) {
                throw ValidationException::withMessages(['plan' => 'Choose an active plan in the marketplace currency.']);
            }
            $current = $this->current();
            if ($current && $current->subscription_plan_id === $plan->id && $current->period_ends_at->isFuture()) {
                $current->update(['auto_renew' => $autoRenew, 'cancelled_at' => $autoRenew ? null : $current->cancelled_at]);

                return $current;
            }
            foreach (['branch' => Branch::class, 'vendor' => Vendor::class, 'warehouse' => Warehouse::class, 'product' => Product::class] as $key => $model) {
                $limit = $plan->limits[$key.'_limit'] ?? null;
                if ($limit !== null && $model::count() > (int) $limit) {
                    throw ValidationException::withMessages(['plan' => 'Existing '.$key.' records exceed this plan.']);
                }
            }
            // Stop unentitled leaf modules first, preserving every obligation guard.
            $registry = config('marketplace.modules');
            $remaining = array_keys(array_filter($registry, fn ($definition, $key) => ! $definition['core'] && ! in_array($key, $plan->modules, true), ARRAY_FILTER_USE_BOTH));
            $passes = 0;
            while ($remaining && $passes++ < count($registry)) {
                foreach ($remaining as $index => $key) {
                    $hasDependent = false;
                    foreach ($remaining as $other) {
                        if (in_array($key, $registry[$other]['dependencies'], true)) {
                            $hasDependent = true;
                            break;
                        }
                    }if (! $hasDependent) {
                        if (TenantModule::where('key', $key)->where('enabled', true)->exists()) {
                            app(ModuleManager::class)->toggle($key, false);
                        }unset($remaining[$index]);
                    }
                }
            }
            TenantSubscription::where('status', 'active')->update(['status' => 'replaced', 'cancelled_at' => now()]);
            $subscription = TenantSubscription::create(['subscription_plan_id' => $plan->id, 'period_days' => $plan->period_days, 'price_minor' => $plan->price_minor, 'currency' => $plan->currency, 'limits' => $plan->limits, 'modules' => $plan->modules, 'period_starts_at' => now(), 'period_ends_at' => now()->addDays($plan->period_days), 'auto_renew' => $autoRenew]);
            $tenant->update(['plan' => $plan->slug, 'branch_limit' => $plan->limits['branch_limit'] ?? $tenant->branch_limit]);
            $this->invoice($subscription, 'initial');
            Audit::record('subscription.changed', (string) $subscription->id, ['plan' => $plan->slug, 'sandbox' => true]);

            return $subscription;
        }, 5);
    }

    private function invoice(TenantSubscription $subscription, string $period): SubscriptionInvoice
    {
        $invoice = SubscriptionInvoice::firstOrCreate(['reference' => 'subscription:'.$subscription->id.':'.$period], ['tenant_subscription_id' => $subscription->id, 'amount_minor' => $subscription->price_minor, 'currency' => $subscription->currency, 'paid_at' => now()]);
        app(Ledger::class)->post('subscription_invoice:'.$invoice->id, 'Sandbox subscription billing', $invoice->currency, [['account' => 'subscription_expense', 'amount_minor' => $invoice->amount_minor], ['account' => 'sandbox_cash', 'amount_minor' => -$invoice->amount_minor]]);

        return $invoice;
    }

    public function cancel(TenantSubscription $subscription): void
    {
        DB::transaction(function () use ($subscription) {
            Tenant::whereKey(app(TenantContext::class)->id())->lockForUpdate()->firstOrFail();
            $subscription = TenantSubscription::whereKey($subscription->id)->lockForUpdate()->firstOrFail();
            $subscription->update(['auto_renew' => false, 'cancelled_at' => now()]);
            Audit::record('subscription.cancel_at_period_end', (string) $subscription->id);
        }, 5);
    }

    public function renewDue(): void
    {
        DB::transaction(function () {
            Tenant::whereKey(app(TenantContext::class)->id())->lockForUpdate()->firstOrFail();
            $subscription = $this->current();
            if (! $subscription || $subscription->period_ends_at->isFuture()) {
                return;
            }
            if (! $subscription->auto_renew) {
                $subscription->update(['status' => 'expired']);
                Audit::record('subscription.expired', (string) $subscription->id);

                return;
            }
            abort_unless(config('marketplace.sandbox'), 403);
            $plan = $subscription->plan;
            if (! $plan->active) {
                $subscription->update(['auto_renew' => false, 'status' => 'expired']);

                return;
            }
            $period = $subscription->period_ends_at->toDateString();
            $this->invoice($subscription, $period);
            $subscription->update(['period_starts_at' => $subscription->period_ends_at, 'period_ends_at' => $subscription->period_ends_at->copy()->addDays($subscription->period_days)]);
            Audit::record('subscription.sandbox_renewed', (string) $subscription->id);
        }, 5);
    }
}
