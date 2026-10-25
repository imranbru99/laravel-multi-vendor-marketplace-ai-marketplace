<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageSettlement;
use App\Filament\Shared\OperationalResource;
use App\Models\Settlement;

class SettlementResource extends OperationalResource
{
    protected static ?string $model = Settlement::class;

    public static string $entity = 'Settlement';

    public static function getPages(): array
    {
        return ['index' => ManageSettlement::route('/')];
    }
}
