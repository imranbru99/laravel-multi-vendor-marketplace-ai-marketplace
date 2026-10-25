<?php

namespace App\Domain;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class OperationsAccess
{
    public static function roles(string $entity): array
    {
        $base = ['superadmin', 'owner', 'admin'];

        return array_merge($base, match ($entity) {
            'Vendor','Offer','VendorOrder','Quotation','ProductProposal','CatalogImport' => ['vendor', 'manager'],
            'InventoryBalance','InventoryMovement','OrderItem','Shipment','PurchaseOrder','Employee','AttendanceRecord' => ['vendor', 'manager', 'warehouse', 'branch'],
            'Branch','Warehouse' => ['vendor', 'manager', 'warehouse', 'branch'],
            'Settlement','Refund','LedgerTransaction','LedgerEntry','Payment' => ['finance'],
            'SupportTicket' => ['support'],
            'ReturnRequest' => ['support', 'warehouse', 'branch'],
            default => [],
        });
    }

    public static function scope(Builder $query, string $entity, ?User $user): Builder
    {
        if (! $user || ! app(AccessManager::class)->entity($user, $entity)) {
            return $query->whereRaw('1=0');
        }
        if ($user->vendor_id) {
            if ($entity === 'Vendor') {
                $query->whereKey($user->vendor_id);
            } else {
                if (in_array($entity, ['Offer', 'ProductProposal', 'CatalogImport', 'Branch', 'Warehouse', 'InventoryBalance', 'InventoryMovement', 'VendorOrder', 'OrderItem', 'Shipment', 'ReturnRequest', 'Refund', 'Settlement', 'LedgerEntry', 'Quotation', 'Booking', 'PurchaseOrder', 'Employee', 'AttendanceRecord', 'AccessRole'], true)) {
                    $query->where('vendor_id', $user->vendor_id);
                } elseif ($entity === 'Product') {
                    $query->whereHas('offers', fn ($q) => $q->where('vendor_id', $user->vendor_id));
                } else {
                    $query->whereRaw('1=0');
                }
            }
        }
        if ($user->branch_id) {
            $query = BranchAccess::scope($query, $entity, $user->branch_id);
        }

        return $query;
    }
}
