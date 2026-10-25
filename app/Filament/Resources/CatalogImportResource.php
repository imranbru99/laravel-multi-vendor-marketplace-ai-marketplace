<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageCatalogImport;
use App\Filament\Shared\OperationalResource;
use App\Models\CatalogImport;
use Illuminate\Database\Eloquent\Model;

class CatalogImportResource extends OperationalResource
{
    protected static ?string $model = CatalogImport::class;

    public static string $entity = 'CatalogImport';

    public static function canEdit(Model $record): bool
    {
        return parent::canEdit($record) && in_array($record->status, ['uploaded', 'needs_correction']);
    }

    public static function getPages(): array
    {
        return ['index' => ManageCatalogImport::route('/')];
    }
}
