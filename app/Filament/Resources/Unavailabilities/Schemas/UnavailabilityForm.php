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
        $fields = [];

        if ($user->can('create_default_unavailabilities')) {
            $fields[] = Select::make('unavailability_type')
                ->label('Tipo de Indisponibilidade')
                ->options([
                    'personal' => '👤 Indisponibilidade Pessoal (Apenas para mim)',
                    'global' => '🌍 Indisponibilidade Global (Para TODOS os utilizadores)',
                    'shared' => '👥 Indisponibilidade Partilhada (Selecionar utilizadores específicos)',
                ])
                ->default('personal')
                ->reactive()
                ->helperText('Escolha o tipo de indisponibilidade');

            $fields[] = Hidden::make('user_id')
                ->default($user->id);

        }
        // 🔥 SE O UTILIZADOR TEM PERMISSÃO EDIT_ALL
        elseif ($user->can('edit_all:unavailabilities')) {
            $fields[] = Select::make('user_id')
                ->label('Associar a Utilizador')
                ->options([
                    null => '🌍 Indisponibilidade Global (Para todos)',
                    ...User::pluck('name', 'id')
                ])
                ->default(null)
                ->searchable()
                ->helperText('Selecione um utilizador específico ou "Global" para todos');
        }
        // 🔥 UTILIZADORES NORMAIS
        else {
            $fields[] = Hidden::make('user_id')
                ->default($user->id);
        }

        // 🔥 CAMPOS COMUNS A TODOS
        $fields = array_merge($fields, [
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
        ]);

        // No início do configure method, adiciona:
        \Log::info('DEBUG FORM - User permissions:', [
            'can_create_default' => $user->can('create_default_unavailabilities'),
            'can_edit_all' => $user->can('edit_all:unavailabilities'),
            'user_id' => $user->id
        ]);

        // E no campo associatedUsers, adiciona:
        $fields[] = Select::make('associatedUsers')
            ->label('Partilhar com utilizadores')
            ->options(User::pluck('name', 'id'))
            ->multiple()
            ->preload()
            ->searchable()
            ->visible(fn($get) => $get('unavailability_type') === 'shared')
            ->helperText('Selecione os utilizadores com quem quer quer partilhar esta indisponibilidade')
            ->dehydrated(true)  // ← TRUE para enviar dados
            ->required(fn($get) => $get('unavailability_type') === 'shared')
            ->afterStateUpdated(function ($state) {
                \Log::info('DEBUG FORM - associatedUsers selected:', ['users' => $state]);
            });

        return $schema->schema($fields);
    }
}