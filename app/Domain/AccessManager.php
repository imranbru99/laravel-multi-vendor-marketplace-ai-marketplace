<?php

namespace App\Domain;

use App\Models\AccessRole;
use App\Models\RoleAssignment;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class AccessManager
{
    private array $cache = [];

    public function clear(): void
    {
        $this->cache = [];
    }

    public function registry(): array
    {
        $permissions = ['catalog.approve' => 'Approve seller product submissions', 'returns.refund' => 'Approve sandbox refunds', 'finance.settle' => 'Approve sandbox settlements', 'inventory.adjust' => 'Adjust inventory', 'inventory.transfer' => 'Transfer inventory', 'orders.ship' => 'Ship allocated order items', 'orders.deliver' => 'Record delivery', 'marketplace.manage' => 'Manage marketplace settings', 'modules.manage' => 'Manage module activation', 'users.manage' => 'Manage user access'];
        foreach (config('marketplace.modules') as $key => $definition) {
            $permissions['module.'.$key.'.use'] = 'Use '.$definition['name'];
        }
        $permissions['modules.manage_scoped'] = 'Manage own seller/branch module settings';
        foreach (config('resources') as $entity => $definition) {
            foreach (['view', 'create', 'update', 'execute'] as $operation) {
                $permissions['entity.'.$entity.'.'.$operation] = ucwords($operation.' '.preg_replace('/(?<!^)[A-Z]/', ' $0', $entity));
            }
        }

        return $permissions;
    }

    private function roles(User $user): array
    {
        $tenant = app(TenantContext::class)->id();
        $key = $tenant.':'.$user->id;

        return $this->cache[$key] ??= $user->tenant_id === $tenant ? RoleAssignment::with('role')->where('user_id', $user->id)->get()->pluck('role')->filter(function ($role) use ($user) {
            return $role && match ($role->scope) {
                'tenant' => ! $user->vendor_id && ! $user->branch_id,'vendor' => $role->vendor_id === $user->vendor_id && ! $user->branch_id,'branch' => $role->vendor_id === $user->vendor_id && $role->branch_id === $user->branch_id,default => false
            };
        })->all() : [];
    }

    public function allows(User $user, string $permission, bool $legacy = false): bool
    {
        $tenant = app(TenantContext::class)->id();
        if ($user->role === 'superadmin') {
            return true;
        }if ($user->tenant_id !== $tenant) {
            return false;
        }if ($user->role === 'owner') {
            return true;
        }
        $roles = $this->roles($user);
        foreach ($roles as $role) {
            if (in_array($permission, $role->denied_permissions ?? [], true)) {
                return false;
            }
        }
        foreach ($roles as $role) {
            if (in_array($permission, $role->permissions ?? [], true)) {
                return true;
            }
        }
        if (in_array($permission, ['marketplace.manage', 'modules.manage', 'users.manage'])) {
            return $user->role === 'admin';
        }
        $actionRoles = match ($permission) {
            'returns.refund' => ['admin', 'support', 'finance'],'finance.settle' => ['admin', 'finance'],'inventory.adjust','inventory.transfer','orders.ship','orders.deliver' => ['admin', 'vendor', 'manager', 'warehouse', 'branch'],default => null
        };

        return $actionRoles !== null ? in_array($user->role, $actionRoles, true) : $legacy;
    }

    public function entity(User $user, string $entity, string $operation = 'view'): bool
    {
        return $this->allows($user, 'entity.'.$entity.'.'.$operation, in_array($user->role, OperationsAccess::roles($entity), true));
    }

    public function panel(User $user, string $panel): bool
    {
        foreach ($this->roles($user) as $role) {
            if (match ($role->scope) {
                'tenant' => $panel === 'tenant','vendor' => $panel === 'vendor','branch' => $panel === 'branch',default => false
            }) {
                return true;
            }
        }

return false;
    }

    public function assign(array $data): RoleAssignment
    {
        $user = User::where('tenant_id', app(TenantContext::class)->id())->findOrFail($data['user_id']);
        $role = AccessRole::findOrFail($data['access_role_id']);
        $matches = match ($role->scope) {
            'tenant' => ! $user->vendor_id && ! $user->branch_id,'vendor' => $role->vendor_id === $user->vendor_id && ! $user->branch_id,'branch' => $role->vendor_id === $user->vendor_id && $role->branch_id === $user->branch_id,default => false
        };
        if (! $matches || in_array($user->role, ['owner', 'superadmin'], true)) {
            throw ValidationException::withMessages(['user_id' => 'Role scope must match the staff account. Protected owner/platform accounts cannot be changed.']);
        }
        $assignment = RoleAssignment::firstOrCreate(['user_id' => $user->id, 'access_role_id' => $role->id]);
        $this->clear();
        Audit::record('role.assigned', (string) $assignment->id);

        return $assignment;
    }

    public function revoke(RoleAssignment $assignment): void
    {
        $assignment = RoleAssignment::findOrFail($assignment->id);
        Audit::record('role.revoked', (string) $assignment->id);
        $assignment->delete();
        $this->clear();
    }

    public function validateRole(array $data): void
    {
        foreach (array_merge($data['permissions'] ?? [], $data['denied_permissions'] ?? []) as $permission) {
            if (! array_key_exists($permission, $this->registry())) {
                throw ValidationException::withMessages(['permissions' => 'Unknown permission.']);
            }
        }
        if (($data['scope'] ?? 'tenant') === 'tenant' && (! empty($data['vendor_id']) || ! empty($data['branch_id']))) {
            throw ValidationException::withMessages(['scope' => 'Tenant roles cannot carry seller or branch ownership.']);
        }
        if (in_array($data['scope'] ?? 'tenant', ['vendor', 'branch'], true) && empty($data['vendor_id'])) {
            throw ValidationException::withMessages(['vendor_id' => 'Seller-scoped roles require a seller.']);
        }
        if (($data['scope'] ?? 'tenant') === 'branch' && empty($data['branch_id'])) {
            throw ValidationException::withMessages(['branch_id' => 'Branch role requires its branch.']);
        }
        if (($data['scope'] ?? 'tenant') !== 'tenant' && array_intersect($data['permissions'] ?? [], ['marketplace.manage', 'modules.manage', 'users.manage'])) {
            throw ValidationException::withMessages(['permissions' => 'Marketplace access management cannot be granted to a seller or branch role.']);
        }
    }
}
