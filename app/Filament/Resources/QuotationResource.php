<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageQuotation;
use App\Filament\Shared\OperationalResource;
use App\Models\Quotation;

class QuotationResource extends OperationalResource
{
    protected static ?string $model = Quotation::class;

    public static string $entity = 'Quotation';

    public static function getPages(): array
    {
        return ['index' => ManageQuotation::route('/')];
    }
}
