<?php

namespace App\Filament\Pages;

use App\Domain\Audit;
use App\Domain\TenantContext;
use App\Models\LedgerTransaction;
use App\Models\Order;
use App\Models\TenantSubscription;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Validation\ValidationException;

class MarketplaceSettings extends Page
{
    protected string $view = 'filament.pages.settings';

    public array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->managesMarketplace();
    }

    public function mount(): void
    {
        $this->data = app(TenantContext::class)->get()->only(['name', 'currency', 'locale', 'timezone', 'commission_bps', 'tax_bps', 'shipping_minor']);
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);
        $this->validate(['data.name' => 'required|string|max:100', 'data.currency' => 'required|in:USD,BDT,EUR,GBP', 'data.locale' => 'required|in:en,bn', 'data.timezone' => 'required|timezone', 'data.commission_bps' => 'required|integer|min:0|max:10000', 'data.tax_bps' => 'required|integer|min:0|max:10000', 'data.shipping_minor' => 'required|integer|min:0|max:1000000']);
        $tenant = app(TenantContext::class)->get();
        if ($this->data['currency'] !== $tenant->currency && (Order::exists() || LedgerTransaction::exists() || TenantSubscription::exists())) {
            throw ValidationException::withMessages(['data.currency' => 'Currency cannot change after financial history exists.']);
        }
        $tenant->update($this->data);
        Audit::record('settings.updated', 'marketplace');
        Notification::make()->title('Settings saved')->success()->send();
    }
}
