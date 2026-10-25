<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use \App\Models\Concerns\HasSlugHistory, TenantScoped;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
