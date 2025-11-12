<?php

namespace App\Filament\Resources\Advertises\Pages;

use App\Filament\Resources\Advertises\AdvertiseResource;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewAdvertise extends ViewRecord
{
    protected static string $resource = AdvertiseResource::class;

    public function mount($record): void
    {
        parent::mount($record);

        // 🔥 CARREGA AS RELAÇÕES NECESSÁRIAS
        $this->record->loadForView();
    }


    public function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                // 📝 Respostas Recebidas
                Section::make('📝 Respostas Recebidas')
                    ->description(function () {
                        $answersCount = $this->record->advertise_answers->count();
                        return "Total: {$answersCount} respostas";
                    })
                    ->schema([
                        RepeatableEntry::make('advertiseAnswersWithRelations')
                            ->label('')
                            ->schema([
                                Section::make(function ($record) {
                                    $name = $record->contact->name ?? 'N/A';

                                    $reservationInfo = '📅 Sem reserva marcada';
                                    if ($record->schedules && $record->schedules->count() > 0) {
                                        $firstSchedule = $record->schedules->first();
                                        $startTime = $firstSchedule->start_time->format('H:i');
                                        $endTime = $firstSchedule->end_time->format('H:i');
                                        $dayName = $firstSchedule->date->translatedFormat('l'); // Ex: "Segunda-feira"
                                        $date = $firstSchedule->date->format('d/m/Y');

                                        $reservationInfo = "{$dayName}, {$date} | {$startTime} - {$endTime}";
                                    }

                                    return "🗓️ {$name} | {$reservationInfo}";
                                })
                                    ->schema([
                                        Section::make('👤 Informações do Contacto')
                                            ->schema([
                                                TextEntry::make('contact.name')
                                                    ->label('Nome')
                                                    ->icon('heroicon-o-user')
                                                    ->default('N/A'),

                                                TextEntry::make('contact.email')
                                                    ->label('Email')
                                                    ->icon('heroicon-o-envelope')
                                                    ->default('N/A'),

                                                TextEntry::make('contact.phone_number')
                                                    ->label('Telefone')
                                                    ->icon('heroicon-o-phone')
                                                    ->default('N/A'),

                                                TextEntry::make('created_at')
                                                    ->label('Data de Submissão')
                                                    ->dateTime('d/m/Y H:i')
                                                    ->icon('heroicon-o-calendar')
                                                    ->default('N/A'),
                                            ])
                                            ->columns(2)
                                            ->collapsible()
                                            ->collapsed(false),

                                        // 📝 Respostas do Formulário
                                        Section::make('📝 Respostas do Formulário')
                                            ->schema([
                                                RepeatableEntry::make('fieldAnswers')
                                                    ->label('')
                                                    ->schema([
                                                        Section::make('')
                                                            ->schema([
                                                                // Cabeçalho com nome do campo
                                                                TextEntry::make('advertise_field.answer')
                                                                    ->label('')
                                                                    ->weight('bold')
                                                                    ->color('primary')
                                                                    ->size('lg')
                                                                    ->icon('heroicon-o-document-text')
                                                                    ->extraAttributes(['class' => 'text-lg font-semibold'])
                                                                    ->default('Campo')
                                                                    ->columnSpanFull(),

                                                                // Tipo do campo e resposta lado a lado
                                                                TextEntry::make('advertise_field.field_type')
                                                                    ->label('Tipo de Campo')
                                                                    ->formatStateUsing(function ($state) {
                                                                        if (empty($state))
                                                                            return 'Tipo não definido';

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
                                                                    ->extraAttributes(['class' => 'text-sm'])
                                                                    ->default('Tipo não definido'),

                                                                TextEntry::make('answer')
                                                                    ->label('Resposta')
                                                                    ->weight('bold')
                                                                    ->color('success')
                                                                    ->icon('heroicon-o-check-circle')
                                                                    ->formatStateUsing(function ($state) {
                                                                        if (empty($state)) {
                                                                            return '❌ Sem resposta';
                                                                        }

                                                                        if (is_string($state) && str_starts_with($state, '{"type"')) {
                                                                            $decoded = json_decode($state, true);
                                                                            if (json_last_error() === JSON_ERROR_NONE) {
                                                                                return $decoded['value'] ?? $decoded['text'] ?? $decoded['selected'] ?? 'Resposta não processável';
                                                                            }
                                                                        }

                                                                        return $state;
                                                                    })
                                                                    ->extraAttributes(['class' => 'text-base'])
                                                                    ->default('N/A'),
                                                            ])
                                                            ->columns(2)
                                                            ->extraAttributes([
                                                                'class' => 'border border-gray-200 rounded-lg p-6 bg-white shadow-sm hover:shadow-md transition-shadow duration-200 w-full'
                                                            ])
                                                    ])
                                                    ->grid(1)
                                                    ->extraAttributes(['class' => 'space-y-4 w-full']),
                                            ])
                                            ->visible(fn($record) => $record->fieldAnswers && $record->fieldAnswers->count() > 0)
                                            ->collapsible()
                                            ->collapsed(false)
                                            ->columnSpanFull(),

                                        // 🕐 Horários Reservados
                                        Section::make('🕐 Horários Reservados')
                                            ->schema([
                                                RepeatableEntry::make('schedules')
                                                    ->label('')
                                                    ->schema([
                                                        Section::make('')
                                                            ->schema([
                                                                TextEntry::make('date')
                                                                    ->label('Data da Reserva')
                                                                    ->date('d/m/Y')
                                                                    ->icon('heroicon-o-calendar')
                                                                    ->default('N/A'),

                                                                TextEntry::make('start_time')
                                                                    ->label('Hora de Início')
                                                                    ->time('H:i')
                                                                    ->icon('heroicon-o-clock')
                                                                    ->default('N/A'),

                                                                TextEntry::make('end_time')
                                                                    ->label('Hora de Fim')
                                                                    ->time('H:i')
                                                                    ->icon('heroicon-o-clock')
                                                                    ->default('N/A'),
                                                            ])
                                                            ->columns(3)
                                                            ->extraAttributes([
                                                                'class' => 'border border-gray-200 rounded-lg p-4 bg-white shadow-sm'
                                                            ])
                                                    ])
                                                    ->grid(1)
                                                    ->extraAttributes(['class' => 'space-y-3 w-full']),
                                            ])
                                            ->visible(fn($record) => $record->schedules && $record->schedules->count() > 0)
                                            ->collapsible()
                                            ->collapsed(false)
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsible()
                                    ->collapsed(true)
                                    ->extraAttributes([
                                        'class' => 'border-2 border-gray-300 rounded-xl bg-gray-50 mb-6'
                                    ]),
                            ])
                            ->grid(1)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn() => $this->record->advertise_answers->count() > 0)
                    ->collapsible()
                    ->columnSpanFull(),

                // Mensagem quando não há respostas
                Section::make('📝 Respostas Recebidas')
                    ->schema([
                        TextEntry::make('no_responses')
                            ->label('')
                            ->state('Nenhuma resposta recebida ainda')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->color('gray')
                            ->extraAttributes(['class' => 'text-center py-12 text-lg'])
                            ->columnSpanFull(),
                    ])
                    ->visible(fn() => $this->record->advertise_answers->count() === 0)
                    ->columnSpanFull(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}