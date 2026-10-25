<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageAiDraft;
use App\Filament\Shared\OperationalResource;
use App\Models\AiDraft;

class AiDraftResource extends OperationalResource
{
    protected static ?string $model = AiDraft::class;

    public static string $entity = 'AiDraft';

    public static function getPages(): array
    {
        return ['index' => ManageAiDraft::route('/')];
    }
}
