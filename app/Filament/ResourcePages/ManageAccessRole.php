<?php

namespace App\Filament\ResourcePages;

use App\Filament\Resources\AccessRoleResource;
use App\Filament\Shared\OperationalResource;
use Filament\Resources\Pages\ManageRecords;

class ManageAccessRole extends ManageRecords
{
    protected static string $resource = AccessRoleResource::class;

    protected function getHeaderActions(): array
    {
        return OperationalResource::creationActions(static::$resource);
    }
}
