<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class ProductProposal extends Model
{
    use TenantScoped;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['rights_confirmed' => 'boolean'];
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
