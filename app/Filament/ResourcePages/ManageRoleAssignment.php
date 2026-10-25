<?php

namespace App\Filament\ResourcePages;

use App\Filament\Resources\RoleAssignmentResource;
use App\Filament\Shared\OperationalResource;
use Filament\Resources\Pages\ManageRecords;

class ManageRoleAssignment extends ManageRecords
{
    protected static string $resource = RoleAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return OperationalResource::creationActions(static::$resource);
    }
}
