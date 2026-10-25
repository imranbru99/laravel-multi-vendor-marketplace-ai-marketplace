<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class CatalogImport extends Model
{
    use TenantScoped;

    protected $guarded = ['id'];

    protected $hidden = ['path', 'rows'];

    protected function casts(): array
    {
        return ['rights_confirmed' => 'boolean', 'rows' => 'array', 'errors' => 'array'];
    }
}
