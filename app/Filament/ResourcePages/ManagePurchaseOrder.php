<?php

namespace App\Filament\ResourcePages;

use App\Filament\Resources\PurchaseOrderResource;
use App\Filament\Shared\OperationalResource;
use Filament\Resources\Pages\ManageRecords;

class ManagePurchaseOrder extends ManageRecords
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return OperationalResource::creationActions(static::$resource);
    }
}
