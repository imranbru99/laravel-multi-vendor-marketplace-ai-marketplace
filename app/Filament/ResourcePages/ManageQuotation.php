<?php

namespace App\Filament\ResourcePages;

use App\Filament\Resources\QuotationResource;
use App\Filament\Shared\OperationalResource;
use Filament\Resources\Pages\ManageRecords;

class ManageQuotation extends ManageRecords
{
    protected static string $resource = QuotationResource::class;

    protected function getHeaderActions(): array
    {
        return OperationalResource::creationActions(static::$resource);
    }
}
