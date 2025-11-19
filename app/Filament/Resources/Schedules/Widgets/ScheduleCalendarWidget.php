<?php

namespace App\Filament\Resources\Schedules\Widgets;

use App\Models\Schedule;
use Carbon\WeekDay;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\Filament\Actions\CreateAction;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Guava\Calendar\ValueObjects\FetchInfo;
use Guava\Calendar\Enums\CalendarViewType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ScheduleCalendarWidget extends CalendarWidget
{
    protected bool $eventClickEnabled = true; //Para abrir o menu de contexto
    protected CalendarViewType $calendarView = CalendarViewType::ListWeek; //Para tipo de lista

    public function getEvents(FetchInfo $info): Collection|Builder|array
    {
        return $this->getQuery()
            ->with(['advertiseAnswer.advertise', 'advertiseAnswer.contact'])
            ->whereBetween('date', [
                $info->start->startOfDay(),
                $info->end->endOfDay(),
            ]);
    }

    public function getHeading(): string
    {
        return 'Calendário de Agendamentos';
    }

    public function getSubheading(): ?string
    {
        return 'Gerencie seus agendamentos';
    }

    public function getQuery(): Builder
    {
        $query = Schedule::query();
        $user = auth()->user();

        if (!$user->can('view_all_schedules')) {
            $query->where(function ($q) use ($user) {
                $q->whereHas('advertiseAnswer.advertise', function ($subQuery) use ($user) {
                    $subQuery->where('user_id', $user->id);
                })
                    ->orWhereHas('advertiseAnswer.advertise.associatedUsers', function ($subQuery) use ($user) {
                        $subQuery->where('users.id', $user->id);
                    });
            });
        }

        return $query;
    }

    public function getEventClickContextMenuActions(): array
    {
        return [
            $this->viewAction(),
            $this->editAction(),
            $this->deleteAction(),
        ];
    }

    public function viewAction(): \Guava\Calendar\Filament\Actions\ViewAction
    {
        return parent::viewAction()
            ->url(fn($record) => \App\Filament\Resources\Schedules\ScheduleResource::getUrl('view', ['record' => $record]))
            ->authorize('view');
    }
}