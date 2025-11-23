<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatchHistory extends Model
{
    use HasFactory;

    protected $table = 'watch_history';

    protected $fillable = [
        'user_id',
        'video_id',
        'title',
        'thumbnail',
        'watch_duration',
        'watched_at',
    ];

    protected $casts = [
        'watch_duration' => 'integer',
        'watched_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeRecent($query)
    {
        return $query->orderBy('watched_at', 'desc');
    }

    // Helper Methods
    public static function record(int $userId, string $videoId, array $metadata, int $watchDuration = 0): void
    {
        static::create([
            'user_id' => $userId,
            'video_id' => $videoId,
            'title' => $metadata['title'],
            'thumbnail' => $metadata['thumbnail'] ?? null,
            'watch_duration' => $watchDuration,
            'watched_at' => now(),
        ]);
    }

    public static function clearForUser(int $userId): void
    {
        static::where('user_id', $userId)->delete();
    }
}
