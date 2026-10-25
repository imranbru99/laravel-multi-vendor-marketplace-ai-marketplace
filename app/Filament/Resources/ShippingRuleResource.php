<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageShippingRule;
use App\Filament\Shared\OperationalResource;
use App\Models\ShippingRule;

class ShippingRuleResource extends OperationalResource
{
    protected static ?string $model = ShippingRule::class;

    public static string $entity = 'ShippingRule';

    public static function getPages(): array
    {
        return ['index' => ManageShippingRule::route('/')];
    }
}
