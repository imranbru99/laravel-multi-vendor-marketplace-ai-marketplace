<?php

namespace Tests\Feature;

use App\Domain\AccessManager;
use App\Filament\ResourcePages\ManageOrderItems;
use App\Filament\Resources\InventoryBalanceResource;
use App\Filament\Resources\OrderItemResource;
use App\Models\AccessRole;
use App\Models\Branch;
use App\Models\InventoryBalance;
use App\Models\RoleAssignment;
use App\Models\User;
use App\Models\Warehouse;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\MarketplaceTestCase;

class BranchPermissionsTest extends MarketplaceTestCase
{
    public function test_branch_operator_can_ship_only_allocated_line_units(): void
    {
        $order = $this->place();
        $this->capture($order);
        $branchId = $this->stocks[0]->warehouse->branch_id;
        $operator = User::create(['name' => 'Branch operator', 'email' => 'branch-operator@example.test', 'password' => 'TestPassword123', 'tenant_id' => $this->tenant->id, 'vendor_id' => $this->offers[0]->vendor_id, 'branch_id' => $branchId, 'role' => 'branch']);
        $this->actingAs($operator);
        $this->assertSame(1, OrderItemResource::getEloquentQuery()->count());
        $this->assertSame(1, InventoryBalanceResource::getEloquentQuery()->count());
        $this->assertFalse(InventoryBalanceResource::canCreate());
        Filament::setCurrentPanel(Filament::getPanel('branch'));
        $line = $order->items()->where('vendor_id', $operator->vendor_id)->first();
        Livewire::test(ManageOrderItems::class)->callTableAction('shipAllocatedItems', $line, data: ['carrier' => 'Manual demo', 'tracking' => 'BRANCH-TEST', 'quantity' => 1])->assertHasNoTableActionErrors();
        $this->assertSame(1, $line->fresh()->shipped_quantity);
        $this->assertSame(0, $order->items()->where('vendor_id', $this->offers[1]->vendor_id)->first()->shipped_quantity);
    }

    public function test_branch_scopes_exclude_a_second_branch_of_the_same_vendor(): void
    {
        $v = $this->offers[0]->vendor_id;
        $branch = Branch::create(['vendor_id' => $v, 'name' => 'Second', 'address' => 'Example']);
        $w = Warehouse::create(['vendor_id' => $v, 'branch_id' => $branch->id, 'name' => 'Second', 'location' => 'B1']);
        InventoryBalance::create(['vendor_id' => $v, 'offer_id' => $this->offers[0]->id, 'warehouse_id' => $w->id]);
        $operator = User::create(['name' => 'Scoped operator', 'email' => 'scoped-operator@example.test', 'password' => 'TestPassword123', 'tenant_id' => $this->tenant->id, 'vendor_id' => $v, 'branch_id' => $branch->id, 'role' => 'warehouse']);
        $this->actingAs($operator);
        $this->assertSame(1, InventoryBalanceResource::getEloquentQuery()->count());
        $this->assertFalse(InventoryBalanceResource::getEloquentQuery()->whereKey($this->stocks[0]->id)->exists());
        $this->assertFalse(app(AccessManager::class)->allows($operator, 'returns.refund'));
    }

    public function test_role_revocation_removes_custom_permissions(): void
    {
        $staff = User::create(['name' => 'Reader', 'email' => 'revoked@example.test', 'password' => 'TestPassword123', 'tenant_id' => $this->tenant->id, 'role' => 'staff']);
        $role = AccessRole::create(['name' => 'Reader', 'key' => 'read', 'scope' => 'tenant', 'permissions' => ['entity.Product.view']]);
        $assignment = app(AccessManager::class)->assign(['user_id' => $staff->id, 'access_role_id' => $role->id]);
        $this->assertTrue(app(AccessManager::class)->entity($staff, 'Product'));
        app(AccessManager::class)->revoke($assignment);
        $this->assertFalse(app(AccessManager::class)->entity($staff, 'Product'));
        $this->assertSame(0, RoleAssignment::count());
    }
}
