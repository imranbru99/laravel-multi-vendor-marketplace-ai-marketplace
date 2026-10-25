<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageOrderItems;
use App\Filament\Shared\OperationalResource;
use App\Models\OrderItem;

class OrderItemResource extends OperationalResource
{
    protected static ?string $model = OrderItem::class;

    public static string $entity = 'OrderItem';

    public static function getNavigationLabel(): string
    {
        return 'Allocated order items';
    }

    public static function getPages(): array
    {
        return ['index' => ManageOrderItems::route('/')];
    }
}
