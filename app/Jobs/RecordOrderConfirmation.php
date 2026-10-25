<?php

namespace App\Jobs;

use App\Domain\TenantContext;
use App\Models\CustomerNotification;
use App\Models\Order;
use App\Models\Tenant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecordOrderConfirmation implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $tenantId, public int $orderId) {}

    public function handle(): void
    {
        $context = app(TenantContext::class);
        $previous = null;
        try {
            $previous = $context->get();
        } catch (\LogicException) {
        }
        $context->set(Tenant::findOrFail($this->tenantId));
        try {
            $order = Order::findOrFail($this->orderId);
            if ($order->user_id) {
                CustomerNotification::firstOrCreate(['reference' => 'confirmed:'.$order->id], ['user_id' => $order->user_id, 'message' => 'Sandbox payment confirmed for order '.$order->number.'. No real money was charged.']);
            }
        } finally {
            $previous ? $context->set($previous) : $context->clear();
        }
    }
}
