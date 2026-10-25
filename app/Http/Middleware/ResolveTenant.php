<?php

namespace App\Http\Middleware;

use App\Domain\TenantContext;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;

final class ResolveTenant
{
    public function handle(Request $request, Closure $next)
    {
        $context = app(TenantContext::class);
        $user = $request->user();
        $selected = $user?->role === 'superadmin' && $request->hasSession() ? $request->session()->get('platform_tenant_id') : null;
        $tenant = $selected ? Tenant::find($selected) : null;
        $tenant ??= $user && $user->tenant_id ? Tenant::find($user->tenant_id) : Tenant::where('domain', $request->getHost())->first();
        $tenant ??= Tenant::where('slug', config('marketplace.default_tenant'))->first();
        abort_unless($tenant && $tenant->status === 'active', 503, 'Marketplace is not available. Run marketplace:install.');
        $previous = null;
        try {
            $previous = $context->get();
        } catch (\LogicException) {
        }
        $context->set($tenant);
        $locale = $request->hasSession() ? $request->session()->get('locale', $tenant->locale) : $tenant->locale;
        app()->setLocale(in_array($locale, ['en', 'bn']) ? $locale : 'en');
        try {
            return $next($request);
        } finally {
            $previous ? $context->set($previous) : $context->clear();
        }
    }
}
