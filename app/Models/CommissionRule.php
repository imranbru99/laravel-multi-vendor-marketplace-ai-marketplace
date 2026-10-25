<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class CommissionRule extends Model
{
    use TenantScoped;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['effective_from' => 'datetime', 'effective_until' => 'datetime', 'enabled' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::updating(function ($rule) {
            if (array_diff(array_keys($rule->getDirty()), ['enabled', 'updated_at'])) {
                throw new \LogicException('Create a new effective-dated rule version instead.');
            }
        });
    }
}
