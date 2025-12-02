<?php

namespace App\Filament\Resources\Opportunities\Tables;

use App\Models\Opportunity;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class OpportunitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('firstPhotoUrl')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-property.jpg')),

                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('address')
                    ->label('Localização')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('price_market')
                    ->label('Preço Mercado')
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'em_avaliacao' => 'gray',
                        'em_negociacao' => 'warning',
                        'em_obras' => 'info',
                        'em_venda' => 'success',
                        'concluido' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => Opportunity::STATUSES[$state] ?? $state),

                TextColumn::make('user.name')
                    ->label('Responsável')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(Opportunity::STATUSES),

                SelectFilter::make('user_id')
                    ->label('Responsável')
                    ->relationship('user', 'name'),

                TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
