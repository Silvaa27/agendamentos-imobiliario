<?php

namespace App\Filament\Resources\UserBookings\Tables;

use App\Models\Schedule;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserBookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('advertiseAnswer.advertise.title')
                    ->label('Formulário')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('advertiseAnswer.advertise.user.name')
                    ->label('Dono do Formulário')
                    ->sortable()
                    ->searchable(),

                // Nova coluna para mostrar o tipo de acesso
                TextColumn::make('access_type')
                    ->label('Tipo de Acesso')
                    ->getStateUsing(function (Schedule $record) {
                        $advertise = $record->advertiseAnswer->advertise;

                        // Se é o dono do formulário
                        if ($advertise->user_id === auth()->id()) {
                            return '👤 Meu Formulário';
                        }

                        // Se o formulário está partilhado com ele
                        if ($advertise->associatedUsers->contains(auth()->id())) {
                            return '🤝 Formulário Partilhado';
                        }

                        // Se tem permissão para ver tudo
                        if (
                            auth()->user()->hasRole('super_admin') ||
                            auth()->user()->can('view_shared_advertises_bookings')
                        ) {
                            return '🌍 Todas as Marcações';
                        }

                        return '🔒 Sem Acesso';
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        '👤 Meu Formulário' => 'success',
                        '🤝 Formulário Partilhado' => 'warning',
                        '🌍 Todas as Marcações' => 'info',
                        '🔒 Sem Acesso' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('date')
                    ->label('Data')
                    ->date()
                    ->sortable(),

                TextColumn::make('start_time')
                    ->label('Hora Início'),

                TextColumn::make('end_time')
                    ->label('Hora Fim'),

                // ... resto das colunas
            ])
            ->filters([
                // Filtro por tipo de acesso
                \Filament\Tables\Filters\SelectFilter::make('access_type')
                    ->label('Tipo de Acesso')
                    ->options([
                        'own' => 'Meus Formulários',
                        'shared' => 'Formulários Partilhados',
                    ])
                    ->query(function ($query, $data) {
                        if ($data['value'] === 'own') {
                            $query->whereHas('advertiseAnswer.advertise', function ($q) {
                                $q->where('user_id', auth()->id());
                            });
                        } elseif ($data['value'] === 'shared') {
                            $query->whereHas('advertiseAnswer.advertise', function ($q) {
                                $q->whereHas('associatedUsers', function ($q) {
                                    $q->where('users.id', auth()->id());
                                })->where('user_id', '!=', auth()->id());
                            });
                        }
                    }),
            ])
            ->actions([
                // ... tuas ações
            ])
            ->bulkActions([
                // ... bulk actions
            ]);
    }
}