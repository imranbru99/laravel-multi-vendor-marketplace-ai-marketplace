<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class PaymentEvent extends Model
{
    use TenantScoped;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Immutable event'));
        static::deleting(fn () => throw new \LogicException('Immutable event'));
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
