<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class ShipmentItem extends Model
{
    use TenantScoped;

    protected $guarded = ['id'];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Shipment item history is immutable.'));
        static::deleting(fn () => throw new \LogicException('Shipment item history is immutable.'));
    }
}
