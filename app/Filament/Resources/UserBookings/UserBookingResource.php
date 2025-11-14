<?php

namespace App\Filament\Resources\UserBookings;

use App\Filament\Resources\UserBookings\Pages\CreateUserBooking;
use App\Filament\Resources\UserBookings\Pages\EditUserBooking;
use App\Filament\Resources\UserBookings\Pages\ListUserBookings;
use App\Filament\Resources\UserBookings\Schemas\UserBookingForm;
use App\Filament\Resources\UserBookings\Tables\UserBookingsTable;
use App\Models\Schedule;
use App\Models\UserBooking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserBookingResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return UserBookingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserBookingsTable::configure($table);
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
}
