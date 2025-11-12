<?php

namespace App\Filament\Resources\BusinessHours\Tables;

use App\Filament\Resources\BusinessHours\Schemas\BusinessHourForm;
use App\Models\BusinessHour;
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
        $hasViewAll = $user->can('view_all:businesshours');

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

                // 🔥 COLUNA DO UTILIZADOR - SEMPRE visível para admins
                TextColumn::make('user_id')
                    ->label($hasViewAll ? 'Utilizador' : 'Tipo')
                    ->formatStateUsing(function ($state) use ($hasViewAll, $user) {
                        if ($state === null) {
                            return '🌍 Horário Default';
                        }

                        if ($hasViewAll) {
                            // Para admins: mostra nome do utilizador
                            $userModel = \App\Models\User::find($state);
                            return $userModel ? $userModel->name : 'Utilizador #' . $state;
                        } else {
                            // Para não-admins: mostra tipo
                            if ($state === $user->id) {
                                return '👤 Meu Horário';
                            }
                            return '👤 Horário de Utilizador';
                        }
                    })
                    ->color(function ($state) use ($hasViewAll, $user) {
                        if ($state === null) {
                            return 'info';
                        }
                        if (!$hasViewAll && $state === $user->id) {
                            return 'success';
                        }
                        return 'gray';
                    })
                    ->sortable(),
            ])
            ->filters([
                // Podes adicionar filtros se quiseres
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
                if ($user->can('view_all:businesshours')) {
                    return $query;
                }

                // 🔥 SE TIVER PERMISSÃO CREATE_DEFAULT, MOSTRA OS SEUS + OS DEFAULT
                if ($user->can('create_default:businesshours')) {
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