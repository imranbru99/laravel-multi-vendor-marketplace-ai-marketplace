<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use TenantScoped;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
