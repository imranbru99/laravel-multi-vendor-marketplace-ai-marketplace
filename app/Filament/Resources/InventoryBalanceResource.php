<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageInventoryBalance;
use App\Filament\Shared\OperationalResource;
use App\Models\InventoryBalance;

class InventoryBalanceResource extends OperationalResource
{
    protected static ?string $model = InventoryBalance::class;

    public static string $entity = 'InventoryBalance';

    public static function getPages(): array
    {
        return ['index' => ManageInventoryBalance::route('/')];
    }
}
