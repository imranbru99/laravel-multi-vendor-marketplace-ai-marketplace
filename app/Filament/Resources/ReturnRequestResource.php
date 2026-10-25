<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageReturnRequest;
use App\Filament\Shared\OperationalResource;
use App\Models\ReturnRequest;

class ReturnRequestResource extends OperationalResource
{
    protected static ?string $model = ReturnRequest::class;

    public static string $entity = 'ReturnRequest';

    public static function getPages(): array
    {
        return ['index' => ManageReturnRequest::route('/')];
    }
}
