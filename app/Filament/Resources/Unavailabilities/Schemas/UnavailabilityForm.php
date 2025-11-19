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
                    'personal' => 'Indisponibilidade Pessoal (Apenas para mim)',
                    'global' => 'Indisponibilidade Global (Para TODOS os utilizadores)',
                    'other_user' => 'Indisponibilidade para Outro Utilizador',
                ])
                ->default('personal')
                ->reactive()
                ->helperText('Escolha o tipo de indisponibilidade');

            $fields[] = Hidden::make('user_id')
                ->default($user->id);

        } elseif ($user->can('edit_all_unavailabilities')) {
            $fields[] = Select::make('user_id')
                ->label('Associar a Utilizador')
                ->options([
                    null => 'Indisponibilidade Global (Para todos)',
                    ...User::pluck('name', 'id')
                ])
                ->default(null)
                ->searchable()
                ->helperText('Selecione um utilizador específico ou "Global" para todos');
        } else {
            $fields[] = Hidden::make('user_id')
                ->default($user->id);
        }

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

        $fields[] = Select::make('other_user_id')
            ->label('Utilizador')
            ->options(User::where('id', '!=', $user->id)->pluck('name', 'id'))
            ->searchable()
            ->visible(fn($get) => $get('unavailability_type') === 'other_user')
            ->helperText('Selecione o utilizador para quem está a criar a indisponibilidade')
            ->required(fn($get) => $get('unavailability_type') === 'other_user');

        $fields[] = Select::make('associatedUsers')
            ->label('Partilhar com utilizadores')
            ->options(User::pluck('name', 'id'))
            ->multiple()
            ->preload()
            ->searchable()
            ->helperText('Selecione os utilizadores com quem quer partilhar esta indisponibilidade (incluindo você se desejar)');

        return $schema->schema($fields);
    }
}