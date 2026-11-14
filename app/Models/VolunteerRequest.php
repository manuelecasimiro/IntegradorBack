<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VolunteerRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'volunteer_id',
        'event_id',
        'message',
        'meeting_point',
        'meeting_time',
        'status',
        'cancellation_reason',
        'ticket_code',
        'attendance_confirmed',
        'attendance_notes',
        'requested_at',
        'accepted_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'attendance_confirmed' => 'boolean',
            'requested_at' => 'datetime',
            'accepted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function volunteer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'volunteer_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'request_id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class, 'request_id');
    }
}
