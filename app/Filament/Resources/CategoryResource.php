<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageCategory;
use App\Filament\Shared\OperationalResource;
use App\Models\Category;

class CategoryResource extends OperationalResource
{
    protected static ?string $model = Category::class;

    public static string $entity = 'Category';

    public static function getPages(): array
    {
        return ['index' => ManageCategory::route('/')];
    }
}
