<?php

namespace App\Filament\Pages;

use App\Domain\Subscriptions;
use App\Models\SubscriptionPlan;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Billing extends Page
{
    protected string $view = 'filament.pages.billing';

    public bool $autoRenew = false;

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['superadmin', 'owner', 'admin'], true);
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public function subscribe(int $planId): void
    {
        abort_unless(static::canAccess(), 403);
        app(Subscriptions::class)->change(SubscriptionPlan::findOrFail($planId), $this->autoRenew);
        Notification::make()->title('Sandbox plan activated — no real payment')->success()->send();
    }

    public function cancelRenewal(): void
    {
        abort_unless(static::canAccess(), 403);
        $subscription = app(Subscriptions::class)->current();
        if ($subscription) {
            app(Subscriptions::class)->cancel($subscription);
        }
    }
}
