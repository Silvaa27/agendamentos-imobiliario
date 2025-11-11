<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\AdvertiseField;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Advertise extends Model
{
    use HasFactory;

    protected $fillable = ['uuid', 'title', 'url', 'is_active'];
    protected $table = 'advertises';
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function advertise_fields()
    {
        return $this->hasMany(AdvertiseField::class);
    }
    public function advertise_answers()
    {
        return $this->hasMany(AdvertiseAnswer::class);
    }

    public function businessHours(): HasMany
    {
        return $this->hasMany(BusinessHour::class);
    }

    protected static function boot()
    {
        parent::boot();

        // Quando um advertise for deletado, deletar os business_hours relacionados
        static::deleting(function ($advertise) {
            $advertise->businessHours()->delete();
        });
    }
}
