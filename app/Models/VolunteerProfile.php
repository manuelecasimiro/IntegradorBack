<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VolunteerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'biography',
        'linkedin_url',
        'instagram_url',
        'availability_status',
        'experience',
        'resume_url',
        'skills',
        'rating_avg',
        'completed_services_count',
        'pendencies_notes',
    ];

    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'rating_avg' => 'decimal:2',
            'completed_services_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
