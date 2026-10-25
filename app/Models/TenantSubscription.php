<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class TenantSubscription extends Model
{
    use TenantScoped;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['limits' => 'array', 'modules' => 'array', 'period_starts_at' => 'datetime', 'period_ends_at' => 'datetime', 'cancelled_at' => 'datetime', 'auto_renew' => 'boolean'];
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }
}
