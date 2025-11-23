<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KaraokeSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'is_active',
        'current_playing_id',
        'current_position',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'current_position' => 'integer',
    ];

    /**
     * Get the user that owns the session
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all queue items for this session
     */
    public function queueItems(): HasMany
    {
        return $this->hasMany(QueueItem::class, 'session_id')->orderBy('position');
    }

    /**
     * Get the currently playing item
     */
    public function currentItem(): ?QueueItem
    {
        return $this->queueItems()->where('is_playing', true)->first();
    }

    /**
     * Scope to get active sessions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get user's sessions
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get or create active session for user
     */
    public static function getOrCreateForUser(int $userId): self
    {
        return static::query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->first() ?? static::create([
                'user_id' => $userId,
                'is_active' => true,
            ]);
    }

    /**
     * Add video to queue
     */
    public function addVideo(array $videoData): QueueItem
    {
        $maxPosition = $this->queueItems()->max('position') ?? -1;

        return $this->queueItems()->create([
            'video_id' => $videoData['id'],
            'title' => $videoData['title'],
            'thumbnail' => $videoData['thumbnail'] ?? null,
            'channel_title' => $videoData['channel_title'] ?? null,
            'duration' => $videoData['duration'] ?? null,
            'position' => $maxPosition + 1,
        ]);
    }

    /**
     * Remove video from queue
     */
    public function removeVideo(int $queueItemId): bool
    {
        $item = $this->queueItems()->find($queueItemId);

        if (!$item) {
            return false;
        }

        $item->delete();

        // Reorder remaining items
        $this->reorderQueue();

        return true;
    }

    /**
     * Reorder queue items
     */
    public function reorderQueue(): void
    {
        $items = $this->queueItems()->get();

        foreach ($items as $index => $item) {
            $item->update(['position' => $index]);
        }
    }

    /**
     * Play next song in queue
     */
    public function playNext(): ?QueueItem
    {
        // Mark current as not playing
        $this->queueItems()->update(['is_playing' => false]);

        // Get next item
        $nextItem = $this->queueItems()
            ->where('position', '>', $this->current_position)
            ->orderBy('position')
            ->first();

        if ($nextItem) {
            $nextItem->update(['is_playing' => true]);
            $this->update([
                'current_playing_id' => $nextItem->video_id,
                'current_position' => $nextItem->position,
            ]);

            return $nextItem;
        }

        return null;
    }

    /**
     * Clear all queue items
     */
    public function clearQueue(): void
    {
        $this->queueItems()->delete();
        $this->update([
            'current_playing_id' => null,
            'current_position' => 0,
        ]);
    }
}
