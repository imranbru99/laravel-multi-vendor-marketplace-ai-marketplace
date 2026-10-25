<?php

namespace App\Models\Concerns;

use App\Domain\ScopeGuard;
use App\Domain\TenantContext;
use Illuminate\Database\Eloquent\Builder;

trait TenantScoped
{
    public static function bootTenantScoped(): void
    {
        static::addGlobalScope('tenant', function (Builder $query) {
            $query->where($query->getModel()->qualifyColumn('tenant_id'), app(TenantContext::class)->id());
        });
        static::saving(fn ($model) => app(ScopeGuard::class)->saving($model));
        static::creating(function ($model) {
            $id = app(TenantContext::class)->id();
            if (isset($model->tenant_id) && (int) $model->tenant_id !== $id) {
                throw new \LogicException('Cross-tenant write denied.');
            }
            $model->tenant_id = $id;
        });
        static::updating(function ($model) {
            if ($model->isDirty('tenant_id')) {
                throw new \LogicException('Tenant ownership is immutable.');
            }
        });
    }
}
