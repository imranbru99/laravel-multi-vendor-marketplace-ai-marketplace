<?php

namespace App\Filament\ResourcePages;

use App\Filament\Resources\WarehouseResource;
use App\Filament\Shared\OperationalResource;
use Filament\Resources\Pages\ManageRecords;

class ManageWarehouse extends ManageRecords
{
    protected static string $resource = WarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return OperationalResource::creationActions(static::$resource);
    }
}
