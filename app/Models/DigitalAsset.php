<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class DigitalAsset extends Model
{
    use TenantScoped;

    protected $guarded = ['id'];

    protected $hidden = ['path'];

    protected function casts(): array
    {
        return ['rights_confirmed' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Publish a new digital asset version instead.'));
        static::deleting(fn () => throw new \LogicException('Purchased asset history must be retained.'));
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
