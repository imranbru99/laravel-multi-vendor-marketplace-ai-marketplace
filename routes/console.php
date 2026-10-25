<?php

use App\Domain\OrderWorkflow;
use App\Domain\Subscriptions;
use App\Domain\TenantContext;
use App\Domain\TenantSetup;
use App\Models\Order;
use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Validation\ValidationException;

Artisan::command('marketplace:expire-orders', function () {
    foreach (Tenant::where('status', 'active')->cursor() as $tenant) {
        app(TenantContext::class)->set($tenant);
        foreach (Order::where('status', 'pending_payment')->where('expires_at', '<', now())->cursor() as $order) {
            try {
                app(OrderWorkflow::class)->cancel($order);
            } catch (ValidationException $exception) {
                // Capture can win after the expiry cursor was read; never cancel a paid order.
                if ($order->fresh()->status === 'pending_payment') {
                    throw $exception;
                }
            }
        }
    }
    app(TenantContext::class)->clear();
    $this->info('Expired unpaid orders released.');
})->purpose('Cancel expired unpaid orders and release stock');
Schedule::command('marketplace:expire-orders')->everyMinute()->withoutOverlapping();

Artisan::command('marketplace:sync-modules', function () {
    foreach (Tenant::cursor() as $tenant) {
        app(TenantSetup::class)->initialize($tenant, false);
    }
    $this->info('New registry entries added disabled; existing states preserved.');
})->purpose('Synchronize new modules without enabling them automatically');

Artisan::command('marketplace:billing-renewals', function () {
    foreach (Tenant::where('status', 'active')->cursor() as $tenant) {
        app(TenantContext::class)->set($tenant);
        app(Subscriptions::class)->renewDue();
    }app(TenantContext::class)->clear();
    $this->info('Subscription periods processed.');
});
Schedule::command('marketplace:billing-renewals')->daily()->withoutOverlapping();
