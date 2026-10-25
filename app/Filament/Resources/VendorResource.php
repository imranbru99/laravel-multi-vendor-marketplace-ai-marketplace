<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageVendor;
use App\Filament\Shared\OperationalResource;
use App\Models\Vendor;

class VendorResource extends OperationalResource
{
    protected static ?string $model = Vendor::class;

    public static string $entity = 'Vendor';

    public static function getPages(): array
    {
        return ['index' => ManageVendor::route('/')];
    }
}
