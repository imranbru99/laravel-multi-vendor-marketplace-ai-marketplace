<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageLedgerEntry;
use App\Filament\Shared\OperationalResource;
use App\Models\LedgerEntry;

class LedgerEntryResource extends OperationalResource
{
    protected static ?string $model = LedgerEntry::class;

    public static string $entity = 'LedgerEntry';

    public static function getPages(): array
    {
        return ['index' => ManageLedgerEntry::route('/')];
    }
}
