<?php

namespace App\Filament\ResourcePages;

use App\Filament\Resources\ShippingRuleResource;
use App\Filament\Shared\OperationalResource;
use Filament\Resources\Pages\ManageRecords;

class ManageShippingRule extends ManageRecords
{
    protected static string $resource = ShippingRuleResource::class;

    protected function getHeaderActions(): array
    {
        return OperationalResource::creationActions(static::$resource);
    }
}
