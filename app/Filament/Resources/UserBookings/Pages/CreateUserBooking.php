<?php

namespace App\Filament\Resources\UserBookings\Pages;

use App\Filament\Resources\UserBookings\UserBookingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserBooking extends CreateRecord
{
    protected static string $resource = UserBookingResource::class;
}
