<?php

namespace App\Filament\ResourcePages;

use App\Filament\Resources\DigitalAssetResource;
use App\Filament\Shared\OperationalResource;
use Filament\Resources\Pages\ManageRecords;

class ManageDigitalAssets extends ManageRecords
{
    protected static string $resource = DigitalAssetResource::class;

    protected function getHeaderActions(): array
    {
        return OperationalResource::creationActions(static::$resource);
    }
}
