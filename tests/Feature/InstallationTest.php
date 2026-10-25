<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InstallationTest extends TestCase
{
    use RefreshDatabase;

    public function test_secure_install_initializes_an_empty_application(): void
    {
        $this->artisan('marketplace:install', ['--email' => 'installed@example.test'])->expectsQuestion('New administrator password (minimum 12 characters)', 'InstalledTestPassword123')->assertSuccessful();
        $this->assertDatabaseHas('users', ['email' => 'installed@example.test', 'role' => 'superadmin']);
        $this->assertSame(1, Tenant::count());
        $this->assertGreaterThan(50, DB::table('content_pages')->count());
        $this->assertSame(count(config('marketplace.modules')), DB::table('tenant_modules')->count());
    }

    public function test_full_demo_seed_has_coherent_cross_tenant_data(): void
    {
        config(['marketplace.demo' => true, 'marketplace.sandbox' => true]);
        Storage::fake('local');
        $this->artisan('marketplace:demo-seed')->assertSuccessful();
        foreach (['tenants' => 2, 'vendors' => 5, 'branches' => 12, 'warehouses' => 8, 'categories' => 20, 'brands' => 30, 'products' => 204, 'offers' => 304, 'orders' => 250, 'return_requests' => 50, 'refunds' => 30, 'reviews' => 100, 'coupons' => 25, 'campaigns' => 10, 'tenant_subscriptions' => 5] as $table => $count) {
            $this->assertDatabaseCount($table, $count);
        }
        $this->assertSame(0, DB::table('ledger_entries')->sum('amount_minor'));
        $this->assertDatabaseHas('users', ['email' => 'superadmin@demo.test']);
        $this->assertDatabaseHas('users', ['email' => 'business@demo.test']);
        $this->artisan('marketplace:verify')->assertSuccessful();
    }
}
