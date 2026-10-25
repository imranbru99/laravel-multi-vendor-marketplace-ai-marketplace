<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageEmployee;
use App\Filament\Shared\OperationalResource;
use App\Models\Employee;

class EmployeeResource extends OperationalResource
{
    protected static ?string $model = Employee::class;

    public static string $entity = 'Employee';

    public static function getPages(): array
    {
        return ['index' => ManageEmployee::route('/')];
    }
}
