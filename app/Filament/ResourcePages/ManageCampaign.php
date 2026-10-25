<?php

namespace App\Filament\ResourcePages;

use App\Filament\Resources\CampaignResource;
use App\Filament\Shared\OperationalResource;
use Filament\Resources\Pages\ManageRecords;

class ManageCampaign extends ManageRecords
{
    protected static string $resource = CampaignResource::class;

    protected function getHeaderActions(): array
    {
        return OperationalResource::creationActions(static::$resource);
    }
}
