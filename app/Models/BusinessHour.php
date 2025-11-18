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
        'user_id', // 👈 ADICIONA ESTE CAMPO
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

    /**
     * Relação com o advertise
     */
    public function advertise(): BelongsTo
    {
        return $this->belongsTo(Advertise::class, 'advertise_id');
    }

    /**
     * Relação com o utilizador
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function setUserIdAttribute($value)
    {
        \Log::info("🎯 MODEL - Valor recebido para user_id:", [
            'valor_original' => $value,
            'valor_final' => $value === '' ? null : $value
        ]);

        $this->attributes['user_id'] = $value === '' ? null : $value;
    }

    /**
     * Scope para business hours do utilizador atual
     */
    public function scopeForCurrentUser($query)
    {
        return $query->where('user_id', auth()->id());
    }

    /**
     * Scope para templates (business hours sem advertise_id)
     */
    public function scopeTemplates($query)
    {
        return $query->whereNull('advertise_id');
    }

    /**
     * Scope para business hours específicos de um advertise
     */
    public function scopeForAdvertise($query, $advertiseId)
    {
        return $query->where('advertise_id', $advertiseId);
    }

    /**
     * Gerar business hours a partir dos templates do utilizador
     */
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


    // No modelo BusinessHour.php
    public function associatedUsers()
    {
        return $this->belongsToMany(User::class, 'business_hour_user');
    }

}