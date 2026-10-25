<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use TenantScoped;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
