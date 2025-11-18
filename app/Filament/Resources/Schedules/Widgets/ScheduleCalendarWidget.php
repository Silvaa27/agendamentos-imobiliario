<?php

namespace App\Filament\Resources\Schedules\Widgets;

use App\Models\Schedule;
use Carbon\WeekDay;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\Filament\Actions\CreateAction;
use Guava\Calendar\ValueObjects\FetchInfo;
use Guava\Calendar\Enums\CalendarViewType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ScheduleCalendarWidget extends CalendarWidget
{
    protected bool $dateClickEnabled = true;
    protected bool $eventClickEnabled = true;
    protected bool $eventDragEnabled = true;

    protected ?string $defaultEventClickAction = 'edit';

    protected CalendarViewType $calendarView = CalendarViewType::ListWeek; // Diário

    /**
     * MÉTODO OTIMIZADO
     */

    public function mount() {
        $this->setOption('slotMinTime', '08:00:00');
        $this->setOption('slotMaxTime', '10:00:00');
    }
    public function getEvents(FetchInfo $info): Collection|Builder|array
    {
        \Log::info('🎯 CALENDARIO - Buscando eventos', [
            'user_id' => auth()->id(),
            'periodo' => $info->start->toDateString() . ' a ' . $info->end->toDateString()
        ]);

        return $this->getQuery()
            ->with(['advertiseAnswer.advertise', 'advertiseAnswer.contact'])
            ->whereBetween('date', [
                $info->start->startOfDay(),
                $info->end->endOfDay(),
            ]);
    }

    public function getModel(): string
    {
        return Schedule::class;
    }

    public function getQuery(): Builder
    {
        $query = Schedule::query();

        if (
            auth()->user()->hasRole('super_admin') ||
            auth()->user()->can('view_shared_advertises_bookings')
        ) {
            return $query;
        }

        return $query->where(function ($query) {
            $query->whereHas('advertiseAnswer.advertise', function ($q) {
                $q->where('user_id', auth()->id());
            })->orWhereHas('advertiseAnswer.advertise', function ($q) {
                $q->whereHas('associatedUsers', function ($q) {
                    $q->where('users.id', auth()->id());
                });
            });
        });
    }

    /**
     * CONFIGURAÇÕES MELHORADAS PARA EVENTOS CURTOS
     */
    public function getFirstDay(): WeekDay
    {
        return WeekDay::Monday;
    }


    public function getAllDaySlot(): bool
    {
        return false; // Não mostrar slot "all day"
    }

    public function getNowIndicator(): bool
    {
        return true; // Mostrar indicador de hora atual
    }

    public function getScrollTime(): string
    {
        return '07:00:00'; // Começar scroll às 7h
    }

    public function getLocale(): string
    {
        return 'pt';
    }

    /**
     * AÇÕES DO CALENDÁRIO
     */
    public function getDateClickContextMenuActions(): array
    {
        return [
            $this->createScheduleAction(),
        ];
    }

    public function getEventClickContextMenuActions(): array
    {
        return [
            $this->viewAction(),
            $this->editAction(),
            $this->deleteAction(),
        ];
    }

    public function createScheduleAction(): CreateAction
    {
        return $this->createAction(Schedule::class)
            ->authorize('create', Schedule::class);
    }
}