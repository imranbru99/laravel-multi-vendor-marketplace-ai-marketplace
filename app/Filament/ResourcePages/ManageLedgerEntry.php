<?php

namespace App\Filament\ResourcePages;

use App\Filament\Resources\LedgerEntryResource;
use App\Filament\Shared\OperationalResource;
use Filament\Resources\Pages\ManageRecords;

class ManageLedgerEntry extends ManageRecords
{
    protected static string $resource = LedgerEntryResource::class;

    protected function getHeaderActions(): array
    {
        return OperationalResource::creationActions(static::$resource);
    }
}
