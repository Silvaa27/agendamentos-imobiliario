<?php

namespace App\Filament\Resources\OpportunityResource\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvestorsRelationManager extends RelationManager
{
    protected static string $relationship = 'investors';

    public function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('investor_id')
                    ->label('Investidor')
                    ->relationship('investors', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('pivot.investment_amount')
                    ->label('Valor Investido (€)')
                    ->numeric()
                    ->prefix('€'),

                TextInput::make('pivot.percentage')
                    ->label('Percentagem (%)')
                    ->numeric()
                    ->suffix('%')
                    ->minValue(0)
                    ->maxValue(100),

                Toggle::make('pivot.has_access')
                    ->label('Tem Acesso ao Programa')
                    ->inline(false),

                DateTimePicker::make('pivot.access_granted_at')
                    ->label('Acesso Concedido em')
                    ->visible(fn($get) => $get('pivot.has_access')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),

                TextColumn::make('nif')
                    ->label('NIF'),

                TextColumn::make('pivot.investment_amount')
                    ->label('Valor Investido')
                    ->money('EUR'),

                TextColumn::make('pivot.percentage')
                    ->label('Percentagem')
                    ->suffix('%'),

                IconColumn::make('pivot.has_access')
                    ->label('Acesso')
                    ->boolean(),

                TextColumn::make('pivot.access_granted_at')
                    ->label('Acesso Concedido')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->form(fn(AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('Investidor')
                            ->required(),

                        TextInput::make('investment_amount')
                            ->label('Valor Investido (€)')
                            ->numeric()
                            ->prefix('€'),

                        TextInput::make('percentage')
                            ->label('Percentagem (%)')
                            ->numeric()
                            ->suffix('%'),

                        Toggle::make('has_access')
                            ->label('Conceder Acesso ao Programa'),
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DetachAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}