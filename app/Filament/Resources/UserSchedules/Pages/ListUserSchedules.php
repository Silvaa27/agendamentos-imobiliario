<?php

namespace App\Filament\Resources\UserSchedules\Pages;

use App\Filament\Resources\UserSchedules\UserScheduleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserSchedules extends ListRecords
{
    protected static string $resource = UserScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
