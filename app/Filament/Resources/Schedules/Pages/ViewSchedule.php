<?php

namespace App\Filament\Resources\Schedules\Pages;

use App\Filament\Resources\Advertises\Pages\ViewAdvertise;
use App\Filament\Resources\Schedules\ScheduleResource;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewSchedule extends ViewRecord
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                Section::make('Informações do Agendamento')
                    ->description('Detalhes completos do agendamento marcado')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('advertiseAnswer.advertise.title')
                                    ->label('Anúncio')
                                    ->formatStateUsing(fn($state) => $state ?? 'N/A')
                                    ->weight('font-medium')
                                    ->color('primary'),

                                TextEntry::make('advertiseAnswer.contact.name')
                                    ->label('Cliente')
                                    ->formatStateUsing(fn($state) => $state ?? 'N/A')
                                    ->weight('font-medium'),

                                TextEntry::make('date')
                                    ->label('Data')
                                    ->date('d/m/Y')
                                    ->weight('font-medium')
                                    ->color('primary'),

                                TextEntry::make('formatted_period')
                                    ->label('Horário')
                                    ->formatStateUsing(function ($state, $record) {
                                        if (!$record)
                                            return 'N/A';

                                        $startTime = $record->formatted_start_time ?? 'N/A';
                                        $endTime = $record->formatted_end_time ?? 'N/A';

                                        return "{$startTime} - {$endTime}";
                                    })
                                    ->weight('font-medium')
                                    ->color('success'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Detalhes do Anúncio')
                    ->description('Informações relacionadas ao anúncio')
                    ->icon('heroicon-o-megaphone')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('advertiseAnswer.advertise.user.name')
                                    ->label('Proprietário do Anúncio')
                                    ->formatStateUsing(fn($state) => $state ?? 'N/A'),
                                TextEntry::make('advertiseAnswer.advertise.url')
                                    ->label('URL')
                                    ->getStateUsing(function ($record) {
                                        $url = $record->advertiseAnswer?->advertise?->url;

                                        if (filled($url)) {
                                            return 'Abrir URL';
                                        }

                                        return 'N/A';
                                    })
                                    ->url(fn($record) => $record->advertiseAnswer?->advertise?->url)
                                    ->openUrlInNewTab()
                                    ->color('primary'),

                                TextEntry::make('advertiseAnswer.advertise.created_at')
                                    ->label('Anúncio Criado em')
                                    ->dateTime('d/m/Y H:i')
                                    ->formatStateUsing(fn($state) => $state ? $state->format('d/m/Y H:i') : 'N/A'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Informações de Contacto')
                    ->description('Dados do cliente que respondeu ao anúncio')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('advertiseAnswer.contact.email')
                                    ->label('Email')
                                    ->formatStateUsing(fn($state) => $state ?? 'N/A')
                                    ->icon('heroicon-o-envelope'),

                                TextEntry::make('advertiseAnswer.contact.phone')
                                    ->label('Telefone')
                                    ->formatStateUsing(fn($state) => $state ?? 'N/A')
                                    ->icon('heroicon-o-phone'),

                                TextEntry::make('advertiseAnswer.created_at')
                                    ->label('Resposta Criada em')
                                    ->dateTime('d/m/Y H:i')
                                    ->formatStateUsing(fn($state) => $state ? $state->format('d/m/Y H:i') : 'N/A')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Metadados do Agendamento')
                    ->description('Informações técnicas do sistema')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Criado em')
                                    ->dateTime('d/m/Y H:i')
                                    ->formatStateUsing(fn($state) => $state ? $state->format('d/m/Y H:i') : 'N/A'),

                                TextEntry::make('updated_at')
                                    ->label('Atualizado em')
                                    ->dateTime('d/m/Y H:i')
                                    ->formatStateUsing(fn($state) => $state ? $state->format('d/m/Y H:i') : 'N/A'),

                                TextEntry::make('id')
                                    ->label('ID do Agendamento')
                                    ->badge()
                                    ->color('gray')
                                    ->copyable()
                                    ->copyMessage('ID copiado!'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
                Section::make('aawdad')
                ->relationship('advertiseAnswer')
                ->columnSpanFull()
                ->schema(fn($livewire)=> (new ViewAdvertise())->fieldAnswerInfolist(new Schema($livewire))),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($data['start_time']) && isset($data['end_time'])) {
            $startTime = \Carbon\Carbon::parse($data['start_time'])->format('H:i');
            $endTime = \Carbon\Carbon::parse($data['end_time'])->format('H:i');
            $data['formatted_period'] = "{$startTime} - {$endTime}";
        }

        return $data;
    }
}
