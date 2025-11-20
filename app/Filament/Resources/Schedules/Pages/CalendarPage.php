<?php

namespace App\Filament\Resources\Schedules\Pages;

use App\Filament\Resources\Schedules\ScheduleResource;
use App\Filament\Resources\Schedules\Widgets\ScheduleCalendarWidget;
use Filament\Resources\Pages\Page;

class CalendarPage extends Page
{
    protected static string $resource = ScheduleResource::class;

    public function getTitle(): string
    {
        return __('Agenda');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ScheduleCalendarWidget::class,
        ];
    }
}