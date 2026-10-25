<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class DigitalEntitlement extends Model
{
    use TenantScoped;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'revoked_at' => 'datetime'];
    }

    public function asset()
    {
        return $this->belongsTo(DigitalAsset::class, 'digital_asset_id');
    }
}
