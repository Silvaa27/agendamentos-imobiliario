<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\AdvertiseAnswer;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'phone_number'];
    protected $table = 'contacts';

    public function advertise_answers(): HasMany
    {
        return $this->hasMany(AdvertiseAnswer::class);
    }

    public function advertiseAnswers()
    {
        return $this->hasMany(AdvertiseAnswer::class);
    }
}
