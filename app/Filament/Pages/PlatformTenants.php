<?php

namespace App\Filament\Pages;

use App\Domain\Audit;
use App\Domain\Subscriptions;
use App\Domain\TenantContext;
use App\Domain\TenantSetup;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class PlatformTenants extends Page
{
    protected string $view = 'filament.pages.tenants';

    public string $name = '';

    public string $tenantSlug = '';

    public string $domain = '';

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'superadmin';
    }

    public function createTenant(): void
    {
        abort_unless(static::canAccess(), 403);
        $data = $this->validate(['name' => 'required|string|max:100', 'tenantSlug' => 'required|alpha_dash|max:80|unique:tenants,slug', 'domain' => 'nullable|regex:/^[a-z0-9.-]+$/|unique:tenants,domain']);
        $data['slug'] = $data['tenantSlug'];
        unset($data['tenantSlug']);
        $data['domain'] = $data['domain'] ?: null;
        DB::transaction(function () use ($data) {
            $tenant = Tenant::create($data);
            app(TenantSetup::class)->initialize($tenant);
            app(TenantSetup::class)->seedContent($tenant);
            Audit::record('tenant.created', (string) $tenant->id);
        });
        $this->reset(['name', 'tenantSlug', 'domain']);
    }

    public function switchTenant(int $id): void
    {
        abort_unless(static::canAccess(), 403);
        $tenant = Tenant::findOrFail($id);
        abort_unless($tenant->status === 'active', 422);
        session(['platform_tenant_id' => $id]);
        $this->redirect('/admin');
    }

    public function upgrade(int $id): void
    {
        abort_unless(static::canAccess(), 403);
        $context = app(TenantContext::class);
        $previous = $context->get();
        $context->set(Tenant::findOrFail($id));
        try {
            $plan = SubscriptionPlan::where('slug', 'growth')->firstOrFail();
            app(Subscriptions::class)->change($plan);
        } finally {
            $context->set($previous);
        }
    }
}
