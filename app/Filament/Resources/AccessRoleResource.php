<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageAccessRole;
use App\Filament\Shared\OperationalResource;
use App\Models\AccessRole;
use Illuminate\Database\Eloquent\Model;

class AccessRoleResource extends OperationalResource
{
    protected static ?string $model = AccessRole::class;

    public static string $entity = 'AccessRole';

    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->role, ['superadmin', 'owner', 'admin'], true);
    }

    public static function canEdit(Model $record): bool
    {
        return parent::canEdit($record);
    }

    public static function getPages(): array
    {
        return ['index' => ManageAccessRole::route('/')];
    }
}
