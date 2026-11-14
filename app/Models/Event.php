<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'name',
        'description',
        'location',
        'address',
        'city',
        'state',
        'event_date',
        'start_time',
        'end_time',
        'ticket_information',
        'banner_url',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
        ];
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function accessibility(): HasOne
    {
        return $this->hasOne(EventAccessibility::class);
    }

    public function volunteerRequests(): HasMany
    {
        return $this->hasMany(VolunteerRequest::class);
    }

    public function eventVolunteers(): HasMany
    {
        return $this->hasMany(EventVolunteer::class);
    }
}
