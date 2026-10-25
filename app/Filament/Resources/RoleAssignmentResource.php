<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageRoleAssignment;
use App\Filament\Shared\OperationalResource;
use App\Models\RoleAssignment;
use Illuminate\Database\Eloquent\Model;

class RoleAssignmentResource extends OperationalResource
{
    protected static ?string $model = RoleAssignment::class;

    public static string $entity = 'RoleAssignment';

    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->role, ['superadmin', 'owner', 'admin'], true);
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => ManageRoleAssignment::route('/')];
    }
}
