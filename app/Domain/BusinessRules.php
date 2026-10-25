<?php

namespace App\Domain;

use App\Models\Branch;
use App\Models\InventoryBalance;
use App\Models\PurchaseOrder;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class BusinessRules
{
    public function branch(array $data): Branch
    {
        return DB::transaction(function () use ($data) {
            app(Subscriptions::class)->quota('Branch');
            $tenant = Tenant::whereKey(app(TenantContext::class)->id())->lockForUpdate()->firstOrFail();
            if (Branch::count() >= $tenant->branch_limit) {
                throw ValidationException::withMessages(['name' => 'Your plan branch limit has been reached. Upgrade the plan.']);
            }

            return Branch::create($data);
        }, 5);
    }

    public function receive(PurchaseOrder $purchase): void
    {
        app(ModuleManager::class)->require('procurement');
        DB::transaction(function () use ($purchase) {
            $purchase = PurchaseOrder::whereKey($purchase->id)->lockForUpdate()->firstOrFail();
            if ($purchase->status === 'received') {
                return;
            }
            if (! in_array($purchase->status, ['draft', 'ordered'])) {
                throw ValidationException::withMessages(['purchase' => 'Purchase order cannot be received.']);
            }
            app(Inventory::class)->change(InventoryBalance::findOrFail($purchase->inventory_balance_id), $purchase->quantity, 0, 'purchase received', 'purchase:'.$purchase->id);
            $purchase->update(['status' => 'received']);
            Audit::record('purchase.received', (string) $purchase->id);
        }, 5);
    }
}
