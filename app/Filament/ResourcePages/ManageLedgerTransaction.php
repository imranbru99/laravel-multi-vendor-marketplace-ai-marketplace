<?php

namespace App\Filament\ResourcePages;

use App\Filament\Resources\LedgerTransactionResource;
use App\Filament\Shared\OperationalResource;
use Filament\Resources\Pages\ManageRecords;

class ManageLedgerTransaction extends ManageRecords
{
    protected static string $resource = LedgerTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return OperationalResource::creationActions(static::$resource);
    }
}
