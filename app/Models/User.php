<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'cpf',
        'birth_date',
        'phone',
        'role',
        'profile_photo',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date' => 'date',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOrganizer(): bool
    {
        return $this->role === 'organizer';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function isVolunteer(): bool
    {
        return $this->role === 'volunteer';
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function volunteerProfile(): HasOne
    {
        return $this->hasOne(VolunteerProfile::class);
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(UserVerification::class);
    }

    public function organizedEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    public function volunteerRequestsAsClient(): HasMany
    {
        return $this->hasMany(VolunteerRequest::class, 'client_id');
    }

    public function volunteerRequestsAsVolunteer(): HasMany
    {
        return $this->hasMany(VolunteerRequest::class, 'volunteer_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function reviewsGiven(): HasMany
    {
        return $this->hasMany(Review::class, 'client_id');
    }

    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'volunteer_id');
    }
}
