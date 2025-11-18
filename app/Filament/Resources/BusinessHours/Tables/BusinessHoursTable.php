<?php

namespace App\Filament\Resources\BusinessHours\Tables;

use App\Filament\Resources\BusinessHours\Schemas\BusinessHourForm;
use App\Models\BusinessHour;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BusinessHoursTable
{
    public static function configure(Table $table): Table
    {
        $user = auth()->user();
        $hasViewAll = $user->can('view_all_businesshours');

        return $table
            ->columns([
                TextColumn::make('day')
                    ->sortable()
                    ->label('Dia')
                    ->formatStateUsing(fn($state) => BusinessHour::DAYS[$state] ?? $state),

                TextColumn::make('start_time')
                    ->label('Inicio')
                    ->time('H:i'),

                TextColumn::make('end_time')
                    ->label('Fim')
                    ->time('H:i'),

                // 🔥 COLUNA SIMPLES E DIRETA
                TextColumn::make('Utilizador')
                    ->label('Utilizador')
                    ->state(function ($record) {
                        \Log::info('DEBUG - Coluna Utilizador:', [
                            'record_id' => $record->id,
                            'user_id' => $record->user_id
                        ]);

                        if ($record->user_id === null) {
                            return '🌍 Horário Default';
                        }

                        $user = User::find($record->user_id);
                        return $user ? $user->name : 'Utilizador #' . $record->user_id;
                    })
                    ->color(function ($record) {
                        return $record->user_id === null ? 'info' : 'gray';
                    })
                    ->sortable()
                    ->badge()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ReplicateAction::make()
                    ->schema(fn($schema) => BusinessHourForm::configure($schema))
                    ->requiresConfirmation(false),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('day')
            ->modifyQueryUsing(function (Builder $query) use ($user) {
                $query->whereNull('advertise_id'); // Apenas templates
    
                // 🔥 SE TIVER PERMISSÃO VIEW_ALL, MOSTRA TODOS OS HORÁRIOS
                if ($user->can('view_all_businesshours')) {
                    return $query;
                }

                // 🔥 SE TIVER PERMISSÃO CREATE_DEFAULT, MOSTRA OS SEUS + OS DEFAULT
                if ($user->can('create_default_businesshours')) {
                    $query->where(function ($q) use ($user) {
                        $q->where('user_id', $user->id)
                            ->orWhereNull('user_id');
                    });
                    return $query;
                }

                // 🔥 UTILIZADORES NORMAIS - APENAS OS SEUS HORÁRIOS
                $query->where('user_id', $user->id);

                return $query;
            });
    }
}