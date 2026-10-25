<?php

namespace App\Filament\ResourcePages;

use App\Filament\Resources\AiDraftResource;
use App\Filament\Shared\OperationalResource;
use Filament\Resources\Pages\ManageRecords;

class ManageAiDraft extends ManageRecords
{
    protected static string $resource = AiDraftResource::class;

    protected function getHeaderActions(): array
    {
        return OperationalResource::creationActions(static::$resource);
    }
}
