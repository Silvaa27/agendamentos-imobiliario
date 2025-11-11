<?php

namespace App\Filament\Resources\BusinessHours\Tables;

use App\Filament\Resources\BusinessHours\Schemas\BusinessHourForm;
use App\Models\BusinessHour;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BusinessHoursTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('day')
                    //->formatStateUsing(fn($state) => BusinessHour::DAYS[$state])
                    ->sortable()
                    ->label('Dia'),

                TextColumn::make('start_time')
                    ->label('Inicio'),

                TextColumn::make('end_time')
                    ->label('Fim'),
            ])
            ->filters([
            ])
            ->actions([
                ReplicateAction::make()
                ->schema(fn($schema) => BusinessHourForm::configure($schema))
                ->requiresConfirmation(false),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('day');

    }

}
