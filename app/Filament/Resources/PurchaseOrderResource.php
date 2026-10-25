<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManagePurchaseOrder;
use App\Filament\Shared\OperationalResource;
use App\Models\PurchaseOrder;

class PurchaseOrderResource extends OperationalResource
{
    protected static ?string $model = PurchaseOrder::class;

    public static string $entity = 'PurchaseOrder';

    public static function getPages(): array
    {
        return ['index' => ManagePurchaseOrder::route('/')];
    }
}
