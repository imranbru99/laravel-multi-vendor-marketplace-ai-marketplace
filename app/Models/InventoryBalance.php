<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class InventoryBalance extends Model
{
    use TenantScoped;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
