<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class TenantModule extends Model
{
    use TenantScoped;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean', 'configuration' => 'array'];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
