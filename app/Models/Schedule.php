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

    public function toCalendarEvent(): CalendarEvent
    {
        return CalendarEvent::make($this)
            ->title($this->getShortEventTitle())
            ->start($this->getEventStart())
            ->end($this->getEventEnd())
            ->backgroundColor($this->getEventColor())
            ->extendedProps([
                'contact' => $this->contact_name,
                'phone' => $this->contact_phone,
                'email' => $this->contact_email,
                'full_title' => $this->getEventTitle(),
            ]);
    }

    protected function getShortEventTitle(): string
    {
        $title = $this->advertiseAnswer->advertise->title ?? 'Agendamento';
        $contact = $this->advertiseAnswer->contact->name ?? 'Contacto';

        $grupo = $title . ' (' . $contact . ')';

        if (strlen($grupo) > 20) {
            return substr($grupo, 0, 17) . '...';
        }

        return $grupo;
    }
    protected function getEventTitle(): string
    {
        $advertiseTitle = $this->advertiseAnswer->advertise->title ?? 'Sem título';
        $contactName = $this->advertiseAnswer->contact->name ?? 'Sem nome';
        return "{$advertiseTitle} - {$contactName}";
    }

    protected function getEventStart(): string
    {
        return $this->date->format('Y-m-d') . 'T' . $this->start_time->format('H:i:s');
    }

    protected function getEventEnd(): string
    {
        return $this->date->format('Y-m-d') . 'T' . $this->end_time->format('H:i:s');
    }

    protected function getEventColor(): string
    {
        return '#3b82f6';
    }

    public function advertiseAnswer(): BelongsTo
    {
        return $this->belongsTo(AdvertiseAnswer::class);
    }

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

    public function getContactNameAttribute()
    {
        return $this->advertiseAnswer->contact->name ?? null;
    }

    public function getContactEmailAttribute()
    {
        return $this->advertiseAnswer->contact->email ?? null;
    }

    public function getContactPhoneAttribute()
    {
        return $this->advertiseAnswer->contact->phone_number ?? null;
    }

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

    public function getAdvertiseTitleAttribute()
    {
        return $this->advertiseAnswer->advertise->title ?? null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForCurrentUser($query)
    {
        return $query->where('user_id', auth()->id());
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->format('Y-m-d'));
    }

    public function scopeForTime($query, $time)
    {
        return $query->whereTime('start_time', $time);
    }

    public static function isTimeAvailable($advertiseAnswerId, $date, $startTime)
    {
        return !self::where('advertise_answer_id', $advertiseAnswerId)
            ->forDate($date)
            ->forTime($startTime)
            ->exists();
    }

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

    public function getFormattedStartTimeAttribute()
    {
        return $this->start_time ? Carbon::parse($this->start_time)->format('H:i') : null;
    }

    public function getFormattedEndTimeAttribute()
    {
        return $this->end_time ? Carbon::parse($this->end_time)->format('H:i') : null;
    }

    public function getFormattedPeriodAttribute()
    {
        if ($this->formatted_start_time && $this->formatted_end_time) {
            return $this->formatted_start_time . ' - ' . $this->formatted_end_time;
        }
        return null;
    }

    public function setStartTimeAttribute($value)
    {
        if (is_string($value) && preg_match('/^\d{2}:\d{2}$/', $value)) {
            $date = $this->date ?? now()->format('Y-m-d');
            $this->attributes['start_time'] = Carbon::parse($date . ' ' . $value);
        } else {
            $this->attributes['start_time'] = $value;
        }
    }
    
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