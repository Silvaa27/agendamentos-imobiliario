<?php

namespace App\Filament\Resources\UserBookings;

use App\Filament\Resources\UserBookings\Pages\CreateUserBooking;
use App\Filament\Resources\UserBookings\Pages\EditUserBooking;
use App\Filament\Resources\UserBookings\Pages\ListUserBookings;
use App\Filament\Resources\UserBookings\Schemas\UserBookingForm;
use App\Filament\Resources\UserBookings\Tables\UserBookingsTable;
use App\Models\Schedule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserBookingResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $modelLabel = 'Marcações';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return UserBookingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserBookingsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Se for super_admin ou tiver a permissão, vê TUDO
        if (
            auth()->user()->hasRole('super_admin') ||
            auth()->user()->can('view_shared_advertises_bookings')
        ) {
            return $query;
        }

        // Caso contrário, vê apenas:
        // 1. Marcações dos SEUS formulários (onde é o dono)
        // 2. Marcações de formulários PARTILHADOS com ele
        return $query->where(function ($query) {
            $query->whereHas('advertiseAnswer.advertise', function ($q) {
                // SEUS formulários (é o dono)
                $q->where('user_id', auth()->id());
            })->orWhereHas('advertiseAnswer.advertise', function ($q) {
                // Formulários PARTILHADOS com ele
                $q->whereHas('associatedUsers', function ($q) {
                    $q->where('users.id', auth()->id());
                });
            });
        });
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
            'index' => ListUserBookings::route('/'),
            'create' => CreateUserBooking::route('/create'),
            'edit' => EditUserBooking::route('/{record}/edit'),
        ];
    }

    // Opcional: Controlar visibilidade do resource no menu
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