<?php

namespace App\Filament\ResourcePages;

use App\Filament\Resources\AttendanceRecordResource;
use App\Filament\Shared\OperationalResource;
use Filament\Resources\Pages\ManageRecords;

class ManageAttendanceRecord extends ManageRecords
{
    protected static string $resource = AttendanceRecordResource::class;

    protected function getHeaderActions(): array
    {
        return OperationalResource::creationActions(static::$resource);
    }
}
