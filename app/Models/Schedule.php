<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Schedule extends Model
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
            'id', // Foreign key on AdvertiseAnswer table
            'id', // Foreign key on Contact table
            'advertise_answer_id', // Local key on Schedule table
            'contact_id' // Local key on AdvertiseAnswer table
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
            'id', // Foreign key on AdvertiseAnswer table
            'id', // Foreign key on Advertise table
            'advertise_answer_id', // Local key on Schedule table
            'advertise_id' // Local key on AdvertiseAnswer table
        );
    }

    /**
     * Get the advertise title through advertise answer.
     */
    public function getAdvertiseTitleAttribute()
    {
        return $this->advertiseAnswer->advertise->title ?? null;
    }

    // ... mantém os outros métodos existentes

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
        return Carbon::parse($this->start_time)->format('H:i');
    }

    /**
     * Accessor para horário de fim formatado
     */
    public function getFormattedEndTimeAttribute()
    {
        return Carbon::parse($this->end_time)->format('H:i');
    }

    /**
     * Accessor para o período completo formatado
     */
    public function getFormattedPeriodAttribute()
    {
        return $this->formatted_start_time . ' - ' . $this->formatted_end_time;
    }

    /**
     * Mutator para start_time - garantir que seja um timestamp completo
     */
    public function setStartTimeAttribute($value)
    {
        if (is_string($value) && preg_match('/^\d{2}:\d{2}$/', $value)) {
            // Combina com a data atual se não houver date definido
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