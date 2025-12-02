<?php

namespace App\Filament\Resources\InvestmentOpportunities\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InvestmentOpportunityForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = auth()->user();
        $canViewAll = $user->can('view_all_opportunities');
        return $schema
            ->components([
                Section::make('Informações Básicas')
                    ->schema([
                        TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Descrição')
                            ->rows(3),
                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'em_avaliacao' => 'Em Avaliação',
                                'em_negociacao' => 'Em Negociação',
                                'em_obras' => 'Em Obras',
                                'em_venda' => 'Em Venda',
                            ])
                            ->required()
                            ->disabled(!$canViewAll),
                    ])->columns(1),

                Section::make('Localização')
                    ->schema([
                        TextInput::make('address')
                            ->label('Morada')
                            ->required()
                            ->disabled(!$canViewAll),
                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->disabled(!$canViewAll),
                        TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->disabled(!$canViewAll),
                        TextInput::make('opportunity_url')
                            ->label('URL da Oportunidade')
                            ->url()
                            ->disabled(!$canViewAll),
                    ])->columns(2),

                Section::make('Galeria de Fotos')
                    ->schema([
                        FileUpload::make('gallery')
                            ->label('Fotos')
                            ->multiple()
                            ->image()
                            ->directory('investment-opportunities')
                            ->reorderable()
                            ->appendFiles()
                            ->disabled(!$canViewAll),
                    ])->visible($canViewAll),

                Section::make('Preços')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('worst_case_price')
                                    ->label('Preço Pior Cenário')
                                    ->numeric()
                                    ->prefix('€')
                                    ->required()
                                    ->disabled(!$canViewAll),
                                TextInput::make('market_price')
                                    ->label('Preço Cenário Mercado')
                                    ->numeric()
                                    ->prefix('€')
                                    ->required()
                                    ->disabled(!$canViewAll),
                                TextInput::make('purchase_price')
                                    ->label('Preço de Compra')
                                    ->numeric()
                                    ->prefix('€')
                                    ->required()
                                    ->disabled(!$canViewAll),
                            ]),
                    ])->visible($canViewAll),

                Section::make('Custos de Obras')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('budgeted_renovation_cost')
                                    ->label('Valor Orçamentado')
                                    ->numeric()
                                    ->prefix('€')
                                    ->required()
                                    ->disabled(!$canViewAll),
                                TextInput::make('actual_renovation_cost')
                                    ->label('Valor com Desvio')
                                    ->numeric()
                                    ->prefix('€')
                                    ->disabled(!$canViewAll),
                            ]),
                    ])->visible($canViewAll),

                Section::make('Outros Custos')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('other_costs')
                                    ->label('Outros Custos')
                                    ->numeric()
                                    ->prefix('€')
                                    ->default(0)
                                    ->disabled(!$canViewAll),
                                TextInput::make('tax_costs')
                                    ->label('Custos com Impostos')
                                    ->numeric()
                                    ->prefix('€')
                                    ->default(0)
                                    ->disabled(!$canViewAll),
                            ]),
                    ])->visible($canViewAll),

                Section::make('Investidores com Acesso')
                    ->schema([
                        Select::make('investors')
                            ->label('Investidores')
                            ->relationship('investors', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->disabled(!$canViewAll)
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required(),
                                TextInput::make('nif')
                                    ->label('NIF')
                                    ->required(),
                                TextInput::make('phone')
                                    ->label('Contacto Telefónico')
                                    ->tel()
                                    ->required(),
                                TextInput::make('email')
                                    ->email()
                                    ->required(),
                            ]),
                    ])->visible($canViewAll),
            ]);
    }
}
