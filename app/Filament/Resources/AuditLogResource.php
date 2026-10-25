<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageAuditLog;
use App\Filament\Shared\OperationalResource;
use App\Models\AuditLog;

class AuditLogResource extends OperationalResource
{
    protected static ?string $model = AuditLog::class;

    public static string $entity = 'AuditLog';

    public static function getPages(): array
    {
        return ['index' => ManageAuditLog::route('/')];
    }
}
