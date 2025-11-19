<?php

namespace App\Filament\Resources\Schedules\Tables;

use App\Models\Schedule;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('advertiseAnswer.advertise.title')
                    ->label('Anúncio')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('advertiseAnswer.contact.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('advertiseAnswer.contact.email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('advertiseAnswer.contact.phone_number')
                    ->label('Telefone')
                    ->searchable(),

                TextColumn::make('date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('formatted_start_time')
                    ->label('Início'),

                TextColumn::make('formatted_end_time')
                    ->label('Fim'),

                TextColumn::make('formatted_period')
                    ->label('Período'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('advertise')
                    ->label('Anúncio')
                    ->relationship('advertiseAnswer.advertise', 'title')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        DatePicker::make('date_from')
                            ->label('Data Inicial'),
                        DatePicker::make('date_until')
                            ->label('Data Final'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }
}