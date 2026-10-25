<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use TenantScoped;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['delivered_at' => 'datetime'];
    }

    public function items()
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function events()
    {
        return $this->hasMany(ShipmentEvent::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
