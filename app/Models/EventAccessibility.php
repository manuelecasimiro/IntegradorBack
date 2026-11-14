<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventAccessibility extends Model
{
    use HasFactory;

    protected $table = 'event_accessibility';

    protected $fillable = [
        'event_id',
        'ramps',
        'accessible_restrooms',
        'reserved_spaces',
        'wheelchair_accessible',
        'sign_language_interpreter',
        'audio_description',
        'subtitles',
        'tactile_paving',
        'specialized_assistance',
        'additional_notes',
    ];

    protected function casts(): array
    {
        return [
            'ramps' => 'boolean',
            'accessible_restrooms' => 'boolean',
            'reserved_spaces' => 'boolean',
            'wheelchair_accessible' => 'boolean',
            'sign_language_interpreter' => 'boolean',
            'audio_description' => 'boolean',
            'subtitles' => 'boolean',
            'tactile_paving' => 'boolean',
            'specialized_assistance' => 'boolean',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
