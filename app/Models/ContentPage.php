<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class ContentPage extends Model
{
    use \App\Models\Concerns\HasSlugHistory, TenantScoped;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
