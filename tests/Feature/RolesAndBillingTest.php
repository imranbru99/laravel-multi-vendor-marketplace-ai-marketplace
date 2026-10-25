<?php

namespace Tests\Feature;

use App\Domain\AccessManager;
use App\Domain\BusinessRules;
use App\Domain\ModuleManager;
use App\Domain\Subscriptions;
use App\Filament\Resources\OfferResource;
use App\Models\AccessRole;
use App\Models\LedgerEntry;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\SubscriptionPlanSeeder;
use Filament\Facades\Filament;
use Illuminate\Validation\ValidationException;
use Tests\MarketplaceTestCase;

class RolesAndBillingTest extends MarketplaceTestCase
{
    public function test_vendor_staff_role_is_scoped_and_read_only(): void
    {
        $staff = User::create(['name' => 'Catalog reader', 'email' => 'catalog-reader@example.test', 'password' => 'TestPassword123', 'tenant_id' => $this->tenant->id, 'vendor_id' => $this->offers[0]->vendor_id, 'role' => 'staff']);
        $role = AccessRole::create(['name' => 'Offer reader', 'key' => 'offer-reader', 'scope' => 'vendor', 'vendor_id' => $staff->vendor_id, 'permissions' => ['entity.Offer.view']]);
        app(AccessManager::class)->assign(['user_id' => $staff->id, 'access_role_id' => $role->id]);
        $this->actingAs($staff);
        $this->assertTrue(OfferResource::canViewAny());
        $this->assertSame(1, OfferResource::getEloquentQuery()->count());
        $this->assertFalse(OfferResource::canCreate());
        $this->assertFalse(OfferResource::canEdit($this->offers[0]));
        $this->assertTrue($staff->canAccessPanel(Filament::getPanel('vendor')));
        $this->assertFalse($staff->canAccessPanel(Filament::getPanel('tenant')));
    }

    public function test_explicit_deny_overrides_legacy_manager_permissions(): void
    {
        $staff = User::create(['name' => 'Manager', 'email' => 'denied-manager@example.test', 'password' => 'TestPassword123', 'tenant_id' => $this->tenant->id, 'vendor_id' => $this->offers[0]->vendor_id, 'role' => 'manager']);
        $role = AccessRole::create(['name' => 'No price changes', 'key' => 'no-prices', 'scope' => 'vendor', 'vendor_id' => $staff->vendor_id, 'permissions' => [], 'denied_permissions' => ['entity.Offer.update']]);
        app(AccessManager::class)->assign(['user_id' => $staff->id, 'access_role_id' => $role->id]);
        $this->actingAs($staff);
        $this->assertTrue(OfferResource::canViewAny());
        $this->assertFalse(OfferResource::canEdit($this->offers[0]));
    }

    public function test_role_cannot_be_assigned_to_another_vendor(): void
    {
        $role = AccessRole::create(['name' => 'Reader', 'key' => 'reader', 'scope' => 'vendor', 'vendor_id' => $this->offers[0]->vendor_id, 'permissions' => ['entity.Offer.view']]);
        $staff = User::create(['name' => 'Other seller', 'email' => 'other-role@example.test', 'password' => 'TestPassword123', 'tenant_id' => $this->tenant->id, 'vendor_id' => $this->offers[1]->vendor_id, 'role' => 'staff']);
        $this->expectException(ValidationException::class);
        app(AccessManager::class)->assign(['user_id' => $staff->id, 'access_role_id' => $role->id]);
    }

    private function plans(): void
    {
        $this->seed(SubscriptionPlanSeeder::class);
    }

    public function test_sandbox_plan_upgrade_changes_branch_entitlement_and_posts_balanced_invoice(): void
    {
        $this->plans();
        app(Subscriptions::class)->change(SubscriptionPlan::where('slug', 'starter')->first());
        $this->assertFalse(app(ModuleManager::class)->enabled('b2b'));
        $this->assertSame(1, SubscriptionInvoice::count());
        $this->assertSame(0, (int) LedgerEntry::sum('amount_minor'));
        app(Subscriptions::class)->change(SubscriptionPlan::where('slug', 'growth')->first());
        $this->assertSame(25, $this->tenant->fresh()->branch_limit);
        $branch = app(BusinessRules::class)->branch(['vendor_id' => $this->offers[0]->vendor_id, 'name' => 'Third', 'address' => 'Example']);
        $this->assertNotNull($branch->id);
        app(ModuleManager::class)->toggle('b2b', true);
        $this->assertTrue(app(ModuleManager::class)->enabled('b2b'));
    }

    public function test_cancelled_renewal_preserves_access_until_period_end(): void
    {
        $this->plans();
        $s = app(Subscriptions::class)->change(SubscriptionPlan::where('slug', 'growth')->first(), true);
        app(Subscriptions::class)->cancel($s);
        $this->assertTrue(app(ModuleManager::class)->enabled('checkout'));
        $s->update(['period_ends_at' => now()->subMinute()]);
        app(Subscriptions::class)->renewDue();
        $this->assertSame('expired', $s->fresh()->status);
        $this->assertFalse(app(ModuleManager::class)->enabled('checkout'));
        $this->assertTrue(app(ModuleManager::class)->enabled('catalog'));
    }

    public function test_due_sandbox_renewal_is_idempotent_for_the_same_period(): void
    {
        $this->plans();
        $s = app(Subscriptions::class)->change(SubscriptionPlan::where('slug', 'growth')->first(), true);
        $s->update(['period_ends_at' => now()->subMinute()]);
        app(Subscriptions::class)->renewDue();
        app(Subscriptions::class)->renewDue();
        $this->assertSame(2, SubscriptionInvoice::count());
        $this->assertTrue($s->fresh()->period_ends_at->isFuture());
    }
}
