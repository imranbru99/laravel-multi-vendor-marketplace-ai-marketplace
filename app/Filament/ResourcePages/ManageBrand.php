<?php

namespace App\Filament\ResourcePages;

use App\Filament\Resources\BrandResource;
use App\Filament\Shared\OperationalResource;
use Filament\Resources\Pages\ManageRecords;

class ManageBrand extends ManageRecords
{
    protected static string $resource = BrandResource::class;

    protected function getHeaderActions(): array
    {
        return OperationalResource::creationActions(static::$resource);
    }
}
