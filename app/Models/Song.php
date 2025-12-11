<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Song extends Model
{
    use SoftDeletes, Searchable;

    protected $fillable = [
        'file_path',
        'file_name',
        'file_size',
        'file_hash',
        'title',
        'artist',
        'genre',
        'language',
        'duration',
        'width',
        'height',
        'video_codec',
        'audio_codec',
        'bitrate',
        'search_text',
        'tags',
        'play_count',
        'favorite_count',
        'last_played_at',
        'storage_driver',
        'cdn_url',
        'indexed_at',
        'index_status',
        'index_error',
    ];

    protected $casts = [
        'tags' => 'array',
        'duration' => 'integer',
        'play_count' => 'integer',
        'favorite_count' => 'integer',
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'bitrate' => 'integer',
        'indexed_at' => 'datetime',
        'last_played_at' => 'datetime',
    ];

    // Relationships
    public function queueItems(): HasMany
    {
        return $this->hasMany(QueueItem::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function watchHistory(): HasMany
    {
        return $this->hasMany(WatchHistory::class);
    }

    // Scout searchable configuration
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'artist' => $this->artist,
            'genre' => $this->genre,
            'search_text' => $this->search_text,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->index_status === 'completed';
    }

    // Scopes
    public function scopePopular($query, int $limit = 50)
    {
        return $query->orderBy('play_count', 'desc')->limit($limit);
    }

    public function scopeRecent($query, int $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function scopeByGenre($query, string $genre)
    {
        return $query->where('genre', $genre);
    }

    public function scopeByArtist($query, string $artist)
    {
        return $query->where('artist', 'LIKE', "%{$artist}%");
    }

    public function scopeIndexed($query)
    {
        return $query->where('index_status', 'completed');
    }

    public function scopeByLanguage($query, string $language)
    {
        return $query->where('language', $language);
    }

    // Accessors
    public function getStreamUrlAttribute(): string
    {
        return route('songs.stream', $this);
    }

    public function getFormattedDurationAttribute(): string
    {
        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        // For now, return null. Can add thumbnail generation later
        return null;
    }

    // Methods
    public function incrementPlayCount(): void
    {
        $this->increment('play_count');
        $this->update(['last_played_at' => now()]);
    }

    public function markAsIndexed(): void
    {
        $this->update([
            'index_status' => 'completed',
            'indexed_at' => now(),
            'index_error' => null,
        ]);
    }

    public function markAsIndexFailed(string $error): void
    {
        $this->update([
            'index_status' => 'failed',
            'index_error' => $error,
        ]);
    }
}
