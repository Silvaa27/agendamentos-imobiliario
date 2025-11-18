<?php

namespace App\Filament\Resources\Schedules\Pages;

use App\Filament\Resources\Schedules\ScheduleResource;
use Filament\Resources\Pages\Page;

class ScheduleCalendar extends Page
{
    protected static string $resource = ScheduleResource::class;

    protected string $view = 'filament.resources.schedules.pages.schedule-calendar';
}
