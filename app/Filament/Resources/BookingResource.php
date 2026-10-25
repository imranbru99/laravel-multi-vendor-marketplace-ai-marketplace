<?php

namespace App\Filament\Resources;

use App\Filament\ResourcePages\ManageBooking;
use App\Filament\Shared\OperationalResource;
use App\Models\Booking;

class BookingResource extends OperationalResource
{
    protected static ?string $model = Booking::class;

    public static string $entity = 'Booking';

    public static function getPages(): array
    {
        return ['index' => ManageBooking::route('/')];
    }
}
