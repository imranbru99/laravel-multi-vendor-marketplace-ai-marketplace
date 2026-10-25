<?php

namespace App\Filament\ResourcePages;

use App\Filament\Resources\VendorOrderResource;
use App\Filament\Shared\OperationalResource;
use Filament\Resources\Pages\ManageRecords;

class ManageVendorOrder extends ManageRecords
{
    protected static string $resource = VendorOrderResource::class;

    protected function getHeaderActions(): array
    {
        return OperationalResource::creationActions(static::$resource);
    }
}
