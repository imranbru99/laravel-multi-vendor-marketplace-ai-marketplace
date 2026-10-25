<?php

namespace App\Filament\ResourcePages;

use App\Filament\Resources\EmployeeResource;
use App\Filament\Shared\OperationalResource;
use Filament\Resources\Pages\ManageRecords;

class ManageEmployee extends ManageRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return OperationalResource::creationActions(static::$resource);
    }
}
