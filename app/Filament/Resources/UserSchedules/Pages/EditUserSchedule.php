<?php

namespace App\Filament\Resources\UserSchedules\Pages;

use App\Filament\Resources\UserSchedules\UserScheduleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUserSchedule extends EditRecord
{
    protected static string $resource = UserScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
