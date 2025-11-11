<?php
// app/Models/AdvertiseField.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdvertiseField extends Model
{
    use HasFactory;

    protected $fillable = [
        'advertise_id',
        'answer',
        'field_type',
        'is_required',
        'min_value',
        'max_value',
        'show_tooltip',
        'options',
        'step',
        'validation_rules',
        'validation_message'
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'show_tooltip' => 'boolean',
        'validation_rules' => 'array',
        'min_value' => 'float',
        'max_value' => 'float', 
        'step' => 'float',
    ];

    public function advertise()
    {
        return $this->belongsTo(Advertise::class);
    }
}