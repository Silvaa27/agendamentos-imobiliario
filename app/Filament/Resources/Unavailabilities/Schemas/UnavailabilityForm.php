<?php

namespace App\Filament\Resources\Unavailabilities\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;

class UnavailabilityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DateTimePicker::make('start')
                    ->required(),
                DateTimePicker::make('end')
                    ->required(),
            ]);
    }
}
