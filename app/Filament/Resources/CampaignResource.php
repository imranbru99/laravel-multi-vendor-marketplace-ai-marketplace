<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageCampaign;
use App\Filament\Shared\OperationalResource;
use App\Models\Campaign;

class CampaignResource extends OperationalResource
{
    protected static ?string $model = Campaign::class;

    public static string $entity = 'Campaign';

    public static function getPages(): array
    {
        return ['index' => ManageCampaign::route('/')];
    }
}
