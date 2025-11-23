<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'video_id',
        'title',
        'thumbnail',
        'channel_title',
        'duration',
        'position',
        'is_playing',
    ];

    protected $casts = [
        'duration' => 'integer',
        'position' => 'integer',
        'is_playing' => 'boolean',
    ];

    /**
     * Get the session that owns this queue item
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(KaraokeSession::class, 'session_id');
    }

    /**
     * Get formatted duration (MM:SS)
     */
    public function getFormattedDuration(): string
    {
        if (!$this->duration) {
            return '0:00';
        }

        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Get formatted duration attribute
     */
    public function getFormattedDurationAttribute(): string
    {
        return $this->getFormattedDuration();
    }

    /**
     * Scope to get playing items
     */
    public function scopePlaying($query)
    {
        return $query->where('is_playing', true);
    }

    /**
     * Scope to get queued items (not playing)
     */
    public function scopeQueued($query)
    {
        return $query->where('is_playing', false);
    }

    /**
     * Scope to order by position
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }
}
