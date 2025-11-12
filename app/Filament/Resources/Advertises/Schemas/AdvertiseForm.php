<?php

namespace App\Filament\Resources\Advertises\Schemas;

use App\Filament\Resources\BusinessHours\Schemas\BusinessHourForm;
use App\Models\Advertise;
use App\Models\User;
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
            ->schema([
                // Campo para associar o utilizador criador (apenas visível para super_admin)
                Hidden::make('user_id')
                    ->default(auth()->id()),

                // Campo para adicionar outros utilizadores com acesso total a ESTE anúncio
                Select::make('associatedUsers')
                    ->label('Adicionar utilizadores com acesso total')
                    ->relationship('associatedUsers', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->visible(
                        fn($record): bool =>
                        // Apenas o criador ou super_admin podem adicionar utilizadores
                        !$record ||
                        $record->user_id === auth()->id() ||
                        auth()->user()->hasRole('super_admin')
                    )
                    ->helperText('Estes utilizadores terão as mesmas permissões que tu para gerir este anúncio específico')
                    ->columnSpanFull(),

                TextInput::make('title')
                    ->label('Título do Anúncio')
                    ->required()
                    ->maxLength(255),

                Hidden::make('uuid')
                    ->default(fn() => substr((string) Str::uuid(), 0, 8)),

                TextInput::make('url')
                    ->label('URL do Anúncio')
                    ->url()
                    ->maxLength(500)
                    ->helperText('Link para onde o anúncio redireciona (opcional)'),

                Toggle::make('is_active')
                    ->label('Anúncio Ativo')
                    ->default(true)
                    ->helperText('Se desativado, o formulário não estará disponível para respostas')
                    ->columnSpanFull(),

                Section::make('Campos do Formulário')
                    ->schema([
                        self::getFieldsRepeater(),
                    ])
                    ->collapsible(),

                Section::make('Horários')
                    ->schema([
                        Repeater::make('businessHours')
                            ->label('Horários de Funcionamento')
                            ->relationship('businessHours')
                            ->schema(components: BusinessHourForm::configure(new Schema())->getComponents())
                            ->default(function ($state, $operation) {
                                // 🔥 PREENCHE COM OS BUSINESS HOURS DO UTILIZADOR
                                if ($operation === 'create' && empty($state)) {
                                    $user = auth()->user();

                                    // Se tiver permissão, pode usar templates globais também
                                    if ($user->can('view_all:businesshours')) {
                                        $businessHours = \App\Models\BusinessHour::whereNull('advertise_id')
                                            ->where(function ($query) use ($user) {
                                                $query->where('user_id', $user->id)
                                                    ->orWhereNull('user_id'); // Inclui templates globais
                                            })
                                            ->get();
                                    } else {
                                        // Apenas os templates do utilizador
                                        $businessHours = \App\Models\BusinessHour::where('user_id', $user->id)
                                            ->whereNull('advertise_id')
                                            ->get();
                                    }

                                    if ($businessHours->count() > 0) {
                                        return $businessHours->map(function ($hour) {
                                            return [
                                                'day' => $hour->day,
                                                'start_time' => $hour->start_time,
                                                'end_time' => $hour->end_time,
                                            ];
                                        })->toArray();
                                    }
                                }

                                return $state;
                            })
                            ->collapsible()
                            ->helperText('Horários preenchidos automaticamente com base nos seus templates')
                    ])
                    ->collapsible(),
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
                            ->helperText('Ex: Nome Completo, Email, Telefone, etc.')
                            ->columnSpanFull(),

                        Select::make('field_type')
                            ->label('Tipo de Campo')
                            ->required()
                            ->options([
                                'TextInput' => 'Texto Simples',
                                'NumberInput' => 'Campo Numérico',
                                'Select' => 'Lista Suspensa',
                                'Checkbox' => 'Checkbox',
                                'Toggle' => 'Toggle (Ligado/Desligado)',
                                'CheckboxList' => 'Lista de Checkboxes',
                                'Radio' => 'Botões de Rádio',
                                'DatePicker' => 'Selecionador de Data',
                                'TimePicker' => 'Selecionador de Hora',
                                'Slider' => 'Slider Numérico',
                                'Textarea' => 'Área de Texto',
                            ])
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Limpar configurações específicas quando o tipo muda
                                $set('options', []);
                                $set('min_value', null);
                                $set('max_value', null);
                                $set('step', null);

                                // Limpar regras de validação quando o tipo muda
                                $set('validation_rules', []);

                                // Para Slider e NumberInput, manter min/max, para outros limpar
                                if (!in_array($state, ['Slider', 'NumberInput'])) {
                                    $set('min_value', null);
                                    $set('max_value', null);
                                    $set('step', null);
                                }
                            })
                            ->columnSpanFull(),

                        Toggle::make('is_required')
                            ->label('Campo Obrigatório')
                            ->default(false)
                            ->helperText('Usuário deve preencher este campo')
                            ->columnSpanFull(),

                        Toggle::make('show_tooltip')
                            ->label('Mostrar Tooltip/Ajuda')
                            ->default(false)
                            ->helperText('Exibir informação adicional sobre o campo (apenas para Slider)')
                            ->visible(fn(Get $get) => $get('field_type') === 'Slider')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Group::make()
                    ->schema(function (Get $get) {
                        $fieldType = $get('field_type');
                        $schema = [];

                        // Verificar se fieldType não é nulo
                        if (!$fieldType) {
                            return $schema;
                        }

                        // Opções para campos de seleção
                        if (in_array($fieldType, ['Select', 'Radio', 'CheckboxList'])) {
                            $schema[] = Repeater::make('options')
                                ->label('Opções Disponíveis')
                                ->schema([
                                    TextInput::make('option')
                                        ->label('Valor da Opção')
                                        ->required()
                                        ->maxLength(255)
                                        ->helperText('Texto que aparecerá para o usuário')
                                        ->columnSpanFull(),
                                ])
                                ->defaultItems(2)
                                ->itemLabel(fn(array $state): ?string => $state['option'] ?? 'Nova opção')
                                ->helperText('Adicione as opções que o usuário poderá selecionar')
                                ->collapsible()
                                ->columnSpanFull();
                        }

                        // Configurações numéricas para Slider e NumberInput
                        if (in_array($fieldType, ['Slider', 'NumberInput'])) {
                            $schema[] = Grid::make(1)
                                ->schema([
                                    TextInput::make('min_value')
                                        ->label('Valor Mínimo')
                                        ->numeric()
                                        ->default($fieldType === 'Slider' ? 0 : null)
                                        ->required($fieldType === 'Slider')
                                        ->helperText($fieldType === 'NumberInput' ? 'Valor mínimo permitido (opcional)' : 'Valor mínimo obrigatório para slider')
                                        ->columnSpanFull(),

                                    TextInput::make('max_value')
                                        ->label('Valor Máximo')
                                        ->numeric()
                                        ->default($fieldType === 'Slider' ? 100 : null)
                                        ->required($fieldType === 'Slider')
                                        ->helperText($fieldType === 'NumberInput' ? 'Valor máximo permitido (opcional)' : 'Valor máximo obrigatório para slider')
                                        ->columnSpanFull(),

                                    TextInput::make('step')
                                        ->label('Incremento')
                                        ->numeric()
                                        ->default(1)
                                        ->helperText('Passo do campo (ex: 1, 0.1, 10)')
                                        ->visible(fn(Get $get) => $get('field_type') === 'Slider')
                                        ->columnSpanFull(),
                                ]);
                        }

                        // Regras de Validação baseadas no tipo de campo
                        $validationRules = self::getValidationRulesForFieldType($fieldType);

                        if (!empty($validationRules)) {
                            $schema[] = Section::make('Regras de Validação')
                                ->schema([
                                    Repeater::make('validation_rules')
                                        ->label('Regras de Validação')
                                        ->schema([
                                            Select::make('rule_type')
                                                ->label('Tipo de Regra')
                                                ->options($validationRules)
                                                ->required()
                                                ->live()
                                                ->columnSpanFull(),

                                            TextInput::make('rule_value')
                                                ->label('Valor da Regra')
                                                ->required()
                                                ->visible(fn(Get $get) => !in_array($get('rule_type'), ['email', 'url']))
                                                ->helperText(function (Get $get) use ($fieldType) {
                                                    return match ($get('rule_type')) {
                                                        'min', 'max' => self::getMinMaxHelperText($fieldType),
                                                        'min_length', 'max_length' => 'Número de caracteres (ex: 3, 255)',
                                                        'regex' => 'Expressão regular (ex: ^[A-Za-z ]+$)',
                                                        'in', 'not_in' => 'Valores separados por vírgula (ex: valor1,valor2,valor3) ou (true,false) para checkbox',
                                                        default => 'Valor específico da regra',
                                                    };
                                                })
                                                ->columnSpanFull(),
                                        ])
                                        ->defaultItems(0)
                                        ->itemLabel(
                                            fn(array $state): ?string =>
                                            $state['rule_type'] ? "Regra: {$state['rule_type']}" : 'Nova regra'
                                        )
                                        ->helperText('Adicione regras de validação específicas para este tipo de campo')
                                        ->collapsible()
                                        ->columnSpanFull(),
                                ])
                                ->collapsible()
                                ->collapsed()
                                ->columnSpanFull();
                        }

                        return $schema;
                    })
                    ->columnSpanFull(),
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
            ->helperText('Adicione os campos que compõem o seu formulário personalizado')
            ->columnSpanFull()
            ->collapsible();
    }

    /**
     * Define regras de validação específicas para cada tipo de campo
     * baseado na lógica do Livewire AdvertismentForm
     */
    private static function getValidationRulesForFieldType(?string $fieldType): array
    {
        // Se fieldType for nulo, retornar array vazio
        if (!$fieldType) {
            return [];
        }

        return match ($fieldType) {
            // Campos de texto
            'TextInput', 'Textarea' => [
                'min_length' => 'Comprimento Mínimo',
                'max_length' => 'Comprimento Máximo',
                'email' => 'Email Válido',
                'url' => 'URL Válida',
                'regex' => 'Expressão Regular',
            ],

            // Campos numéricos (Slider e NumberInput)
            'Slider', 'NumberInput' => [
                'min' => 'Valor Mínimo',
                'max' => 'Valor Máximo',
            ],

            // Campos de seleção
            'Select', 'Radio', 'CheckboxList' => [
                'in' => 'Valor Permitido',
                'not_in' => 'Valor Proibido',
            ],

            // Campos de data/hora
            'DatePicker' => [
                'min' => 'Data Mínima',
                'max' => 'Data Máxima',
            ],

            'TimePicker' => [
                'min' => 'Hora Mínima',
                'max' => 'Hora Máxima',
            ],

            // Campos booleanos
            'Toggle', 'Checkbox' => [
                'in' => 'Valores Permitidos',
                'not_in' => 'Valores Proibidos',
            ],

            default => [],
        };
    }

    /**
     * Helper text específico para regras min/max baseado no tipo de campo
     */
    private static function getMinMaxHelperText(string $fieldType): string
    {
        return match ($fieldType) {
            'DatePicker' => 'Data no formato YYYY-MM-DD (ex: 2024-12-31)',
            'TimePicker' => 'Hora no formato HH:MM (ex: 09:00)',
            'Slider', 'NumberInput' => 'Valor numérico (ex: 18, 100.50)',
            default => 'Valor numérico ou de comprimento',
        };
    }
}