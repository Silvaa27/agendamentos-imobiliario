<?php

namespace App\Filament\Resources\InvestmentOpportunities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvestmentOpportunitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('address')
                    ->searchable(),
                TextColumn::make('latitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('longitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('worst_case_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('market_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('budgeted_renovation_cost')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('actual_renovation_cost')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('purchase_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('other_costs')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tax_costs')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('opportunity_url')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
