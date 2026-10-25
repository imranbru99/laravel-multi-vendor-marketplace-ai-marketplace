<?php

namespace App\Domain;

use App\Domain\Modules\ModuleRegistry;
use App\Models\AttendanceRecord;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\InventoryBalance;
use App\Models\ModuleScopeOverride;
use App\Models\OrderItem;
use App\Models\PlatformModule;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\ReturnRequest;
use App\Models\Shipment;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\Vendor;
use App\Models\VendorOrder;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ModuleManager
{
    public function scopedKeys(): array
    {
        return ['inventory', 'checkout', 'payments', 'shipping', 'returns', 'digital', 'b2b', 'bookings', 'procurement', 'workforce', 'ai'];
    }

    public function enabled(string $key, ?int $vendorId = null, ?int $branchId = null, array $path = []): bool
    {
        $definition = config('marketplace.modules.'.$key);
        if (! $definition || in_array($key, $path, true)) {
            return false;
        }
        if (PlatformModule::where('key', $key)->where('enabled', false)->exists() || ! app(Subscriptions::class)->entitled($key) || ! TenantModule::where('key', $key)->where('enabled', true)->exists()) {
            return false;
        }
        $inferActor = $vendorId === null && $branchId === null;
        $vendorId ??= auth()->user()?->vendor_id;
        if ($inferActor) {
            $branchId ??= auth()->user()?->branch_id;
        }
        if ($branchId && ! $vendorId) {
            $vendorId = Branch::whereKey($branchId)->value('vendor_id');
        }
        if (! $definition['core'] && in_array($key, $this->scopedKeys(), true)) {
            $denied = ModuleScopeOverride::where('key', $key)->where('enabled', false)->where(function ($q) use ($vendorId, $branchId) {
                $q->whereRaw('1=0');
                if ($vendorId) {
                    $q->orWhere(fn ($v) => $v->where('scope', 'vendor')->where('subject_id', $vendorId));
                }if ($branchId) {
                    $q->orWhere(fn ($b) => $b->where('scope', 'branch')->where('subject_id', $branchId));
                }
            })->exists();
            if ($denied) {
                return false;
            }
        }
        if (auth()->check() && ! app(AccessManager::class)->allows(auth()->user(), 'module.'.$key.'.use', true)) {
            return false;
        }
        foreach ($definition['dependencies'] as $dependency) {
            if (! $this->enabled($dependency, $vendorId, $branchId, array_merge($path, [$key]))) {
                return false;
            }
        }

return true;
    }

    public function require(string $key, ?int $vendorId = null, ?int $branchId = null): void
    {
        abort_unless($this->enabled($key, $vendorId, $branchId), 403, 'This module is disabled or outside the current entitlement.');
    }

    public function transaction(string $key, callable $operation, ?int $vendorId = null, ?int $branchId = null): mixed
    {
        return DB::transaction(function () use ($key, $operation, $vendorId, $branchId) {
            Tenant::whereKey(app(TenantContext::class)->id())->lockForUpdate()->firstOrFail();
            $this->require($key, $vendorId, $branchId);

            return $operation();
        }, 5);
    }

    public function hasObligations(string $key, ?int $vendorId = null, ?int $branchId = null): bool
    {
        $balances = $branchId ? InventoryBalance::whereIn('warehouse_id', Warehouse::where('branch_id', $branchId)->select('id'))->select('id') : null;
        $suborders = VendorOrder::query()->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))->when($branchId, fn ($q) => $q->whereHas('items', fn ($i) => $i->whereIn('inventory_balance_id', $balances)));

        return match ($key) {
            'checkout','inventory','payments','shipping' => (clone $suborders)->whereNotIn('status', ['completed', 'cancelled', 'refunded'])->exists(),
            'returns' => ReturnRequest::query()->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))->when($branchId, fn ($q) => $q->whereIn('order_item_id', OrderItem::whereIn('inventory_balance_id', $balances)->select('id')))->whereIn('status', ['requested', 'authorized', 'received'])->exists() || Shipment::whereIn('vendor_order_id', (clone $suborders)->select('id'))->where('status', 'delivered')->where('delivered_at', '>=', now()->subDays(14))->exists(),
            'b2b' => Quotation::query()->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))->whereIn('status', ['requested', 'quoted', 'accepted'])->exists(),
            'bookings' => Booking::query()->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))->where('status', 'confirmed')->where('starts_at', '>', now())->exists(),
            'procurement' => PurchaseOrder::query()->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))->when($branchId, fn ($q) => $q->whereIn('inventory_balance_id', $balances))->whereIn('status', ['draft', 'ordered'])->exists(),
            'workforce' => AttendanceRecord::query()->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))->when($branchId, fn ($q) => $q->whereIn('employee_id', Employee::where('branch_id', $branchId)->select('id')))->whereNull('clocked_out_at')->exists(),
            default => false,
        };
    }

    private function validateToggle(string $key, bool $enabled, ?int $vendorId = null, ?int $branchId = null): void
    {
        $registry = config('marketplace.modules');
        $definition = $registry[$key] ?? throw ValidationException::withMessages(['module' => 'Unknown module.']);
        if (! $enabled && $definition['core']) {
            throw ValidationException::withMessages(['module' => 'Core modules cannot be disabled.']);
        }
        if ($enabled) {
            if (! app(Subscriptions::class)->entitled($key) || PlatformModule::where('key', $key)->where('enabled', false)->exists()) {
                throw ValidationException::withMessages(['module' => 'The plan or platform does not permit this module.']);
            }foreach ($definition['dependencies'] as $dependency) {
                if (! $this->enabled($dependency, $vendorId, $branchId)) {
                    throw ValidationException::withMessages(['module' => 'Enable dependency '.$dependency.' first.']);
                }
            }
        } else {
            foreach ($registry as $name => $module) {
                if (((! $vendorId && ! $branchId) || in_array($name, $this->scopedKeys(), true)) && in_array($key, $module['dependencies'], true) && $this->enabled($name, $vendorId, $branchId)) {
                    throw ValidationException::withMessages(['module' => 'Disable dependent module '.$name.' first.']);
                }
            }if ($this->hasObligations($key, $vendorId, $branchId)) {
                throw ValidationException::withMessages(['module' => 'Active obligations prevent deactivation.']);
            }
        }
    }

    public function toggle(string $key, bool $enabled): void
    {
        DB::transaction(function () use ($key, $enabled) {
            Tenant::whereKey(app(TenantContext::class)->id())->lockForUpdate()->firstOrFail();
            app(ModuleRegistry::class)->all();
            $this->validateToggle($key, $enabled);
            TenantModule::updateOrCreate(['key' => $key], ['enabled' => $enabled]);
            Audit::record('module.toggled', $key, ['enabled' => $enabled]);
        }, 5);
    }

    public function toggleScoped(string $key, string $scope, int $subjectId, bool $enabled): void
    {
        DB::transaction(function () use ($key, $scope, $subjectId, $enabled) {
            Tenant::whereKey(app(TenantContext::class)->id())->lockForUpdate()->firstOrFail();
            if (! in_array($key, $this->scopedKeys(), true) || ! in_array($scope, ['vendor', 'branch'], true)) {
                throw ValidationException::withMessages(['module' => 'This module does not support the chosen scope.']);
            }
            $branch = $scope === 'branch' ? Branch::findOrFail($subjectId) : null;
            $vendor = Vendor::findOrFail($branch?->vendor_id ?? $subjectId);
            $actor = auth()->user();
            if ($actor) {
                abort_if($actor->vendor_id && $actor->vendor_id !== $vendor->id, 403);
                abort_if($actor->branch_id && ($scope !== 'branch' || $actor->branch_id !== $branch?->id), 403);
                abort_unless($actor->managesMarketplace() || app(AccessManager::class)->allows($actor, 'modules.manage_scoped', in_array($actor->role, ['vendor', 'manager', 'warehouse', 'branch'], true)), 403);
            }
            if ($enabled && ! TenantModule::where('key', $key)->where('enabled', true)->exists()) {
                throw ValidationException::withMessages(['module' => 'The marketplace must enable this module first.']);
            }
            $this->validateToggle($key, $enabled, $vendor->id, $branch?->id);
            ModuleScopeOverride::updateOrCreate(['key' => $key, 'scope' => $scope, 'subject_id' => $subjectId], ['enabled' => $enabled]);
            Audit::record('module.scope_toggled', $key, ['scope' => $scope, 'subject_id' => $subjectId, 'enabled' => $enabled]);
        }, 5);
    }

    public function togglePlatform(string $key, bool $enabled): void
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);
        $definition = config('marketplace.modules.'.$key);
        if (! $definition || (! $enabled && $definition['core'])) {
            throw ValidationException::withMessages(['module' => 'Unknown or protected core module.']);
        }
        $context = app(TenantContext::class);
        $previous = $context->get();
        try {
            DB::transaction(function () use ($key, $enabled, $context) {
                $tenants = Tenant::orderBy('id')->lockForUpdate()->get();
                foreach ($tenants as $tenant) {
                    $context->set($tenant);
                    if (! $enabled) {
                        $this->validateToggle($key,false);
                    } else {
                        foreach (config('marketplace.modules.'.$key.'.dependencies',[]) as $dependency) {
                            if (PlatformModule::where('key',$dependency)->where('enabled',false)->exists()) {
                                throw ValidationException::withMessages(['module' => 'Enable platform dependency '.$dependency.' first.']);
                            }
                        }
                    }
                }PlatformModule::updateOrCreate(['key' => $key],['enabled' => $enabled]);
            }, 5);
        } finally {
            $context->set($previous);
        }Audit::record('module.platform_toggled',$key,['enabled' => $enabled]);
    }
}
