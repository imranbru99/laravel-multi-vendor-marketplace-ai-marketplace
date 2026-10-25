<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class SlugRedirect extends Model
{
    use TenantScoped;

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Slug history is immutable.'));
        static::deleting(fn () => throw new \LogicException('Slug history is immutable.'));
    }
}
