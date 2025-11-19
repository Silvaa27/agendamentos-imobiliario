<?php

namespace App\Filament\Resources\Schedules\Pages;

use App\Filament\Resources\Schedules\ScheduleResource;
use App\Filament\Resources\Schedules\Widgets\ScheduleCalendarWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSchedules extends ListRecords
{
    protected static string $resource = ScheduleResource::class;

    public function mount(): void
    {
        redirect()->route('filament.admin.resources.schedules.calendar');
    }
}