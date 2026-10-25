<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageSupportTicket;
use App\Filament\Shared\OperationalResource;
use App\Models\SupportTicket;

class SupportTicketResource extends OperationalResource
{
    protected static ?string $model = SupportTicket::class;

    public static string $entity = 'SupportTicket';

    public static function getPages(): array
    {
        return ['index' => ManageSupportTicket::route('/')];
    }
}
