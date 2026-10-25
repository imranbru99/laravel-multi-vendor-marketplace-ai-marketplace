<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageLedgerTransaction;
use App\Filament\Shared\OperationalResource;
use App\Models\LedgerTransaction;

class LedgerTransactionResource extends OperationalResource
{
    protected static ?string $model = LedgerTransaction::class;

    public static string $entity = 'LedgerTransaction';

    public static function getPages(): array
    {
        return ['index' => ManageLedgerTransaction::route('/')];
    }
}
