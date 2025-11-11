<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;


class AdvertiseAnswer extends Model
{
    use HasFactory;

    protected $fillable = ['contact_id', 'advertise_id'];
    protected $table = 'advertise_answers';

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    // Relação com as respostas dos campos do formulário
    public function fieldAnswers(): HasMany
    {
        return $this->hasMany(AdvertiseFieldAnswer::class, 'advertise_answer_id', 'id');
    }

    public function advertise()
    {
        return $this->belongsTo(Advertise::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
