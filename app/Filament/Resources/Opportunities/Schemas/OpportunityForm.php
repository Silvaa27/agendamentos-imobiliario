<?php

namespace App\Filament\Resources\Opportunities\Schemas;

use App\Models\Opportunity;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class OpportunityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Informação Básica')
                            ->schema([
                                Section::make('Identificação')
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Título')
                                            ->required()
                                            ->maxLength(255),
                                        Textarea::make('description')
                                            ->label('Descrição')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        Select::make('status')
                                            ->label('Estado')
                                            ->options(Opportunity::STATUSES)
                                            ->default('em_avaliacao')
                                            ->required(),
                                        Select::make('user_id')
                                            ->label('Responsável')
                                            ->relationship('user', 'name')
                                            ->searchable()
                                            ->preload(),
                                    ])->columns(2),

                                Section::make('Localização')
                                    ->schema([
                                        TextInput::make('address')
                                            ->label('Morada')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('city')
                                            ->label('Cidade'),
                                        TextInput::make('postal_code')
                                            ->label('Código Postal'),
                                        TextInput::make('latitude')
                                            ->label('Latitude')
                                            ->numeric()
                                            ->step(0.00000001),
                                        TextInput::make('longitude')
                                            ->label('Longitude')
                                            ->numeric()
                                            ->step(0.00000001),
                                    ])->columns(2),

                                Section::make('Fotos')
                                    ->schema([
                                        FileUpload::make('photos')
                                            ->label('Galeria de Fotos')
                                            ->multiple()
                                            ->image()
                                            ->directory('opportunities')
                                            ->maxFiles(20)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tabs\Tab::make('Preços e Custos')
                            ->schema([
                                Section::make('Preços de Venda')
                                    ->schema([
                                        TextInput::make('price_worst_case')
                                            ->label('Preço Pior Cenário (€)')
                                            ->numeric()
                                            ->prefix('€'),
                                        TextInput::make('price_market')
                                            ->label('Preço Cenário Mercado (€)')
                                            ->numeric()
                                            ->prefix('€'),
                                    ])->columns(2),

                                Section::make('Custos')
                                    ->schema([
                                        TextInput::make('purchase_price')
                                            ->label('Preço de Compra (€)')
                                            ->numeric()
                                            ->prefix('€'),
                                        TextInput::make('budgeted_work_value')
                                            ->label('Valor Orçamentado Obras (€)')
                                            ->numeric()
                                            ->prefix('€'),
                                        TextInput::make('actual_work_value')
                                            ->label('Valor com Desvio Obras (€)')
                                            ->numeric()
                                            ->prefix('€'),
                                        TextInput::make('other_costs')
                                            ->label('Outros Custos (€)')
                                            ->numeric()
                                            ->prefix('€'),
                                        TextInput::make('tax_costs')
                                            ->label('Custos com Impostos (€)')
                                            ->numeric()
                                            ->prefix('€'),
                                    ])->columns(2),

                                Section::make('Resumo Financeiro')
                                    ->schema([
                                        Placeholder::make('total_cost')
                                            ->label('Custo Total')
                                            ->content(function ($record) {
                                                if (!$record)
                                                    return '€0.00';
                                                return '€' . number_format($record->total_cost, 2, ',', ' ');
                                            }),
                                        Placeholder::make('potential_profit')
                                            ->label('Lucro Potencial')
                                            ->content(function ($record) {
                                                if (!$record)
                                                    return '€0.00';
                                                return '€' . number_format($record->potential_profit, 2, ',', ' ');
                                            }),
                                        Placeholder::make('profit_margin')
                                            ->label('Margem (%)')
                                            ->content(function ($record) {
                                                if (!$record)
                                                    return '0%';
                                                return number_format($record->profit_margin, 2, ',', ' ') . '%';
                                            }),
                                    ])->columns(3),
                            ]),

                        Tabs\Tab::make('Informações Adicionais')
                            ->schema([
                                Section::make('Informação do Imóvel')
                                    ->schema([
                                        Textarea::make('property_info')
                                            ->label('Informação Detalhada')
                                            ->rows(10)
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Links e Acessos')
                                    ->schema([
                                        TextInput::make('opportunity_link')
                                            ->label('Link da Oportunidade (URL)')
                                            ->url()
                                            ->columnSpanFull(),
                                        Toggle::make('has_investment_program')
                                            ->label('Tem Programa de Investidores')
                                            ->inline(false),
                                    ]),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
