<?php

namespace App\Filament\Resources\Advertises\Schemas;

use App\Filament\Resources\BusinessHours\Schemas\BusinessHourForm;
use App\Models\Advertise;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AdvertiseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações do Anúncio')
                    ->description('Configurações básicas do anúncio e formulário')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Título do Anúncio')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if (!empty($state)) {
                                            $set('uuid', Str::random(6));
                                        }
                                    }),

                                Hidden::make('uuid')
                                    ->default(fn() => (string) Str::uuid()),

                            ]),

                        TextInput::make('url')
                            ->label('URL do Anúncio')
                            ->url()
                            ->maxLength(500)
                            ->helperText('Link para onde o anúncio redireciona (opcional)'),

                        Toggle::make('is_active')
                            ->label('Anúncio Ativo')
                            ->default(true)
                            ->helperText('Se desativado, o formulário não estará disponível para respostas'),
                    ]),

                Section::make('Campos do Formulário')
                    ->description('Configure os campos dinâmicos que aparecerão no formulário público')
                    ->schema([
                        self::getFieldsRepeater(),
                    ])
                    ->collapsible()
                    ->collapsed(fn($operation) => $operation === 'edit'),

                Section::make('Configurações de Agendamento')
                    ->description('Defina os horários disponíveis para agendamento')
                    ->schema([
                        Repeater::make('business_hours')
                            ->schema(components: BusinessHourForm::configure(new Schema())->getComponents())
                            ->default(function ($state) {
                                // Buscar dados do banco
                                $businessHours = \App\Models\BusinessHour::all();

                                return $businessHours->map(function ($hour) {
                                    return [
                                        'day' => $hour->day,
                                        'start_time' => $hour->start_time,
                                        'end_time' => $hour->end_time,
                                    ];
                                })->toArray();
                            })
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function getFieldsRepeater(): Repeater
    {
        return Repeater::make('advertise_fields')
            ->relationship('advertise_fields')
            ->label('Campos do Formulário')
            ->schema([
                Fieldset::make('Configurações do Campo')
                    ->schema([
                        TextInput::make('answer')
                            ->label('Nome do Campo')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Ex: Nome Completo, Email, Telefone, etc.'),

                        Select::make('field_type')
                            ->label('Tipo de Campo')
                            ->required()
                            ->options([
                                'TextInput' => 'Simple Text',
                                'Select' => 'Dropdown List',
                                'Checkbox' => 'Checkbox',
                                'Toggle' => 'Toggle (On/Off)',
                                'CheckboxList' => 'Checkbox List',
                                'Radio' => 'Radio Buttons',
                                'DatePicker' => 'Date Picker',
                                'TimePicker' => 'Time Picker',
                                'Slider' => 'Numeric Slider',
                                'Textarea' => 'Text Area',
                            ])
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Limpar configurações específicas quando o tipo muda
                                $set('options', []);
                                $set('min_value', null);
                                $set('max_value', null);
                                $set('step', null);
                            }),

                        Toggle::make('is_required')
                            ->label('Campo Obrigatório')
                            ->default(false)
                            ->helperText('Usuário deve preencher este campo'),

                        Toggle::make('show_tooltip')
                            ->label('Mostrar Tooltip/Ajuda')
                            ->default(false)
                            ->helperText('Exibir informação adicional sobre o campo'),
                    ]),

                Group::make()
                    ->schema(function (Get $get) {
                        $fieldType = $get('field_type');
                        $schema = [];

                        if (in_array($fieldType, ['Select', 'Radio', 'CheckboxList'])) {
                            $schema[] = Repeater::make('options')
                                ->label('Opções Disponíveis')
                                ->schema([
                                    TextInput::make('option')
                                        ->label('Valor da Opção')
                                        ->required()
                                        ->maxLength(255)
                                        ->helperText('Texto que aparecerá para o usuário'),
                                ])
                                ->defaultItems(2)
                                ->itemLabel(fn(array $state): ?string => $state['option'] ?? 'Nova opção')
                                ->helperText('Adicione as opções que o usuário poderá selecionar')
                                ->collapsible();
                        }

                        // Configurações numéricas para Slider
                        if ($fieldType === 'Slider') {
                            $schema[] = Grid::make(columns: 3)
                                ->schema([
                                    TextInput::make('min_value')
                                        ->label('Valor Mínimo')
                                        ->numeric()
                                        ->default(0)
                                        ->required(),

                                    TextInput::make('max_value')
                                        ->label('Valor Máximo')
                                        ->numeric()
                                        ->default(100)
                                        ->required(),

                                    TextInput::make('step')
                                        ->label('Incremento')
                                        ->numeric()
                                        ->default(1)
                                        ->helperText('Passo do slider (ex: 1, 0.5, 10)'),
                                ]);
                        }

                        // Configurações para campos numéricos
                        if ($fieldType === 'TextInput') {
                            $schema[] = Grid::make(2)
                                ->schema([
                                    TextInput::make('min_value')
                                        ->label('Valor Mínimo')
                                        ->numeric()
                                        ->helperText('Valor mínimo permitido (opcional)'),

                                    TextInput::make('max_value')
                                        ->label('Valor Máximo')
                                        ->numeric()
                                        ->helperText('Valor máximo permitido (opcional)'),
                                ]);
                        }

                        return $schema;
                    }),

                // Regras de Validação Avançadas
                Section::make('Regras de Validação Avançadas')
                    ->schema([
                        Repeater::make('validation_rules')
                            ->label('Regras Personalizadas')
                            ->schema([
                                Select::make('rule_type')
                                    ->label('Tipo de Regra')
                                    ->options([
                                        'min' => 'Valor Mínimo',
                                        'max' => 'Valor Máximo',
                                        'min_length' => 'Comprimento Mínimo',
                                        'max_length' => 'Comprimento Máximo',
                                        'email' => 'Email Válido',
                                        'url' => 'URL Válida',
                                        'regex' => 'Expressão Regular',
                                        'in' => 'Valor em Lista Permitida',
                                        'not_in' => 'Valor em Lista Proibida',
                                    ])
                                    ->required()
                                    ->live(),

                                TextInput::make('rule_value')
                                    ->label('Valor da Regra')
                                    ->required()
                                    ->visible(fn(Get $get) => !in_array($get('rule_type'), ['email', 'url']))
                                    ->helperText(function (Get $get) {
                                        return match ($get('rule_type')) {
                                            'min', 'max' => 'Valor numérico (ex: 18, 100.50)',
                                            'min_length', 'max_length' => 'Número de caracteres (ex: 3, 255)',
                                            'regex' => 'Expressão regular (ex: ^[A-Za-z ]+$)',
                                            'in', 'not_in' => 'Valores separados por vírgula (ex: opcao1,opcao2,opcao3)',
                                            default => 'Valor específico da regra',
                                        };
                                    }),
                            ])
                            ->defaultItems(0)
                            ->itemLabel(
                                fn(array $state): ?string =>
                                $state['rule_type'] ? "Regra: {$state['rule_type']}" : 'Nova regra'
                            )
                            ->helperText('Adicione regras de validação específicas para este campo')
                            ->collapsible(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ])
            ->defaultItems(0)
            ->itemLabel(
                fn(array $state): ?string =>
                $state['answer'] ?? 'Novo Campo'
            )
            ->reorderable()
            ->cloneable()
            ->collapseAllAction(
                fn(Action $action) => $action->label('Recolher todos os campos'),
            )
            ->expandAllAction(
                fn(Action $action) => $action->label('Expandir todos os campos'),
            )
            ->helperText('Adicione os campos que compõem o seu formulário personalizado');
    }
}