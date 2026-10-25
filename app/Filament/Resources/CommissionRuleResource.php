<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageCommissionRule;
use App\Filament\Shared\OperationalResource;
use App\Models\CommissionRule;

class CommissionRuleResource extends OperationalResource
{
    protected static ?string $model = CommissionRule::class;

    public static string $entity = 'CommissionRule';

    public static function getPages(): array
    {
        return ['index' => ManageCommissionRule::route('/')];
    }
}
