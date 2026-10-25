<?php

namespace App\Filament\ResourcePages;

use App\Filament\Resources\OfferResource;
use App\Filament\Shared\OperationalResource;
use Filament\Resources\Pages\ManageRecords;

class ManageOffer extends ManageRecords
{
    protected static string $resource = OfferResource::class;

    protected function getHeaderActions(): array
    {
        return OperationalResource::creationActions(static::$resource);
    }
}
