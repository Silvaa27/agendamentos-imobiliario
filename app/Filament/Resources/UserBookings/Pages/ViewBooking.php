<?php

namespace App\Filament\Resources\UserBookings\Pages;

use App\Filament\Resources\UserBookings\UserBookingResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBooking extends ViewRecord
{
    protected static string $resource = UserBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
