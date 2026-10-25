<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageOffer;
use App\Filament\Shared\OperationalResource;
use App\Models\Offer;

class OfferResource extends OperationalResource
{
    protected static ?string $model = Offer::class;

    public static string $entity = 'Offer';

    public static function getPages(): array
    {
        return ['index' => ManageOffer::route('/')];
    }
}
