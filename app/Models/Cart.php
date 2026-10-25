<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use TenantScoped;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
