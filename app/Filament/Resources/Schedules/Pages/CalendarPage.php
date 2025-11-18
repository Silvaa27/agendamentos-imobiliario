<?php

namespace App\Filament\Resources\Schedules\Pages;

use App\Filament\Resources\Schedules\ScheduleResource;
use App\Filament\Resources\Schedules\Widgets\ScheduleCalendarWidget;
use Filament\Resources\Pages\Page;

class CalendarPage extends Page
{
    protected static string $resource = ScheduleResource::class;

    // REMOVA COMPLETAMENTE a declaração de $view
    // NÃO declare $view aqui - o Filament usa automático

    protected function getHeaderWidgets(): array
    {
        return [
            ScheduleCalendarWidget::class,
        ];
    }
}