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
        $cancreateUnavailabilites = $user->can('create_default_unavailabilities');

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

        if ($cancreateUnavailabilites) {
            $otherUsers = User::where('id', '!=', $user->id)
                ->whereNotNull('email')
                ->pluck('name', 'id')
                ->filter()
                ->toArray();

            $options = [
                null => 'Indisponibilidade Default',
                $user->id => 'Indisponibilidade Pessoal',
            ];

            foreach ($otherUsers as $id => $name) {
                $options[$id] = $name;
            }

            $fields[] = Select::make('user_id')
                ->label('Tipo de Indisponibilidade')
                ->options($options)
                ->default($user->id)
                ->searchable()
                ->helperText('Escolha o tipo de indisponiblidade a criar')
                ->dehydrated(true)
                ->nullable();

        } else {
            $fields[] = Hidden::make('user_id')
                ->default($user->id)
                ->dehydrated(true);
        }
        return $schema->schema($fields);
    }
}