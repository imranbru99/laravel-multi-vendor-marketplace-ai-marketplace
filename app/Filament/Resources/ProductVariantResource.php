<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageProductVariant;
use App\Filament\Shared\OperationalResource;
use App\Models\ProductVariant;

class ProductVariantResource extends OperationalResource
{
    protected static ?string $model = ProductVariant::class;

    public static string $entity = 'ProductVariant';

    public static function getPages(): array
    {
        return ['index' => ManageProductVariant::route('/')];
    }
}
