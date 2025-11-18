<?php

namespace App\Filament\Resources\UserBookings\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserBookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informações da Marcação')
                    ->schema([
                        TextEntry::make('contact.name')
                            ->label('Nome')
                            ->icon('heroicon-o-user')
                            ->default('N/A'),

                        DatePicker::make('date')
                            ->label('Data')
                            ->required()
                            ->native(true),
                        TimePicker::make('start_time')
                            ->label('Hora de Início')
                            ->required()
                            ->seconds(true),

                        TimePicker::make('end_time')
                            ->label('Hora de Fim')
                            ->required()
                            ->seconds(true),
                    ])
                    ->columns(2),
            ]);
    }
}
