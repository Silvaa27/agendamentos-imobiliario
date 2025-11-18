<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use App\Filament\Resources\ScheduleResource\Widgets\ScheduleCalendarWidget;
use App\Filament\Resources\Schedules\ScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\Page;

class CalendarPage extends Page
{
    protected static string $resource = ScheduleResource::class;


    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nova Marcação')
                ->url(ScheduleResource::getUrl('create')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ScheduleCalendarWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }
}