<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageProduct;
use App\Filament\Shared\OperationalResource;
use App\Models\Product;

class ProductResource extends OperationalResource
{
    protected static ?string $model = Product::class;

    public static string $entity = 'Product';

    public static function getPages(): array
    {
        return ['index' => ManageProduct::route('/')];
    }
}
