<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BusinessHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'day',
        'start_time',
        'end_time',
        'advertise_id',
    ];

    const DAYS = [
        'monday' => 'Segunda-feira',
        'tuesday' => 'Terça-feira',
        'wednesday' => 'Quarta-feira',
        'thursday' => 'Quinta-feira',
        'friday' => 'Sexta-feira',
        'saturday' => 'Sábado',
        'sunday' => 'Domingo',
    ];
    
    public function advertise(): BelongsTo
    {
        return $this->belongsTo(Advertise::class, 'advertise_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function setUserIdAttribute($value)
    {

        $this->attributes['user_id'] = $value === '' ? null : $value;
    }

    public function scopeForCurrentUser($query)
    {
        return $query->where('user_id', auth()->id());
    }

    public function scopeTemplates($query)
    {
        return $query->whereNull('advertise_id');
    }

    public function scopeForAdvertise($query, $advertiseId)
    {
        return $query->where('advertise_id', $advertiseId);
    }

    public static function getUserTemplatesForAdvertise()
    {
        return self::forCurrentUser()
            ->templates()
            ->get()
            ->map(function ($template) {
                return [
                    'day' => $template->day,
                    'start_time' => $template->start_time,
                    'end_time' => $template->end_time,
                ];
            })
            ->toArray();
    }

    public function associatedUsers()
    {
        return $this->belongsToMany(User::class, 'business_hour_user');
    }

}