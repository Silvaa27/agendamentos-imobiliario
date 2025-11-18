<?php

namespace App\Filament\Resources\Schedules\Schemas;

use App\Models\Schedule;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class ScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('advertise_answer_id')
                    ->label('Resposta do Anúncio')
                    ->relationship('advertiseAnswer', 'id')
                    ->getOptionLabelFromRecordUsing(
                        fn($record) =>
                        "{$record->advertise->title} - {$record->contact->name}"
                    )
                    ->searchable(['advertise.title', 'contact.name'])
                    ->preload()
                    ->required()
                    ->columnSpanFull(),

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