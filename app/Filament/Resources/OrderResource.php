<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageOrder;
use App\Filament\Shared\OperationalResource;
use App\Models\Order;

class OrderResource extends OperationalResource
{
    protected static ?string $model = Order::class;

    public static string $entity = 'Order';

    public static function getPages(): array
    {
        return ['index' => ManageOrder::route('/')];
    }
}
