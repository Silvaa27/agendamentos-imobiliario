<?php

namespace App\Filament\Resources\AdvertiseResponses\Tables;

use App\Models\AdvertiseAnswer;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class AdvertiseResponsesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn() => AdvertiseAnswer::with(['contact', 'advertise', 'fieldAnswers', 'schedules']))
            ->columns([
                // Anúncio
                TextColumn::make('advertise.title')
                    ->label('Anúncio')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->advertise->url ?? 'Sem URL')
                    ->icon('heroicon-o-megaphone')
                    ->color('primary'),

                // Contacto
                TextColumn::make('contact.name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),

                TextColumn::make('contact.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-envelope')
                    ->copyable()
                    ->copyMessage('Email copiado!'),

                TextColumn::make('contact.phone_number')
                    ->label('Telefone')
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->copyable()
                    ->copyMessage('Telefone copiado!'),

                // COLUNA DE RESERVA - CORRIGIDA
                TextColumn::make('schedules')
                    ->label('Reserva')
                    ->formatStateUsing(function ($record) {
                        // Verifica se há schedules
                        if ($record->schedules->isEmpty()) {
                            return '---';
                        }

                        $schedule = $record->schedules->first();
                        
                        // Verifica se os dados existem
                        if (!$schedule->date || !$schedule->start_time || !$schedule->end_time) {
                            return 'Dados incompletos';
                        }
                        
                        try {
                            // Formata data
                            $date = Carbon::parse($schedule->date)->format('d/m/Y');
                            
                            // Formata horários - trata tanto time quanto timestamp
                            $startTime = Carbon::parse($schedule->start_time)->format('H:i');
                            $endTime = Carbon::parse($schedule->end_time)->format('H:i');
                            
                            return "{$date} {$startTime}-{$endTime}";
                        } catch (\Exception $e) {
                            \Log::error('Erro ao formatar schedule:', [
                                'record_id' => $record->id,
                                'date' => $schedule->date,
                                'start_time' => $schedule->start_time,
                                'end_time' => $schedule->end_time,
                                'error' => $e->getMessage()
                            ]);
                            return 'Erro no formato';
                        }
                    })
                    ->icon('heroicon-o-clock')
                    ->color(fn($record) => $record->schedules->isNotEmpty() ? 'success' : 'gray')
                    ->tooltip('Data e horário reservado'),

                // Estatísticas
                TextColumn::make('field_answers_count')
                    ->label('Campos')
                    ->counts('fieldAnswers')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->tooltip('Campos respondidos'),

                TextColumn::make('schedules_count')
                    ->label('Reservas')
                    ->counts('schedules')
                    ->icon('heroicon-o-calendar')
                    ->color(fn($state) => $state > 0 ? 'success' : 'gray')
                    ->tooltip('Total de horários reservados'),

                // Data de submissão
                TextColumn::make('created_at')
                    ->label('Submetido em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->tooltip('Data de submissão'),

                // Status - CORRIGIDO
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($record) => $record->schedules->isNotEmpty() ? 'success' : 'warning')
                    ->formatStateUsing(fn($record) => $record->schedules->isNotEmpty() ? 'Com Reserva' : 'Sem Reserva')
                    ->icon(fn($record) => $record->schedules->isNotEmpty() ? 'heroicon-o-check-circle' : 'heroicon-o-clock'),
            ])
            ->filters([
                // Filtro por anúncio
                SelectFilter::make('advertise_id')
                    ->relationship('advertise', 'title')
                    ->label('Filtrar por Anúncio')
                    ->searchable()
                    ->preload(),

                // Filtro por reservas - CORRIGIDO
                Filter::make('has_schedules')
                    ->label('Com Reservas')
                    ->query(fn(Builder $query) => $query->has('schedules')),

                Filter::make('no_schedules')
                    ->label('Sem Reservas')
                    ->query(fn(Builder $query) => $query->doesntHave('schedules')),

                // Filtro por data de submissão - CORRIGIDO
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('De'),
                        DatePicker::make('created_until')
                            ->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Ver Detalhes')
                    ->icon('heroicon-o-eye')
                    ->color('primary'),

                DeleteAction::make()
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash')
                    ->color('danger'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Eliminar Selecionados')
                        ->icon('heroicon-o-trash'),
                ]),
            ])
            ->emptyStateHeading('Nenhuma resposta encontrada')
            ->emptyStateDescription('Quando receber respostas dos seus anúncios, elas aparecerão aqui.')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right')
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}