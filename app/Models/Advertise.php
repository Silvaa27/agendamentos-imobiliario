<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\AdvertiseField;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Advertise extends Model
{
    use HasFactory;

    protected $fillable = ['uuid', 'title', 'url', 'is_active', 'user_id'];
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

    /**
     * Utilizador que criou o advertise
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Utilizadores associados que podem gerir este advertise
     */
    public function associatedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'advertise_user')
            ->withTimestamps();
    }
}
