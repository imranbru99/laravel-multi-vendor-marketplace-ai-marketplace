<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageContentPage;
use App\Filament\Shared\OperationalResource;
use App\Models\ContentPage;

class ContentPageResource extends OperationalResource
{
    protected static ?string $model = ContentPage::class;

    public static string $entity = 'ContentPage';

    public static function getPages(): array
    {
        return ['index' => ManageContentPage::route('/')];
    }
}
