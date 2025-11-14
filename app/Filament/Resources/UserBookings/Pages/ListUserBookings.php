<?php

namespace App\Filament\Resources\UserBookings\Pages;

use App\Filament\Resources\UserBookings\UserBookingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserBookings extends ListRecords
{
    protected static string $resource = UserBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
