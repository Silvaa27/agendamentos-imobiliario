<?php

namespace App\Filament\Resources\Unavailabilities\Schemas;

use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class UnavailabilityForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();

        $fields = [
            TextInput::make('title')
                ->label('Título')
                ->required()
                ->maxLength(255)
                ->placeholder('Ex: Férias, Consulta médica, Manutenção...')
                ->helperText('Descreva brevemente o motivo da indisponibilidade'),

            DateTimePicker::make('start')
                ->label('Data/Hora de Início')
                ->required()
                ->seconds(false),

            DateTimePicker::make('end')
                ->label('Data/Hora de Fim')
                ->required()
                ->seconds(false)
                ->after('start'),
        ];

        // 🔥 SE O UTILIZADOR TEM PERMISSÃO PARA CRIAR DEFAULT
        if ($user->can('create_default:unavailabilities')) {
            array_unshift(
                $fields,
                Select::make('unavailability_type')
                    ->label('Tipo de Indisponibilidade')
                    ->options([
                        'personal' => '👤 Indisponibilidade Pessoal (Apenas para mim)',
                        'global' => '🌍 Indisponibilidade Global (Para TODOS os utilizadores)',
                        'shared' => '👥 Indisponibilidade Partilhada (Selecionar utilizadores específicos)',
                    ])
                    ->default('personal')
                    ->reactive()
                    ->helperText('Escolha o tipo de indisponibilidade')
            );

            // 🔥 CAMPO user_id HIDDEN - será definido baseado no tipo
            array_unshift(
                $fields,
                Hidden::make('user_id')
                    ->default($user->id)
            );

            // 🔥 CAMPO PARA SELECIONAR UTILIZADORES - CORRIGIDO (INCLUI O PRÓPRIO UTILIZADOR)
            $fields[] = Select::make('associatedUsers')
                ->label('Partilhar com utilizadores')
                ->options(User::pluck('name', 'id')) // 🔥 INCLUI TODOS OS UTILIZADORES (INCLUINDO O PRÓPRIO)
                ->multiple()
                ->preload()
                ->searchable()
                ->visible(fn($get) => $get('unavailability_type') === 'shared')
                ->helperText('Selecione os utilizadores com quem quer partilhar esta indisponibilidade (pode incluir-se a si próprio se desejar)')
                ->dehydrated();

        }
        // 🔥 SE O UTILIZADOR TEM PERMISSÃO EDIT_ALL
        elseif ($user->can('edit_all:unavailabilities')) {
            array_unshift(
                $fields,
                Select::make('user_id')
                    ->label('Associar a Utilizador')
                    ->options([
                        null => '🌍 Indisponibilidade Global (Para todos)',
                        ...User::pluck('name', 'id')
                    ])
                    ->default(null)
                    ->searchable()
                    ->helperText('Selecione um utilizador específico ou "Global" para todos')
            );
        }
        // 🔥 UTILIZADORES NORMAISa
        else {
            array_unshift(
                $fields,
                Hidden::make('user_id')
                    ->default($user->id)
            );
        }

        return $schema->schema($fields);
    }
}