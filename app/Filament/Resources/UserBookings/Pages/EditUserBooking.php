<?php

namespace App\Filament\Resources\UserBookings\Pages;

use App\Filament\Resources\UserBookings\UserBookingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUserBooking extends EditRecord
{
    protected static string $resource = UserBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
