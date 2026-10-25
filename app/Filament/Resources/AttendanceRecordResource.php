<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageAttendanceRecord;
use App\Filament\Shared\OperationalResource;
use App\Models\AttendanceRecord;

class AttendanceRecordResource extends OperationalResource
{
    protected static ?string $model = AttendanceRecord::class;

    public static string $entity = 'AttendanceRecord';

    public static function getPages(): array
    {
        return ['index' => ManageAttendanceRecord::route('/')];
    }
}
