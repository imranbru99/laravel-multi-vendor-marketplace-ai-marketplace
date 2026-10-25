<?php

namespace App\Domain;

use App\Models\Employee;
use App\Models\InventoryBalance;
use App\Models\OrderItem;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;

final class BranchAccess
{
    public static function scope(Builder $query, string $entity, int $branchId): Builder
    {
        $warehouses = Warehouse::where('branch_id', $branchId)->select('id');
        $balances = InventoryBalance::whereIn('warehouse_id', $warehouses)->select('id');

        return match ($entity) {
            'Branch' => $query->whereKey($branchId),
            'Warehouse','Employee' => $query->where('branch_id', $branchId),
            'InventoryBalance' => $query->whereIn('warehouse_id', $warehouses),
            'InventoryMovement','OrderItem','PurchaseOrder' => $query->whereIn('inventory_balance_id', $balances),
            'ReturnRequest' => $query->whereIn('order_item_id', OrderItem::whereIn('inventory_balance_id', $balances)->select('id')),
            'Shipment' => $query->whereHas('items', fn ($q) => $q->whereIn('order_item_id', OrderItem::whereIn('inventory_balance_id', $balances)->select('id')))->whereDoesntHave('items', fn ($q) => $q->whereNotIn('order_item_id', OrderItem::whereIn('inventory_balance_id', $balances)->select('id'))),
            'AttendanceRecord' => $query->whereIn('employee_id', Employee::where('branch_id', $branchId)->select('id')),
            default => $query->whereRaw('1=0'),
        };
    }
}
