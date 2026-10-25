<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use TenantScoped;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['attributes' => 'array'];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
