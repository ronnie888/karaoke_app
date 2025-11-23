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

    // Accessors
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration) {
            return '--:--';
        }

        $hours = floor($this->duration / 3600);
        $minutes = floor(($this->duration % 3600) / 60);
        $seconds = $this->duration % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
