<?php

namespace App\Filament\Pages;

use App\Domain\CommissionEngine;
use App\Domain\Money;
use App\Models\Offer;
use Filament\Pages\Page;

class CommissionSimulator extends Page
{
    protected string $view = 'filament.pages.commission-simulator';

    public int $offerId = 0;

    public int $quantity = 1;

    public int $discountBps = 0;

    public array $result = [];

    public static function getNavigationGroup(): ?string
    {
        return 'Finance';
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['superadmin', 'owner', 'admin', 'finance'], true);
    }

    public function calculate(): void
    {
        abort_unless(static::canAccess(), 403);
        $this->validate(['offerId' => 'required|integer|min:1', 'quantity' => 'required|integer|min:1|max:100', 'discountBps' => 'required|integer|min:0|max:10000']);
        $offer = Offer::with('vendor', 'product')->findOrFail($this->offerId);
        $base = $offer->price_minor * $this->quantity;
        $net = $base - Money::percentage($base, $this->discountBps);
        $quote = app(CommissionEngine::class)->calculate($offer, $net, $this->quantity);
        $this->result = ['merchandise_minor' => $net, 'commission_minor' => $quote['amount_minor'], 'vendor_payable_minor' => $net - $quote['amount_minor'], 'rule' => $quote['snapshot']['name'], 'rate_bps' => $quote['bps']];
    }
}
