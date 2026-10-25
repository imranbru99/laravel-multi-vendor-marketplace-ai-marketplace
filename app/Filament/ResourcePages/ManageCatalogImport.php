<?php

namespace App\Filament\ResourcePages;

use App\Filament\Resources\CatalogImportResource;
use App\Filament\Shared\OperationalResource;
use Filament\Resources\Pages\ManageRecords;

class ManageCatalogImport extends ManageRecords
{
    protected static string $resource = CatalogImportResource::class;

    protected function getHeaderActions(): array
    {
        return OperationalResource::creationActions(static::$resource);
    }
}
