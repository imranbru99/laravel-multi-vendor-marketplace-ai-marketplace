<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageRefund;
use App\Filament\Shared\OperationalResource;
use App\Models\Refund;

class RefundResource extends OperationalResource
{
    protected static ?string $model = Refund::class;

    public static string $entity = 'Refund';

    public static function getPages(): array
    {
        return ['index' => ManageRefund::route('/')];
    }
}
