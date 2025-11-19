<?php

namespace App\Filament\Resources\Schedules\Schemas;

use App\Models\Schedule;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextEntry::make('advertiseAnswer.advertise.title')
                            ->label('Anúncio')
                            ->formatStateUsing(fn($state) => $state ?? 'N/A')
                            ->weight('font-medium')
                            ->color('primary'),

                        TextEntry::make('advertiseAnswer.contact.name')
                            ->label('Cliente')
                            ->formatStateUsing(fn($state) => $state ?? 'N/A')
                            ->weight('font-medium'),
                    ]),

                DatePicker::make('date')
                    ->label('Data')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y'),

                TimePicker::make('start_time')
                    ->label('Hora Início')
                    ->seconds(false)
                    ->required(),

                TimePicker::make('end_time')
                    ->label('Hora Fim')
                    ->seconds(false)
                    ->required(),

                TextInput::make('formatted_period')
                    ->label('Período')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(
                        fn($state, $record) =>
                        $record ? "{$record->formatted_start_time} - {$record->formatted_end_time}" : ''
                    )
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}