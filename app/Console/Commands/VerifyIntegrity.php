<?php

namespace App\Console\Commands;

use App\Domain\TenantContext;
use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use App\Models\LedgerTransaction;
use App\Models\Order;
use App\Models\Tenant;
use Illuminate\Console\Command;

class VerifyIntegrity extends Command
{
    protected $signature = 'marketplace:verify {--json}';

    protected $description = 'Read-only verification of stock movement reconciliation, order arithmetic and balanced journals';

    public function handle(): int
    {
        $report = ['tenants' => 0, 'orders' => 0, 'journals' => 0, 'stock_locations' => 0, 'errors' => []];
        foreach (Tenant::cursor() as $tenant) {
            app(TenantContext::class)->set($tenant);
            $report['tenants']++;
            foreach (LedgerTransaction::with('entries')->cursor() as $tx) {
                $report['journals']++;
                if ($tx->entries->sum('amount_minor') !== 0) {
                    $report['errors'][] = 'Unbalanced journal '.$tx->id;
                }
            }
            foreach (InventoryBalance::cursor() as $stock) {
                $report['stock_locations']++;
                $movements = InventoryMovement::where('inventory_balance_id', $stock->id);
                if ((int) (clone $movements)->sum('quantity_delta') !== $stock->quantity || (int) (clone $movements)->sum('reserved_delta') !== $stock->reserved || $stock->reserved > $stock->quantity) {
                    $report['errors'][] = 'Stock reconciliation '.$stock->id;
                }
            }
            foreach (Order::cursor() as $order) {
                $report['orders']++;
                if ($order->subtotal_minor - $order->discount_minor + $order->tax_minor + $order->shipping_minor !== $order->total_minor || $order->refunded_minor > $order->total_minor) {
                    $report['errors'][] = 'Order arithmetic '.$order->id;
                }
            }
        }
        app(TenantContext::class)->clear();
        $this->line(json_encode($report, JSON_PRETTY_PRINT));

        return $report['errors'] ? self::FAILURE : self::SUCCESS;
    }
}
