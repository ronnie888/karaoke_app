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
        'song_id',
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
     * Get the associated song (for local karaoke files)
     */
    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }

    /**
     * Check if this is a local song (from CDN) vs YouTube
     */
    public function isLocalSong(): bool
    {
        return $this->song_id !== null;
    }

    /**
     * Check if this is a YouTube video
     */
    public function isYouTubeVideo(): bool
    {
        return $this->song_id === null && $this->video_id !== null;
    }

    /**
     * Get the stream URL for the video
     * For local songs: returns CDN URL or stream route
     * For YouTube: returns null (use IFrame API)
     */
    public function getStreamUrlAttribute(): ?string
    {
        if ($this->isLocalSong() && $this->song) {
            return $this->song->cdn_url ?? route('songs.stream', $this->song);
        }

        return null;
    }

    /**
     * Get the source type for the player
     */
    public function getSourceTypeAttribute(): string
    {
        return $this->isLocalSong() ? 'local' : 'youtube';
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
