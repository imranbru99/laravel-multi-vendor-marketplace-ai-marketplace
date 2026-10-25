<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManagePayment;
use App\Filament\Shared\OperationalResource;
use App\Models\Payment;

class PaymentResource extends OperationalResource
{
    protected static ?string $model = Payment::class;

    public static string $entity = 'Payment';

    public static function getPages(): array
    {
        return ['index' => ManagePayment::route('/')];
    }
}
