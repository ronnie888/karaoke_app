<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'video_id',
        'title',
        'thumbnail',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Helper Methods
    public static function isFavorited(int $userId, string $videoId): bool
    {
        return static::where('user_id', $userId)
            ->where('video_id', $videoId)
            ->exists();
    }

    public static function toggle(int $userId, string $videoId, array $metadata): bool
    {
        $favorite = static::where('user_id', $userId)
            ->where('video_id', $videoId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return false; // Removed from favorites
        }

        static::create([
            'user_id' => $userId,
            'video_id' => $videoId,
            'title' => $metadata['title'],
            'thumbnail' => $metadata['thumbnail'] ?? null,
        ]);

        return true; // Added to favorites
    }
}
