<?php

namespace App\Filament\ResourcePages;

use App\Filament\Resources\TaxRuleResource;
use App\Filament\Shared\OperationalResource;
use Filament\Resources\Pages\ManageRecords;

class ManageTaxRule extends ManageRecords
{
    protected static string $resource = TaxRuleResource::class;

    protected function getHeaderActions(): array
    {
        return OperationalResource::creationActions(static::$resource);
    }
}
