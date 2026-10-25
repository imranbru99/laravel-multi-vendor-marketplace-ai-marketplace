<?php

namespace App\Filament\Widgets;

use App\Domain\AccessManager;
use App\Domain\Money;
use App\Domain\OperationsAccess;
use App\Models\InventoryBalance;
use App\Models\VendorOrder;
use App\Models\Warehouse;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MarketplaceStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        $access = app(AccessManager::class);
        $stats = [];
        if ($user->branch_id && $access->entity($user, 'Warehouse')) {
            $stats[] = Stat::make('Branch warehouses', OperationsAccess::scope(Warehouse::query(), 'Warehouse', $user)->count());
        }
        if ($access->entity($user, 'VendorOrder')) {
            $orders = OperationsAccess::scope(VendorOrder::query(), 'VendorOrder', $user);
            $stats[] = Stat::make('Vendor orders', (clone $orders)->count())->description('Within your authorized scope');
            $stats[] = Stat::make('Captured merchandise', Money::format((clone $orders)->whereNotIn('status', ['pending_payment', 'cancelled'])->sum('subtotal_minor')))->description('Sandbox and manual operations');
        }
        if ($access->entity($user, 'InventoryBalance')) {
            $stock = OperationsAccess::scope(InventoryBalance::query(), 'InventoryBalance', $user);
            $stats[] = Stat::make('Available units', (clone $stock)->sum('quantity') - (clone $stock)->sum('reserved'));
            $stats[] = Stat::make('Low stock locations', (clone $stock)->whereRaw('quantity - reserved <= reorder_point')->count())->description('At or below reorder point');
        }

        return $stats;
    }
}
