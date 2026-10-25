<?php

namespace App\Filament\ResourcePages;

use App\Filament\Resources\VendorResource;
use App\Filament\Shared\OperationalResource;
use Filament\Resources\Pages\ManageRecords;

class ManageVendor extends ManageRecords
{
    protected static string $resource = VendorResource::class;

    protected function getHeaderActions(): array
    {
        return OperationalResource::creationActions(static::$resource);
    }
}
