<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageContactMessage;
use App\Filament\Shared\OperationalResource;
use App\Models\ContactMessage;

class ContactMessageResource extends OperationalResource
{
    protected static ?string $model = ContactMessage::class;

    public static string $entity = 'ContactMessage';

    public static function getPages(): array
    {
        return ['index' => ManageContactMessage::route('/')];
    }
}
