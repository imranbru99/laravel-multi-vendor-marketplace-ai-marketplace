<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use TenantScoped;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'active' => 'boolean'];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
