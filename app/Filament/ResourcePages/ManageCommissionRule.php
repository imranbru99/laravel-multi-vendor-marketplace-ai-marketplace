<?php

namespace App\Filament\ResourcePages;

use App\Filament\Resources\CommissionRuleResource;
use App\Filament\Shared\OperationalResource;
use Filament\Resources\Pages\ManageRecords;

class ManageCommissionRule extends ManageRecords
{
    protected static string $resource = CommissionRuleResource::class;

    protected function getHeaderActions(): array
    {
        return OperationalResource::creationActions(static::$resource);
    }
}
