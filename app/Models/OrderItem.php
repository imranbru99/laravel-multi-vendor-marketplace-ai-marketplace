<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use TenantScoped;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['variant_snapshot' => 'array', 'commission_snapshot' => 'array', 'tax_snapshot' => 'array'];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function vendorOrder()
    {
        return $this->belongsTo(VendorOrder::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function inventoryBalance()
    {
        return $this->belongsTo(InventoryBalance::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
