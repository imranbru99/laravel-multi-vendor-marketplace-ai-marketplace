<?php

namespace Tests\Feature;

use App\Domain\AccessManager;
use App\Domain\Inventory;
use App\Domain\ModuleManager;
use App\Domain\TenantContext;
use App\Domain\TenantSetup;
use App\Models\AccessRole;
use App\Models\Branch;
use App\Models\InventoryBalance;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\MarketplaceTestCase;

class ModuleScopesTest extends MarketplaceTestCase
{
    public function test_seller_restriction_does_not_disable_another_seller(): void
    {
        $m = app(ModuleManager::class);
        $m->toggleScoped('b2b', 'vendor', $this->offers[0]->vendor_id, false);
        $this->assertFalse($m->enabled('b2b', $this->offers[0]->vendor_id));
        $this->assertTrue($m->enabled('b2b', $this->offers[1]->vendor_id));
        $m->toggleScoped('b2b', 'vendor', $this->offers[0]->vendor_id, true);
        $this->assertTrue($m->enabled('b2b', $this->offers[0]->vendor_id));
    }

    public function test_branch_restriction_inherits_and_cannot_override_parent(): void
    {
        $m = app(ModuleManager::class);
        $branch = $this->stocks[0]->warehouse->branch_id;
        $m->toggleScoped('workforce', 'branch', $branch, false);
        $this->assertFalse($m->enabled('workforce', $this->offers[0]->vendor_id, $branch));
        $this->assertTrue($m->enabled('workforce', $this->offers[0]->vendor_id));
        $m->toggle('workforce', false);
        $this->expectException(ValidationException::class);
        $m->toggleScoped('workforce', 'branch', $branch, true);
    }

    public function test_seller_cannot_restrict_another_sellers_modules(): void
    {
        $user = User::create(['name' => 'Seller', 'email' => 'module-seller@example.test', 'password' => 'TestPassword123', 'tenant_id' => $this->tenant->id, 'vendor_id' => $this->offers[0]->vendor_id, 'role' => 'vendor']);
        $this->actingAs($user);
        $this->expectException(HttpException::class);
        app(ModuleManager::class)->toggleScoped('workforce', 'vendor', $this->offers[1]->vendor_id, false);
    }

    public function test_platform_restriction_applies_to_all_tenants(): void
    {
        $this->admin->update(['role' => 'superadmin']);
        $this->actingAs($this->admin);
        $second = Tenant::create(['name' => 'Second', 'slug' => 'second']);
        app(TenantSetup::class)->initialize($second);
        app(ModuleManager::class)->togglePlatform('workforce', false);
        $this->assertFalse(app(ModuleManager::class)->enabled('workforce'));
        app(TenantContext::class)->set($second);
        $this->assertFalse(app(ModuleManager::class)->enabled('workforce'));
        $this->context();
        app(ModuleManager::class)->togglePlatform('workforce', true);
        $this->assertTrue(app(ModuleManager::class)->enabled('workforce'));
    }

    public function test_role_can_deny_a_module_without_affecting_other_users(): void
    {
        $role = AccessRole::create(['name' => 'No wholesale', 'key' => 'no-wholesale', 'scope' => 'tenant', 'permissions' => [], 'denied_permissions' => ['module.b2b.use']]);
        app(AccessManager::class)->assign(['user_id' => $this->customer->id, 'access_role_id' => $role->id]);
        $this->actingAs($this->customer);
        $this->assertFalse(app(ModuleManager::class)->enabled('b2b'));
        $this->actingAs($this->admin);
        $this->assertTrue(app(ModuleManager::class)->enabled('b2b'));
    }

    public function test_checkout_skips_a_restricted_branch_and_allocates_an_eligible_warehouse(): void
    {
        $vendor = $this->offers[0]->vendor_id;
        $oldBranch = $this->stocks[0]->warehouse->branch_id;
        $branch = Branch::create(['vendor_id' => $vendor, 'name' => 'Other branch', 'address' => 'Example']);
        $warehouse = Warehouse::create(['vendor_id' => $vendor, 'branch_id' => $branch->id, 'name' => 'Eligible warehouse', 'location' => 'A2']);
        $stock = InventoryBalance::create(['vendor_id' => $vendor, 'offer_id' => $this->offers[0]->id, 'warehouse_id' => $warehouse->id]);
        app(Inventory::class)->change($stock, 5, 0, 'Initial', 'module-allocation');
        foreach (['shipping', 'returns', 'digital', 'payments', 'checkout', 'procurement', 'inventory'] as $key) {
            app(ModuleManager::class)->toggleScoped($key, 'branch', $oldBranch, false);
        }
        $order = $this->place($this->cart(1, false));
        $this->assertSame($stock->id, $order->items->first()->inventory_balance_id);
        $this->assertSame(0,$this->stocks[0]->fresh()->reserved);
    }
}
