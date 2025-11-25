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

        $this->record->loadForView();
    }


    public function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                Section::make('Respostas Recebidas')
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

                                    $reservationInfo = 'Sem reserva marcada';
                                    if ($record->schedules && $record->schedules->count() > 0) {
                                        $firstSchedule = $record->schedules->first();
                                        $startTime = $firstSchedule->start_time->format('H:i');
                                        $endTime = $firstSchedule->end_time->format('H:i');
                                        $dayName = $firstSchedule->date->translatedFormat('l');
                                        $date = $firstSchedule->date->format('d/m/Y');

                                        $reservationInfo = "{$dayName}, {$date} | {$startTime} - {$endTime}";
                                    }

                                    return " {$name} | {$reservationInfo}";
                                })
                                    ->schema([
                                        Section::make('Informações do Contacto')
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

                                        Section::make('Respostas do Formulário')
                                            ->schema(fn() => $this->fieldAnswerInfolist(new Schema($this)))
                                            ->visible(fn($record) => $record->fieldAnswers && $record->fieldAnswers->count() > 0)
                                            ->collapsible()
                                            ->collapsed(false)
                                            ->columnSpanFull(),

                                        Section::make('Horários Reservados')
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
                                    ->collapsed(true),
                            ])
                            ->grid(1)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn() => $this->record->advertise_answers->count() > 0)
                    ->collapsible()
                    ->columnSpanFull(),

                Section::make('Respostas Recebidas')
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

    public function fieldAnswerInfolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                RepeatableEntry::make('fieldAnswers')
                    ->label('')
                    ->schema([
                        Section::make('')
                            ->schema([
                                TextEntry::make('advertise_field.answer')
                                    ->label('')
                                    ->weight('bold')
                                    ->color('primary')
                                    ->size('lg')
                                    ->icon('heroicon-o-document-text')
                                    ->extraAttributes(['class' => 'text-lg font-semibold'])
                                    ->default('Campo')
                                    ->columnSpanFull(),

                                TextEntry::make('advertise_field.field_type')
                                    ->label('Tipo de Campo')
                                    ->formatStateUsing(function ($state) {
                                        if (empty($state))
                                            return 'Tipo não definido';

                                        return match ($state) {
                                            'TextInput' => 'Text',
                                            'NumberInput' => 'Number',
                                            'Select' => 'Select',
                                            'Radio' => 'Single Option',
                                            'Checkbox' => 'Checkbox',
                                            'Toggle' => 'Toggle',
                                            'CheckboxList' => 'Multiple Choice',
                                            'DatePicker' => 'Date',
                                            'TimePicker' => 'Time',
                                            'Slider' => 'Slider',
                                            'Textarea' => 'Text Area',
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
                                            return 'Sem resposta';
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
                    ])
                    ->grid(1)
                    ->extraAttributes(['class' => 'space-y-4 w-full']),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}