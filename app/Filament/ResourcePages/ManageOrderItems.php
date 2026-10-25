<?php

namespace App\Filament\ResourcePages;

use App\Filament\Resources\OrderItemResource;
use Filament\Resources\Pages\ManageRecords;

class ManageOrderItems extends ManageRecords
{
    protected static string $resource = OrderItemResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
