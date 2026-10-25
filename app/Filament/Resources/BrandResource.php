<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageBrand;
use App\Filament\Shared\OperationalResource;
use App\Models\Brand;

class BrandResource extends OperationalResource
{
    protected static ?string $model = Brand::class;

    public static string $entity = 'Brand';

    public static function getPages(): array
    {
        return ['index' => ManageBrand::route('/')];
    }
}
