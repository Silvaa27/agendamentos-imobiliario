<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\CalendarEvent;

class Schedule extends Model implements Eventable
{
    use HasFactory;

    protected $fillable = [
        'advertise_answer_id',
        'date',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'contact_name',
        'contact_email',
        'contact_phone',
        'advertise_title',
        'formatted_date',
        'formatted_start_time',
        'formatted_end_time',
        'formatted_period',
    ];

    /**
     * Implementação CORRETA da interface Eventable
     */
    public function toCalendarEvent(): CalendarEvent
    {
        return CalendarEvent::make($this)
            ->title($this->getShortEventTitle()) // Título curto
            ->start($this->getEventStart())
            ->end($this->getEventEnd())
            ->backgroundColor($this->getEventColor())
            ->extendedProps([
                'contact' => $this->contact_name,
                'phone' => $this->contact_phone,
                'email' => $this->contact_email,
                'full_title' => $this->getEventTitle(), // Título completo para tooltip
            ]);
    }

    protected function getShortEventTitle(): string
    {
        $title = $this->advertiseAnswer->advertise->title ?? 'Agendamento';

        // Se for muito longo, encurta
        if (strlen($title) > 12) {
            return substr($title, 0, 10) . '...';
        }

        return $title;
    }

    /**
     * Título do evento para o calendário
     */
    protected function getEventTitle(): string
    {
        $advertiseTitle = $this->advertiseAnswer->advertise->title ?? 'Sem título';
        $contactName = $this->advertiseAnswer->contact->name ?? 'Sem nome';
        return "{$advertiseTitle} - {$contactName}";
    }



    /**
     * Data/hora de início para o calendário
     */
    protected function getEventStart(): string
    {
        return $this->date->format('Y-m-d') . 'T' . $this->start_time->format('H:i:s');
    }

    /**
     * Data/hora de fim para o calendário
     */
protected function getEventEnd(): string
{
    return $this->date->format('Y-m-d') . 'T' . $this->end_time->format('H:i:s');
}

    /**
     * Cor do evento
     */
    protected function getEventColor(): string
    {
        return '#3b82f6'; // Azul forte e profissional
    }

    // ... mantém todos os outros métodos existentes ...

    /**
     * Get the advertise answer that owns the schedule.
     */
    public function advertiseAnswer(): BelongsTo
    {
        return $this->belongsTo(AdvertiseAnswer::class);
    }

    /**
     * Get the contact through advertise answer.
     */
    public function contact(): HasOneThrough
    {
        return $this->hasOneThrough(
            Contact::class,
            AdvertiseAnswer::class,
            'id',
            'id',
            'advertise_answer_id',
            'contact_id'
        );
    }

    /**
     * Get the contact name through advertise answer.
     */
    public function getContactNameAttribute()
    {
        return $this->advertiseAnswer->contact->name ?? null;
    }

    /**
     * Get the contact email through advertise answer.
     */
    public function getContactEmailAttribute()
    {
        return $this->advertiseAnswer->contact->email ?? null;
    }

    /**
     * Get the contact phone through advertise answer.
     */
    public function getContactPhoneAttribute()
    {
        return $this->advertiseAnswer->contact->phone_number ?? null;
    }

    /**
     * Get the advertise through advertise answer.
     */
    public function advertise(): HasOneThrough
    {
        return $this->hasOneThrough(
            Advertise::class,
            AdvertiseAnswer::class,
            'id',
            'id',
            'advertise_answer_id',
            'advertise_id'
        );
    }

    /**
     * Get the advertise title through advertise answer.
     */
    public function getAdvertiseTitleAttribute()
    {
        return $this->advertiseAnswer->advertise->title ?? null;
    }

    /**
     * Get the user that owns the schedule.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para agendamentos do utilizador atual
     */
    public function scopeForCurrentUser($query)
    {
        return $query->where('user_id', auth()->id());
    }

    /**
     * Scope para agendamentos em uma data específica
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope para agendamentos futuros
     */
    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->format('Y-m-d'));
    }

    /**
     * Scope para agendamentos por horário
     */
    public function scopeForTime($query, $time)
    {
        return $query->whereTime('start_time', $time);
    }

    /**
     * Verificar se o horário está disponível
     */
    public static function isTimeAvailable($advertiseAnswerId, $date, $startTime)
    {
        return !self::where('advertise_answer_id', $advertiseAnswerId)
            ->forDate($date)
            ->forTime($startTime)
            ->exists();
    }

    /**
     * Accessor para data formatada
     */
    public function getFormattedDateAttribute()
    {
        $date = $this->date;

        if ($date instanceof Carbon) {
            return $date->format('d/m/Y');
        }

        if (empty($date)) {
            return null;
        }

        return Carbon::parse($date)->format('d/m/Y');
    }

    /**
     * Accessor para horário de início formatado
     */
    public function getFormattedStartTimeAttribute()
    {
        return $this->start_time ? Carbon::parse($this->start_time)->format('H:i') : null;
    }

    /**
     * Accessor para horário de fim formatado
     */
    public function getFormattedEndTimeAttribute()
    {
        return $this->end_time ? Carbon::parse($this->end_time)->format('H:i') : null;
    }

    /**
     * Accessor para o período completo formatado
     */
    public function getFormattedPeriodAttribute()
    {
        if ($this->formatted_start_time && $this->formatted_end_time) {
            return $this->formatted_start_time . ' - ' . $this->formatted_end_time;
        }
        return null;
    }

    /**
     * Mutator para start_time - garantir que seja um timestamp completo
     */
    public function setStartTimeAttribute($value)
    {
        if (is_string($value) && preg_match('/^\d{2}:\d{2}$/', $value)) {
            $date = $this->date ?? now()->format('Y-m-d');
            $this->attributes['start_time'] = Carbon::parse($date . ' ' . $value);
        } else {
            $this->attributes['start_time'] = $value;
        }
    }

    /**
     * Mutator para end_time - garantir que seja um timestamp completo
     */
    public function setEndTimeAttribute($value)
    {
        if (is_string($value) && preg_match('/^\d{2}:\d{2}$/', $value)) {
            $date = $this->date ?? now()->format('Y-m-d');
            $this->attributes['end_time'] = Carbon::parse($date . ' ' . $value);
        } else {
            $this->attributes['end_time'] = $value;
        }
    }
}