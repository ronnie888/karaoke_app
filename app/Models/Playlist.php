<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Playlist extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_public',
        'views_count',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'views_count' => 'integer',
    ];

    protected $with = ['user'];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PlaylistItem::class)->with('song')->orderBy('position');
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Accessors
    public function getItemsCountAttribute(): int
    {
        return $this->items()->count();
    }

    public function getTotalDurationAttribute(): int
    {
        return $this->items()->sum('duration') ?? 0;
    }

    // Business Logic Methods
    public function addSong(int $songId): PlaylistItem
    {
        $song = Song::find($songId);

        return $this->items()->create([
            'song_id' => $songId,
            'title' => $song?->title ?? 'Unknown',
            'duration' => $song?->duration,
            'position' => ($this->items()->max('position') ?? -1) + 1,
        ]);
    }

    public function addVideo(string $videoId, array $metadata): void
    {
        $this->items()->create([
            'video_id' => $videoId,
            'title' => $metadata['title'],
            'thumbnail' => $metadata['thumbnail'] ?? null,
            'duration' => $metadata['duration'] ?? null,
            'position' => ($this->items()->max('position') ?? -1) + 1,
        ]);
    }

    public function removeItem(int $itemId): void
    {
        $this->items()->where('id', $itemId)->delete();

        // Reorder remaining items
        $this->items()->orderBy('position')->get()->each(function ($item, $index) {
            $item->update(['position' => $index]);
        });
    }

    public function removeVideo(int $itemId): void
    {
        $this->removeItem($itemId);
    }

    public function hasSong(int $songId): bool
    {
        return $this->items()->where('song_id', $songId)->exists();
    }
}
