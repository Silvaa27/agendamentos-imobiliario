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
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class BusinessHoursTable
{
    public static function configure(Table $table): Table
    {
        $user = auth()->user();
        $hasViewAll = $user->can('view_all_businesshours');
        $canCreateDefault = $user->can('create_default_businesshours');

        return $table
            ->columns([
                TextColumn::make('day')
                    ->sortable()
                    ->label('Dia')
                    ->formatStateUsing(fn($state) => BusinessHour::DAYS[$state] ?? $state)
                    ->searchable(),

                TextColumn::make('start_time')
                    ->label('Início')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('end_time')
                    ->label('Fim')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Utilizador')
                    ->state(function ($record) {
                        if ($record->user_id === null) {
                            return 'Horário Default';
                        }

                        $user = User::find($record->user_id);
                        return $user ? $user->name : 'Utilizador #' . $record->user_id;
                    })
                    ->color(function ($record) {
                        if ($record->user_id === null) {
                            return 'success';
                        }

                        if ($record->user_id == auth()->id()) {
                            return 'primary';
                        }

                        return 'gray';
                    })
                    ->sortable()
                    ->badge()
                    ->searchable(),
            ])
            
            ->filters([
                SelectFilter::make('acesso')
                    ->label('Meus Horários')
                    ->options([
                        'meus' => 'Apenas os meus horários',
                        'meus_default' => 'Meus + Horários Default',
                        'todos' => 'Todos os horários',
                    ])
                    ->default(function () use ($user, $hasViewAll, $canCreateDefault) {
                        if ($hasViewAll)
                            return 'todos';
                        if ($canCreateDefault)
                            return 'meus_default';
                        return 'meus';
                    })
                    ->query(function (Builder $query, array $data) use ($user, $hasViewAll) {
                        if ($hasViewAll) {
                            if (isset($data['value'])) {
                                switch ($data['value']) {
                                    case 'meus':
                                        $query->where('user_id', $user->id);
                                        break;
                                    case 'meus_default':
                                        $query->where(function ($q) use ($user) {
                                            $q->where('user_id', $user->id)
                                                ->orWhereNull('user_id');
                                        });
                                        break;
                                    case 'todos':
                                        break;
                                }
                            }
                            return;
                        }

                        if (!isset($data['value'])) {
                            if ($user->can('create_default_businesshours')) {
                                $query->where(function ($q) use ($user) {
                                    $q->where('user_id', $user->id)
                                        ->orWhereNull('user_id');
                                });
                            } else {
                                $query->where('user_id', $user->id);
                            }
                        } else {
                            switch ($data['value']) {
                                case 'meus':
                                    $query->where('user_id', $user->id);
                                    break;
                                case 'meus_default':
                                    $query->where(function ($q) use ($user) {
                                        $q->where('user_id', $user->id)
                                            ->orWhereNull('user_id');
                                    });
                                    break;
                            }
                        }
                    })
                    ->hidden(fn() => $user->can('view_all_businesshours')),

                SelectFilter::make('user_id')
                    ->label('Filtrar por Utilizador')
                    ->options(function () {
                        $users = User::query()
                            ->pluck('name', 'id')
                            ->toArray();

                        return [
                            'null' => 'Horário Default',
                            ...$users
                        ];
                    })
                    ->query(function (Builder $query, array $data) {
                        if (!isset($data['value'])) {
                            return;
                        }

                        if ($data['value'] === 'null') {
                            $query->whereNull('user_id');
                        } else {
                            $query->where('user_id', $data['value']);
                        }
                    })
                    ->searchable()
                    ->preload(),

                SelectFilter::make('day')
                    ->label('Dia da Semana')
                    ->options(BusinessHour::DAYS)
                    ->searchable(),
            ])
            ->actions([
                EditAction::make(),
                ReplicateAction::make()
                    ->schema(fn($schema) => BusinessHourForm::configure($schema))
                    ->requiresConfirmation(false),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('day')
            ->modifyQueryUsing(function (Builder $query) use ($user) {
                $query->whereNull('advertise_id');
            });
    }
}