<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class RoleAssignment extends Model
{
    use TenantScoped;

    protected $guarded = ['id'];

    public function role()
    {
        return $this->belongsTo(AccessRole::class, 'access_role_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
