<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformModule extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }
}
