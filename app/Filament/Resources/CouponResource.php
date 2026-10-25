<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageCoupon;
use App\Filament\Shared\OperationalResource;
use App\Models\Coupon;

class CouponResource extends OperationalResource
{
    protected static ?string $model = Coupon::class;

    public static string $entity = 'Coupon';

    public static function getPages(): array
    {
        return ['index' => ManageCoupon::route('/')];
    }
}
