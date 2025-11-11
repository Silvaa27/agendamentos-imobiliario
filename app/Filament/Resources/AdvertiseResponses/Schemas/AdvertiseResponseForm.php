<?php

namespace App\Filament\Resources\AdvertiseResponses\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdvertiseResponseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 👤 Informações do Contacto
                Section::make('👤 Informações do Contacto')
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextEntry::make('contact.name')
                                    ->label('Nome')
                                    ->icon('heroicon-o-user'),

                                TextEntry::make('contact.email')
                                    ->label('Email')
                                    ->icon('heroicon-o-envelope'),

                                TextEntry::make('contact.phone_number')
                                    ->label('Telefone')
                                    ->icon('heroicon-o-phone'),

                                TextEntry::make('created_at')
                                    ->label('Data de Submissão')
                                    ->dateTime('d/m/Y H:i')
                                    ->icon('heroicon-o-calendar'),
                            ]),
                    ])
                    ->collapsible(),

                // 📢 Detalhes do Anúncio
                Section::make('📢 Detalhes do Anúncio')
                    ->components([
                        TextEntry::make('advertise.title')
                            ->label('Título')
                            ->icon('heroicon-o-megaphone'),

                        TextEntry::make('advertise.url')
                            ->label('URL')
                            ->icon('heroicon-o-link')
                            ->url(fn($record) => $record->advertise->url)
                            ->openUrlInNewTab()
                            ->visible(fn($record) => !empty($record->advertise->url)),
                    ])
                    ->collapsible(),

                // 📝 Respostas do Formulário
                Section::make('📝 Respostas do Formulário')
                    ->components([
                        RepeatableEntry::make('fieldAnswers')
                            ->label('')
                            ->components([
                                // Card para cada resposta
                                Section::make('')
                                    ->components([
                                        Grid::make(1)
                                            ->components([
                                                // Cabeçalho com nome do campo
                                                TextEntry::make('advertise_field.answer')
                                                    ->label('')
                                                    ->weight('bold')
                                                    ->color('primary')
                                                    ->size('lg')
                                                    ->icon('heroicon-o-document-text')
                                                    ->extraAttributes(['class' => 'text-lg font-semibold']),

                                                // Grid com tipo e resposta
                                                Grid::make(2)
                                                    ->components([
                                                        // Tipo do campo
                                                        TextEntry::make('advertise_field.field_type')
                                                            ->label('Tipo de Campo')
                                                            ->formatStateUsing(function ($state) {
                                                                return match ($state) {
                                                                    'TextInput' => '📝 Texto',
                                                                    'NumberInput' => '🔢 Número',
                                                                    'Select' => '📋 Lista Suspensa',
                                                                    'Radio' => '🔘 Opção Única',
                                                                    'Checkbox' => '☑️ Checkbox',
                                                                    'Toggle' => '⚡ Toggle',
                                                                    'CheckboxList' => '✅ Múltipla Escolha',
                                                                    'DatePicker' => '📅 Data',
                                                                    'TimePicker' => '⏰ Hora',
                                                                    'Slider' => '🎚️ Slider',
                                                                    'Textarea' => '📄 Área de Texto',
                                                                    default => $state,
                                                                };
                                                            })
                                                            ->icon('heroicon-o-cog')
                                                            ->color('gray')
                                                            ->extraAttributes(['class' => 'text-sm']),

                                                        // Resposta em destaque
                                                        TextEntry::make('answer')
                                                            ->label('Resposta')
                                                            ->weight('bold')
                                                            ->color('success')
                                                            ->icon('heroicon-o-check-circle')
                                                            ->formatStateUsing(function ($state) {
                                                                if (empty($state)) {
                                                                    return '❌ Sem resposta';
                                                                }

                                                                if (is_string($state) && !json_validate($state)) {
                                                                    return $state;
                                                                }

                                                                if (is_string($state)) {
                                                                    $decoded = json_decode($state, true);
                                                                    if (json_last_error() === JSON_ERROR_NONE) {
                                                                        $state = $decoded;
                                                                    }
                                                                }

                                                                if (is_array($state)) {
                                                                    return $state['value'] ??
                                                                        $state['text'] ??
                                                                        $state['label'] ??
                                                                        $state['selected'] ??
                                                                        (is_array($state) ? implode(', ', $state) : $state);
                                                                }

                                                                return is_string($state) ? $state : 'Resposta não processável';
                                                            })
                                                            ->extraAttributes(['class' => 'text-base']),
                                                    ])
                                                    ->extraAttributes(['class' => 'mt-2']),
                                            ])
                                    ])
                                    ->extraAttributes([
                                        'class' => 'border border-gray-200 rounded-lg p-4 bg-white shadow-sm hover:shadow-md transition-shadow duration-200'
                                    ])
                            ])
                            ->grid(1)
                            ->extraAttributes(['class' => 'space-y-4']),
                    ])
                    ->visible(fn($record) => $record->fieldAnswers->count() > 0)
                    ->collapsible(),

                // 🕐 Horários Reservados
                Section::make('🕐 Horários Reservados')
                    ->components([
                        RepeatableEntry::make('schedules')
                            ->label('')
                            ->components([
                                Grid::make(3)
                                    ->components([
                                        TextEntry::make('date')
                                            ->label('Data')
                                            ->date('d/m/Y')
                                            ->icon('heroicon-o-calendar'),

                                        TextEntry::make('start_time')
                                            ->label('Hora de Início')
                                            ->time('H:i')
                                            ->icon('heroicon-o-clock'),

                                        TextEntry::make('end_time')
                                            ->label('Hora de Fim')
                                            ->time('H:i')
                                            ->icon('heroicon-o-clock'),
                                    ]),
                            ])
                    ])
                    ->visible(fn($record) => $record->schedules->count() > 0)
                    ->collapsible(),
            ]);
    }
}