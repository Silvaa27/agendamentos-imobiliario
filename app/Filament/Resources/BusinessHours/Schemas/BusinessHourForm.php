<?php

namespace App\Filament\Resources\BusinessHours\Schemas;

use App\Models\BusinessHour;
use App\Models\User;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class BusinessHourForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();
        $canCreateDefault = $user->can('create_default_businesshours');

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

        if ($canCreateDefault) {
            $otherUsers = User::where('id', '!=', $user->id)
                ->whereNotNull('email')
                ->pluck('name', 'id')
                ->filter()
                ->toArray();

            // CORREÇÃO: Usar array_merge em vez de spread operator
            $options = [
                null => 'Horário Default (user_id = NULL)',
                $user->id => 'Horário Pessoal (apenas para mim)',
            ];

            // Merge manual para preservar as chaves corretas
            foreach ($otherUsers as $id => $name) {
                $options[$id] = $name;
            }

            $fields[] = Select::make('user_id')
                ->label('Tipo de Horário')
                ->options($options) // ← Array corrigido
                ->default($user->id)
                ->searchable()
                ->helperText('Escolha o tipo de horário a criar')
                ->dehydrated(true)
                ->nullable();

        } else {
            $fields[] = Hidden::make('user_id')
                ->default($user->id)
                ->dehydrated(true);
        }

        return $schema->schema($fields);
    }

    public static function forAdvertiseRepeater(): array
    {
        return [
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

            Hidden::make('user_id')
                ->default(auth()->id())
                ->dehydrated(true),
        ];
    }
}