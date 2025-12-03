<?php

namespace App\Filament\Resources\Opportunities\Tables;

use App\Models\Opportunity;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OpportunitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('first_photo')
                    ->label('')
                    ->getStateUsing(function ($record) {
                        return $record->getFirstMediaUrl('photos');
                    })
                    ->circular()
                    ->defaultImageUrl(url('/images/default-property.jpg'))
                    ->size(50),

                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 40) {
                            return null;
                        }
                        return $state;
                    })
                    ->weight('medium')
                    ->description(fn($record) => $record->address)
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('city')
                    ->label('Localização')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state ?: 'Não definida')
                    ->color(fn($state) => $state ? 'gray' : 'danger')
                    ->toggleable(isToggledHiddenByDefault: false),

                BadgeColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn($state): string => match ($state) {
                        'em_avaliacao' => 'Em Avaliação',
                        'ativa' => 'Ativa',
                        'em_andamento' => 'Em Andamento',
                        'concluida' => 'Concluída',
                        'cancelada' => 'Cancelada',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'em_avaliacao',
                        'success' => 'ativa',
                        'info' => 'em_andamento',
                        'gray' => 'concluida',
                        'danger' => 'cancelada',
                    ])
                    ->icons([
                        'heroicon-o-magnifying-glass' => 'em_avaliacao',
                        'heroicon-o-check-circle' => 'ativa',
                        'heroicon-o-play-circle' => 'em_andamento',
                        'heroicon-o-check-badge' => 'concluida',
                        'heroicon-o-x-circle' => 'cancelada',
                    ])
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('price_market')
                    ->label('Valor Mercado')
                    ->money('EUR', locale: 'pt')
                    ->sortable()
                    ->alignEnd()
                    ->color('success')
                    ->formatStateUsing(fn($state) => $state ? '€ ' . number_format($state, 2, ',', ' ') : '€ 0,00')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('purchase_price')
                    ->label('Custo Compra')
                    ->money('EUR', locale: 'pt')
                    ->sortable()
                    ->alignEnd()
                    ->color('warning')
                    ->formatStateUsing(fn($state) => $state ? '€ ' . number_format($state, 2, ',', ' ') : '€ 0,00')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_cost')
                    ->label('Custo Total')
                    ->getStateUsing(fn($record) => $record->total_cost)
                    ->money('EUR', locale: 'pt')
                    ->sortable()
                    ->alignEnd()
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('potential_profit')
                    ->label('Lucro Potencial')
                    ->getStateUsing(fn($record) => $record->potential_profit)
                    ->money('EUR', locale: 'pt')
                    ->sortable()
                    ->alignEnd()
                    ->color(fn($record) => $record->potential_profit > 0 ? 'success' : 'danger')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('profit_margin')
                    ->label('Margem (%)')
                    ->getStateUsing(fn($record) => $record->profit_margin)
                    ->sortable()
                    ->alignEnd()
                    ->color(fn($record) => $record->profit_margin > 20 ? 'success' : ($record->profit_margin > 0 ? 'warning' : 'danger'))
                    ->formatStateUsing(fn($state) => $state ? number_format($state, 2, ',', ' ') . '%' : '0%')
                    ->toggleable(isToggledHiddenByDefault: false),

                IconColumn::make('has_investment_program')
                    ->label('Programa Inv.')
                    ->boolean()
                    ->trueIcon('heroicon-o-user-group')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('user.name')
                    ->label('Responsável')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('investors_count')
                    ->label('Investidores')
                    ->getStateUsing(fn($record) => $record->investorsOnly()->count())
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'success' : 'gray')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('invoices_sum_amount')
                    ->label('Custos Reg.')
                    ->sum('invoices', 'amount')
                    ->money('EUR', locale: 'pt')
                    ->alignEnd()
                    ->color('warning')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('construction_updates_count')
                    ->label('Atualizações')
                    ->counts('constructionUpdates')
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'info' : 'gray')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(Opportunity::STATUSES)
                    ->searchable()
                    ->preload(),

                SelectFilter::make('city')
                    ->label('Cidade')
                    ->options(function () {
                        return Opportunity::query()
                            ->select('city')
                            ->distinct()
                            ->whereNotNull('city')
                            ->pluck('city', 'city')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('has_investment_program')
                    ->label('Tem Programa')
                    ->placeholder('Todos')
                    ->trueLabel('Com programa')
                    ->falseLabel('Sem programa'),

                TernaryFilter::make('has_photos')
                    ->label('Tem Fotos')
                    ->placeholder('Todos')
                    ->trueLabel('Com fotos')
                    ->falseLabel('Sem fotos')
                    ->queries(
                        true: fn(Builder $query) => $query->whereHas('media'),
                        false: fn(Builder $query) => $query->whereDoesntHave('media'),
                    ),

                TernaryFilter::make('has_invoices')
                    ->label('Tem Custos')
                    ->placeholder('Todos')
                    ->trueLabel('Com custos')
                    ->falseLabel('Sem custos')
                    ->queries(
                        true: fn(Builder $query) => $query->whereHas('invoices'),
                        false: fn(Builder $query) => $query->whereDoesntHave('invoices'),
                    ),

                TernaryFilter::make('has_construction_updates')
                    ->label('Tem Atualizações')
                    ->placeholder('Todos')
                    ->trueLabel('Com atualizações')
                    ->falseLabel('Sem atualizações')
                    ->queries(
                        true: fn(Builder $query) => $query->whereHas('constructionUpdates'),
                        false: fn(Builder $query) => $query->whereDoesntHave('constructionUpdates'),
                    ),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Eliminar Selecionadas')
                        ->modalHeading('Eliminar Oportunidades Selecionadas')
                        ->modalDescription('Tem certeza que deseja eliminar as oportunidades selecionadas? Esta ação não pode ser desfeita.')
                        ->successNotificationTitle('Oportunidades eliminadas com sucesso'),

                    BulkAction::make('activate')
                        ->label('Ativar Selecionadas')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'ativa']);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('mark_as_completed')
                        ->label('Marcar como Concluídas')
                        ->icon('heroicon-o-check-badge')
                        ->color('gray')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'concluida']);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateHeading('Nenhuma oportunidade encontrada')
            ->emptyStateDescription('Crie sua primeira oportunidade de investimento imobiliário.')
            ->emptyStateIcon('heroicon-o-building-office')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Criar Primeira Oportunidade')
                    ->button(),
            ])
            ->deferLoading()
            ->persistFiltersInSession()
            ->poll('60s');
    }
}