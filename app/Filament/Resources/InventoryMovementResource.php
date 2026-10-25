<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageInventoryMovement;
use App\Filament\Shared\OperationalResource;
use App\Models\InventoryMovement;

class InventoryMovementResource extends OperationalResource
{
    protected static ?string $model = InventoryMovement::class;

    public static string $entity = 'InventoryMovement';

    public static function getPages(): array
    {
        return ['index' => ManageInventoryMovement::route('/')];
    }
}
