<?php

namespace App\Filament\ResourcePages;

use App\Filament\Resources\SubscriptionPlanResource;
use App\Filament\Shared\OperationalResource;
use Filament\Resources\Pages\ManageRecords;

class ManageSubscriptionPlans extends ManageRecords
{
    protected static string $resource = SubscriptionPlanResource::class;

    protected function getHeaderActions(): array
    {
        return OperationalResource::creationActions(static::$resource);
    }
}
