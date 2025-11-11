<?php

namespace App\Filament\Resources\BusinessHours;

use App\Filament\Resources\BusinessHours\Pages\CreateBusinessHour;
use App\Filament\Resources\BusinessHours\Pages\EditBusinessHour;
use App\Filament\Resources\BusinessHours\Pages\ListBusinessHours;
use App\Filament\Resources\BusinessHours\Schemas\BusinessHourForm;
use App\Filament\Resources\BusinessHours\Tables\BusinessHoursTable;
use App\Models\BusinessHour;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BusinessHourResource extends Resource
{
    protected static ?string $model = BusinessHour::class;
    protected static ?string $modelLabel = 'Horário';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return BusinessHourForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BusinessHoursTable::configure($table);
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
            'index' => ListBusinessHours::route('/'),
            'create' => CreateBusinessHour::route('/create'),
            'edit' => EditBusinessHour::route('/{record}/edit'),
        ];
    }
}
