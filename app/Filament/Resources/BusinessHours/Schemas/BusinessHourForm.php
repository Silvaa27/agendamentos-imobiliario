<?php

namespace App\Filament\Resources\BusinessHours\Schemas;

use App\Models\BusinessHour;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class BusinessHourForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('day')
                    ->options(BusinessHour::DAYS)
                    ->required()
                    ->label('Dia da Semana'),
                TimePicker::make('start_time')
                    ->seconds(false)
                    ->nullable()
                    ->label('Início'),
                TimePicker::make('end_time')
                    ->seconds(false)
                    ->nullable()
                    ->after('start_time')
                    ->label('Fim'),
            ]);
    }
}
