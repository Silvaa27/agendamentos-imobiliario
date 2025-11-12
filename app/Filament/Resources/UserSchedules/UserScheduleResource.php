<?php

namespace App\Filament\Resources\UserSchedules;

use App\Filament\Resources\UserSchedules\Pages\CreateUserSchedule;
use App\Filament\Resources\UserSchedules\Pages\EditUserSchedule;
use App\Filament\Resources\UserSchedules\Pages\ListUserSchedules;
use App\Filament\Resources\UserSchedules\Schemas\UserScheduleForm;
use App\Filament\Resources\UserSchedules\Tables\UserSchedulesTable;
use App\Models\UserSchedule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserScheduleResource extends Resource
{
    protected static ?string $model = UserSchedule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return UserScheduleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserSchedulesTable::configure($table);
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
            'index' => ListUserSchedules::route('/'),
            'create' => CreateUserSchedule::route('/create'),
            'edit' => EditUserSchedule::route('/{record}/edit'),
        ];
    }
}
