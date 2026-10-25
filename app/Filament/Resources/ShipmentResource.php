<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageShipment;
use App\Filament\Shared\OperationalResource;
use App\Models\Shipment;

class ShipmentResource extends OperationalResource
{
    protected static ?string $model = Shipment::class;

    public static string $entity = 'Shipment';

    public static function getPages(): array
    {
        return ['index' => ManageShipment::route('/')];
    }
}
