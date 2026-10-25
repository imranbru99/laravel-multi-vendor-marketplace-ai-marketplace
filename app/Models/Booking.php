<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use TenantScoped;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime'];
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
