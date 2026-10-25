<?php

namespace App\Domain;

use App\Models\Payment;

interface PaymentGateway
{
    public function confirm(Payment $payment, string $eventId, int $amount, string $currency): void;
}
