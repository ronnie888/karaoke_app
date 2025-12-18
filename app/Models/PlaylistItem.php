<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaylistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'playlist_id',
        'song_id',
        'video_id',
        'title',
        'thumbnail',
        'duration',
        'position',
    ];

    protected $casts = [
        'duration' => 'integer',
        'position' => 'integer',
    ];

    // Relationships
    public function playlist(): BelongsTo
    {
        return $this->belongsTo(Playlist::class);
    }

    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }

    // Accessors
    public function getTitleAttribute($value): string
    {
        // If linked to a song, use song's title
        if ($this->song_id && $this->relationLoaded('song') && $this->song) {
            return $this->song->title;
        }
        return $value ?? '';
    }

    public function getArtistAttribute(): ?string
    {
        if ($this->song_id && $this->relationLoaded('song') && $this->song) {
            return $this->song->artist;
        }
        return null;
    }

    public function getFormattedDurationAttribute(): string
    {
        $duration = $this->duration;

        // If linked to a song, use song's duration
        if ($this->song_id && $this->relationLoaded('song') && $this->song) {
            $duration = $this->song->duration;
        }

        if (!$duration) {
            return '--:--';
        }

        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        $seconds = $duration % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function isLibrarySong(): bool
    {
        return $this->song_id !== null;
    }
}
