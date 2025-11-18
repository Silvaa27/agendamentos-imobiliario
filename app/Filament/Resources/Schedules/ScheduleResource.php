<?php

namespace App\Filament\Resources\Schedules;

use App\Filament\Resources\Schedules\Schemas\ScheduleForm;
use App\Filament\Resources\Schedules\Tables\SchedulesTable;
use App\Models\Schedule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ScheduleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SchedulesTable::configure($table);
    }

    public static function getSchemas(): array
    {
        return [
            'form' => ScheduleForm::class,
            'table' => SchedulesTable::class,
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedule::route('/create'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
            'calendar' => Pages\CalendarPage::route('/calendar'), // ← Note o "Pages\"
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('super_admin') ||
            auth()->user()->can('view_shared_advertises_bookings') ||
            Schedule::whereHas('advertiseAnswer.advertise', function ($q) {
                $q->where('user_id', auth()->id())
                    ->orWhereHas('associatedUsers', function ($q) {
                        $q->where('users.id', auth()->id());
                    });
            })->exists();
    }
}
