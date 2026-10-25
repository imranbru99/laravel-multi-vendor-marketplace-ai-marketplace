<?php

namespace App\Filament\ResourcePages;

use App\Filament\Resources\RefundResource;
use App\Filament\Shared\OperationalResource;
use Filament\Resources\Pages\ManageRecords;

class ManageRefund extends ManageRecords
{
    protected static string $resource = RefundResource::class;

    protected function getHeaderActions(): array
    {
        return OperationalResource::creationActions(static::$resource);
    }
}
