<?php

namespace App\Filament\Resources\Unavailabilities\Tables;

use App\Filament\Resources\Unavailabilities\Schemas\UnavailabilityForm;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UnavailabilitiesTable
{
    public static function configure(Table $table): Table
    {
        $user = auth()->user();
        $hasViewAll = $user->can('view_all_unavailabilities');

        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->sortable()
                    ->searchable()
                    ->limit(30),

                TextColumn::make('start')
                    ->label('Início')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('end')
                    ->label('Fim')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('visibility_type')
                    ->label($hasViewAll ? 'Visibilidade' : 'Tipo')
                    ->state(function ($record) use ($hasViewAll, $user) {
                        if (!$record->relationLoaded('associatedUsers')) {
                            $record->load('associatedUsers');
                        }

                        // LÓGICA SIMPLIFICADA
                        if ($record->user_id === null) {
                            if ($record->associatedUsers->count() > 0) {
                                $userNames = $record->associatedUsers->pluck('name')->join(', ');
                                return $userNames;
                            }
                            return 'Global (Todos os utilizadores)';
                        }

                        if ($hasViewAll) {
                            $owner = User::find($record->user_id);
                            $sharedCount = $record->associatedUsers->count();

                            if ($sharedCount > 0) {
                                $userNames = $record->associatedUsers->pluck('name')->join(', ');
                                return $owner->name . ' + ' . $userNames;
                            }
                            return $owner->name;
                        } else {
                            if ($record->user_id === $user->id) {
                                $sharedCount = $record->associatedUsers->count();
                                if ($sharedCount > 0) {
                                    $userNames = $record->associatedUsers->pluck('name')->join(', ');
                                    return 'Minha (Partilhada com: ' . $userNames . ')';
                                }
                                return 'Minha';
                            }
                            if ($record->associatedUsers->contains($user->id)) {
                                return 'Partilhada comigo';
                            }
                            return 'Outros';
                        }
                    })
                    ->color(function ($record) use ($hasViewAll, $user) {
                        if (!$record->relationLoaded('associatedUsers')) {
                            $record->load('associatedUsers');
                        }

                        if ($record->user_id === null) {
                            return 'info';
                        }
                        if ($record->user_id === $user->id) {
                            return 'success';
                        }
                        if ($record->associatedUsers->contains($user->id)) {
                            return 'warning';
                        }
                        return 'gray';
                    })
                    ->badge()
                    ->wrap(),
            ])
            ->filters([])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                ReplicateAction::make()
                    ->schema(fn($schema) => UnavailabilityForm::configure($schema))
                    ->requiresConfirmation(false),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start')
            ->modifyQueryUsing(function (Builder $query) use ($user) {
                $query->where('end', '>=', now());

                $query->with(['user', 'associatedUsers']);

                if ($user->can('view_all_unavailabilities')) {
                    return $query;
                }

                return $query->visibleTo($user);
            });
    }
}