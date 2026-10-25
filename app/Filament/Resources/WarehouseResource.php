<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageWarehouse;
use App\Filament\Shared\OperationalResource;
use App\Models\Warehouse;

class WarehouseResource extends OperationalResource
{
    protected static ?string $model = Warehouse::class;

    public static string $entity = 'Warehouse';

    public static function getPages(): array
    {
        return ['index' => ManageWarehouse::route('/')];
    }
}
