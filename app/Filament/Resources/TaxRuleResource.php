<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageTaxRule;
use App\Filament\Shared\OperationalResource;
use App\Models\TaxRule;

class TaxRuleResource extends OperationalResource
{
    protected static ?string $model = TaxRule::class;

    public static string $entity = 'TaxRule';

    public static function getPages(): array
    {
        return ['index' => ManageTaxRule::route('/')];
    }
}
