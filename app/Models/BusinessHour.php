<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'day', // Note: your migration uses 'days' (plural)
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

    /*public function getDayNameAttribute(): string
    {
        return self::DAYS[$this->days] ?? $this->days;
    }*/
}
