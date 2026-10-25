<?php

namespace App\Domain;

use App\Models\AccessRole;
use App\Models\AttendanceRecord;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\CatalogImport;
use App\Models\CommissionRule;
use App\Models\DigitalAsset;
use App\Models\Employee;
use App\Models\InventoryBalance;
use App\Models\Offer;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductProposal;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\ShippingRule;
use App\Models\TaxRule;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Defense in depth: all local FK references must belong to the active tenant. */
final class ScopeGuard
{
    public function saving(Model $model): void
    {
        $tenant = app(TenantContext::class)->id();
        if ($model->exists && $model->isDirty('vendor_id')) {
            throw ValidationException::withMessages(['vendor_id' => 'Seller ownership is immutable.']);
        }
        if ($model instanceof Offer && $model->exists && $model->isDirty('product_id')) {
            throw ValidationException::withMessages(['product_id' => 'An existing offer cannot be reassigned to another product.']);
        }
        if ($model instanceof AttendanceRecord && ! Employee::whereKey($model->employee_id)->where('vendor_id', $model->vendor_id)->exists()) {
            throw ValidationException::withMessages(['employee_id' => 'Employee belongs to another seller.']);
        }

        if ($model instanceof OrderItem && $model->exists && array_intersect(array_keys($model->getDirty()), ['unit_minor', 'quantity', 'discount_minor', 'tax_minor', 'total_minor', 'commission_bps', 'commission_minor', 'commission_rule_id', 'commission_snapshot', 'variant_snapshot', 'tax_rule_id', 'tax_snapshot', 'offer_id', 'inventory_balance_id', 'name', 'sku', 'product_type', 'digital_asset_id'])) {
            throw ValidationException::withMessages(['order' => 'Historical order pricing and allocation snapshots are immutable.']);
        }
        if ($model instanceof CommissionRule) {
            if ($model->percentage_bps < 0 || $model->percentage_bps > 10000 || ($model->maximum_minor !== null && $model->minimum_minor > $model->maximum_minor) || ($model->to_subtotal_minor !== null && $model->from_subtotal_minor > $model->to_subtotal_minor)) {
                throw ValidationException::withMessages(['rule' => 'Invalid commission rate or limits.']);
            }
        }
        if ($model instanceof TaxRule && ($model->percentage_bps < 0 || $model->percentage_bps > 10000)) {
            throw ValidationException::withMessages(['rule' => 'Invalid tax rate.']);
        }
        if ($model instanceof TaxRule || $model instanceof CommissionRule) {
            if ($model->effective_from && $model->effective_until && $model->effective_until->lte($model->effective_from)) {
                throw ValidationException::withMessages(['effective_until' => 'End date must follow the start date.']);
            }
        }
        if (($model instanceof TaxRule || $model instanceof ShippingRule) && $model->country) {
            $model->country = strtoupper($model->country);
            if (! preg_match('/^[A-Z]{2}$/', $model->country)) {
                throw ValidationException::withMessages(['country' => 'Use a two-letter country code.']);
            }
        }
        if ($model instanceof ShippingRule && $model->branch_id && $model->vendor_id && ! Branch::whereKey($model->branch_id)->where('vendor_id', $model->vendor_id)->exists()) {
            throw ValidationException::withMessages(['branch_id' => 'Shipping branch belongs to another seller.']);
        }
        if ($model instanceof Offer && $model->product_variant_id && ! ProductVariant::whereKey($model->product_variant_id)->where('product_id', $model->product_id)->exists()) {
            throw ValidationException::withMessages(['product_variant_id' => 'Variant belongs to another master product.']);
        }
        if (($model instanceof ProductProposal || $model instanceof CatalogImport) && ! Warehouse::whereKey($model->warehouse_id)->where('vendor_id', $model->vendor_id)->exists()) {
            throw ValidationException::withMessages(['warehouse_id' => 'Warehouse belongs to another seller.']);
        }
        if ($model instanceof ProductProposal && $model->image && (! str_starts_with($model->image, 'tenants/'.$tenant.'/proposals/') || str_contains($model->image, '..'))) {
            throw ValidationException::withMessages(['image' => 'Use private tenant proposal storage.']);
        }
        if ($model instanceof Product && $model->isDirty('gallery')) {
            foreach ($model->gallery ?? [] as $path) {
                if (! str_starts_with($path, 'tenants/'.$tenant.'/catalog/') || str_contains($path, '..')) {
                    throw ValidationException::withMessages(['gallery' => 'Use public catalog storage for the current tenant.']);
                }
            }
        }
        $references = ['product_variant_id' => 'product_variants', 'vendor_id' => 'vendors', 'branch_id' => 'branches', 'warehouse_id' => 'warehouses', 'category_id' => 'categories', 'brand_id' => 'brands', 'product_id' => 'products', 'offer_id' => 'offers', 'cart_id' => 'carts', 'order_id' => 'orders', 'vendor_order_id' => 'vendor_orders', 'inventory_balance_id' => 'inventory_balances', 'order_item_id' => 'order_items', 'return_request_id' => 'return_requests', 'payment_id' => 'payments', 'ledger_transaction_id' => 'ledger_transactions', 'employee_id' => 'employees', 'commission_rule_id' => 'commission_rules', 'tax_rule_id' => 'tax_rules', 'shipment_id' => 'shipments', 'tenant_subscription_id' => 'tenant_subscriptions', 'access_role_id' => 'access_roles', 'digital_asset_id' => 'digital_assets', 'user_id' => 'users'];
        foreach ($references as $column => $table) {
            if ($model->getAttribute($column) && $model->isDirty($column)) {
                if ($model instanceof AuditLog && $column === 'user_id' && auth()->user()?->role === 'superadmin' && $model->user_id === auth()->id()) {
                    continue;
                }
                if (! DB::table($table)->where('id', $model->getAttribute($column))->where('tenant_id', $tenant)->exists()) {
                    throw ValidationException::withMessages([$column => 'Reference does not belong to this marketplace.']);
                }
            }
        }
        if ($model instanceof Warehouse || $model instanceof Employee || ($model instanceof AccessRole && $model->branch_id)) {
            if (! Branch::whereKey($model->branch_id)->where('vendor_id', $model->vendor_id)->exists()) {
                throw ValidationException::withMessages(['branch_id' => 'Branch belongs to another vendor.']);
            }
        }
        if ($model instanceof DigitalAsset) {
            if (! str_starts_with($model->path, 'tenants/'.$tenant.'/digital/') || str_contains($model->path, '..') || ! $model->rights_confirmed) {
                throw ValidationException::withMessages(['path' => 'Use private tenant digital storage and confirm distribution rights.']);
            }
        }
        if ($model instanceof PurchaseOrder) {
            if (! InventoryBalance::whereKey($model->inventory_balance_id)->where('offer_id', $model->offer_id)->where('vendor_id', $model->vendor_id)->exists()) {
                throw ValidationException::withMessages(['inventory_balance_id' => 'Purchase inventory must match the offer and vendor.']);
            }
        }
        if ($model instanceof InventoryBalance) {
            if (! Offer::whereKey($model->offer_id)->where('vendor_id', $model->vendor_id)->exists() || ! Warehouse::whereKey($model->warehouse_id)->where('vendor_id', $model->vendor_id)->exists()) {
                throw ValidationException::withMessages(['offer_id' => 'Stock location and offer must share a vendor.']);
            }
        }
    }
}
