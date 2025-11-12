<?php

namespace App\Filament\Resources\BusinessHours\Schemas;

use App\Models\BusinessHour;
use App\Models\User;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class BusinessHourForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();

        $fields = [
            Select::make('day')
                ->options(BusinessHour::DAYS)
                ->required()
                ->label('Dia da Semana'),

            TimePicker::make('start_time')
                ->seconds(false)
                ->required()
                ->label('Hora de Início'),

            TimePicker::make('end_time')
                ->seconds(false)
                ->required()
                ->after('start_time')
                ->label('Hora de Fim'),
        ];

        // 🔥 SE O UTILIZADOR TEM PERMISSÃO PARA EDITAR TODOS OS HORÁRIOS
        if ($user->can('edit_all:businesshours')) {
            array_unshift(
                $fields,
                Select::make('user_id')
                    ->label('Associar a Utilizador')
                    ->options([
                        '' => '🌍 Horário Default (Para utilizadores que não tenham horários)',
                        ...User::pluck('name', 'id')
                    ])
                    ->default('')
                    ->searchable()
                    ->helperText('Selecione um utilizador específico ou "Horário Default" para todos')
                    ->afterStateUpdated(function ($state) {
                        \Log::info("DEBUG FORM - User ID selecionado: " . $state);
                    })
                    ->dehydrated(true)
            );
        }
        // 🔥 SE O UTILIZADOR TEM PERMISSÃO PARA CRIAR HORÁRIOS DEFAULT
        elseif ($user->can('create_default:businesshours')) {
            array_unshift(
                $fields,
                Select::make('user_id')
                    ->label('Tipo de Horário')
                    ->options([
                        '' => '🌍 Horário Default (Para utilizadores que não tenham horários)',
                        $user->id => '👤 Horário Pessoal (Apenas para mim)',
                    ])
                    ->default($user->id)
                    ->helperText('Escolha se quer criar um horário para todos ou apenas para si')
                    ->afterStateUpdated(function ($state) {
                        \Log::info("DEBUG FORM - Tipo de horário selecionado: " . $state);
                    })
                    ->dehydrated(true)
            );
        }
        // 🔥 UTILIZADORES NORMAIS - APENAS CRIAM PARA SI MESMOS
        else {
            array_unshift(
                $fields,
                Hidden::make('user_id')
                    ->default($user->id)
                    ->dehydrated(true)
            );
        }

        return $schema->schema($fields);
    }
}