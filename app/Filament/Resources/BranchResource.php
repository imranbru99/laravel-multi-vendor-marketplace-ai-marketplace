<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageBranch;
use App\Filament\Shared\OperationalResource;
use App\Models\Branch;

class BranchResource extends OperationalResource
{
    protected static ?string $model = Branch::class;

    public static string $entity = 'Branch';

    public static function getPages(): array
    {
        return ['index' => ManageBranch::route('/')];
    }
}
