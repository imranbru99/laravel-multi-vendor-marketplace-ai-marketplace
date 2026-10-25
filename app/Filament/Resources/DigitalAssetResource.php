<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageDigitalAssets;
use App\Filament\Shared\OperationalResource;
use App\Models\DigitalAsset;

class DigitalAssetResource extends OperationalResource
{
    protected static ?string $model = DigitalAsset::class;

    public static string $entity = 'DigitalAsset';

    public static function getPages(): array
    {
        return ['index' => ManageDigitalAssets::route('/')];
    }
}
