<?php

namespace App\Filament\Resources\Opportunities\RelationManagers;

use App\Filament\Resources\Opportunities\OpportunityResource;
use App\Models\Invoice;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    protected static ?string $relatedResource = OpportunityResource::class;

    public function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informação da Fatura')
                    ->schema([
                        TextInput::make('invoice_number')
                            ->label('Número da Fatura'),

                        TextInput::make('supplier')
                            ->label('Fornecedor'),

                        Select::make('type')
                            ->label('Tipo')
                            ->options(Invoice::TYPES)
                            ->default('outro')
                            ->required(),
                    ])->columns(3),

                Section::make('Valores e Datas')
                    ->schema([
                        TextInput::make('amount')
                            ->label('Valor (€)')
                            ->numeric()
                            ->prefix('€')
                            ->required(),

                        TextInput::make('description')
                            ->label('Descrição')
                            ->required()
                            ->maxLength(255),

                        DatePicker::make('invoice_date')
                            ->label('Data da Fatura')
                            ->required()
                            ->default(now()),

                        DatePicker::make('due_date')
                            ->label('Data de Vencimento'),

                        DatePicker::make('payment_date')
                            ->label('Data de Pagamento'),
                    ])->columns(2),

                Section::make('Estado e Anexos')
                    ->schema([
                        Select::make('status')
                            ->label('Estado')
                            ->options(Invoice::STATUSES)
                            ->default('pendente')
                            ->required(),

                        FileUpload::make('file_path')
                            ->label('Anexar Fatura')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->directory('invoices')
                            ->preserveFilenames(),

                        Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3),
                    ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
