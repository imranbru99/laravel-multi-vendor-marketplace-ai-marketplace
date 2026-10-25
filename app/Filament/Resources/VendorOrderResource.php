<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageVendorOrder;
use App\Filament\Shared\OperationalResource;
use App\Models\VendorOrder;

class VendorOrderResource extends OperationalResource
{
    protected static ?string $model = VendorOrder::class;

    public static string $entity = 'VendorOrder';

    public static function getPages(): array
    {
        return ['index' => ManageVendorOrder::route('/')];
    }
}
