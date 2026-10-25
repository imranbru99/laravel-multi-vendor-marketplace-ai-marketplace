<?php

namespace App\Providers;

use App\Domain\AccessManager;
use App\Domain\RequestDiagnostics;
use App\Domain\TenantContext;
use App\Http\Middleware\ResolveTenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(AccessManager::class, fn () => new AccessManager);
        $this->app->scoped(RequestDiagnostics::class, fn () => new RequestDiagnostics);
        $this->app->scoped(TenantContext::class, fn () => new TenantContext);
    }

    public function boot(): void
    {
        if ($this->app->isProduction() && config('marketplace.demo')) {
            throw new \LogicException('Development demo mode is forbidden in production.');
        }
        DB::listen(function ($query) {
            $guard = auth()->guard();
            if (method_exists($guard, 'hasUser') && $guard->hasUser() && $guard->user()->role === 'superadmin') {
                app(RequestDiagnostics::class)->query($query);
            }
        });
        Livewire::addPersistentMiddleware([ResolveTenant::class]);
        Gate::define('manage-marketplace', fn ($user) => $user->managesMarketplace());
    }
}
