<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\AdvertiseField;
use App\Models\AdvertiseAnswer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertiseFieldAnswer extends Model
{
    use HasFactory;
    protected $fillable = ['advertise_answer_id', 'advertise_field_id', 'answer'];
    protected $table = 'advertise_field_answers';
    protected $casts = [
        'answer' => 'json',
    ];

    public function advertise_field()
    {
        return $this->belongsTo(AdvertiseField::class);
    }
    public function advertise_answer()
    {
        return $this->belongsTo(AdvertiseAnswer::class);
    }
    
}
