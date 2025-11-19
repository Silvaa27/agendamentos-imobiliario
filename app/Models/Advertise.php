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

    public function advertise_fields(): HasMany
    {
        return $this->hasMany(AdvertiseField::class);
    }

    public function advertise_answers(): HasMany
    {
        return $this->hasMany(AdvertiseAnswer::class);
    }

    /**
     * Respostas com eager loading das relações necessárias
     */
    public function advertiseAnswersWithRelations(): HasMany
    {
        return $this->hasMany(AdvertiseAnswer::class)
            ->with(['contact', 'fieldAnswers.advertise_field', 'schedules']);
    }

    /**
     * Contactos através das respostas
     */
    public function contacts()
    {
        return $this->hasManyThrough(Contact::class, AdvertiseAnswer::class, 'advertise_id', 'id', 'id', 'contact_id');
    }

    /**
     * FieldAnswers através das respostas
     */
    public function allFieldAnswers()
    {
        return $this->hasManyThrough(FieldAnswer::class, AdvertiseAnswer::class, 'advertise_id', 'advertise_answer_id');
    }

    /**
     * Horários através das respostas
     */
    public function allSchedules()
    {
        return $this->hasManyThrough(Schedule::class, AdvertiseAnswer::class, 'advertise_id', 'advertise_answer_id');
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
            $advertise->advertise_answers()->delete();
            $advertise->advertise_fields()->delete();
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


    /**
     * Carrega todas as relações necessárias para a view
     */
    public function loadForView()
    {
        return $this->load([
            'advertiseAnswersWithRelations',
            'advertiseAnswersWithRelations.contact',
            'advertiseAnswersWithRelations.fieldAnswers.advertise_field',
            'advertiseAnswersWithRelations.schedules',
            'user',
            'associatedUsers'
        ]);
    }

    /**
     * Getter para o número total de respostas
     */
    public function getTotalAnswersAttribute()
    {
        return $this->advertise_answers()->count();
    }
}