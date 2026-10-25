<?php

namespace App\Models;

use App\Domain\AccessManager;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'tenant_id', 'vendor_id', 'branch_id', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->role === 'staff') {
            return app(AccessManager::class)->panel($this, $panel->getId());
        }

        return match ($panel->getId()) {
            'admin' => $this->role === 'superadmin',
            'tenant' => in_array($this->role, ['superadmin', 'owner', 'admin', 'finance', 'support']),
            'vendor' => in_array($this->role, ['vendor', 'manager']) && $this->vendor_id !== null,
            'branch' => in_array($this->role, ['branch', 'warehouse']) && $this->branch_id !== null,
            default => false,
        };
    }

    public function managesMarketplace(): bool
    {
        return app(AccessManager::class)->allows($this, 'marketplace.manage');
    }
}
