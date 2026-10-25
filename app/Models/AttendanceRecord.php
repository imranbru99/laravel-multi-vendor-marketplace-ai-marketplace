<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use TenantScoped;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['clocked_in_at' => 'datetime', 'clocked_out_at' => 'datetime'];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
