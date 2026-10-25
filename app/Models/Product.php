<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use \App\Models\Concerns\HasSlugHistory, TenantScoped;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['gallery' => 'array', 'specifications' => 'array'];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function displayImage(): string
    {
        return $this->image ?: (! empty($this->gallery[0]) ? Storage::disk('public')->url($this->gallery[0]) : '/images/placeholder.svg');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
