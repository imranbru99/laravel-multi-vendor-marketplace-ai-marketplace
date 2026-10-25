<?php

namespace App\Filament\ResourcePages;

use App\Filament\Resources\InventoryBalanceResource;
use App\Filament\Shared\OperationalResource;
use Filament\Resources\Pages\ManageRecords;

class ManageInventoryBalance extends ManageRecords
{
    protected static string $resource = InventoryBalanceResource::class;

    protected function getHeaderActions(): array
    {
        return OperationalResource::creationActions(static::$resource);
    }
}
