<?php

namespace Tests\Feature;

use App\Domain\AccessManager;
use App\Filament\Widgets\MarketplaceStats;
use App\Models\AccessRole;
use App\Models\User;
use Livewire\Livewire;
use Tests\MarketplaceTestCase;

class DashboardPermissionsTest extends MarketplaceTestCase
{
    public function test_catalog_staff_cannot_read_finance_or_stock_aggregates(): void
    {
        $staff = User::create(['name' => 'Catalog staff', 'email' => 'catalog-stats@example.test', 'password' => 'password123', 'tenant_id' => $this->tenant->id, 'role' => 'staff']);
        $role = AccessRole::create(['name' => 'Catalog reader', 'key' => 'catalog-reader', 'scope' => 'tenant', 'permissions' => ['entity.Product.view']]);
        app(AccessManager::class)->assign(['user_id' => $staff->id, 'access_role_id' => $role->id]);
        $this->actingAs($staff);
        Livewire::test(MarketplaceStats::class)->assertDontSee('Captured merchandise')->assertDontSee('Available units')->assertDontSee('Vendor orders');
    }
}
