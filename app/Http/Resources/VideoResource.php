<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DataTransferObjects\VideoResultDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin VideoResultDTO
 */
class VideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var VideoResultDTO $video */
        $video = $this->resource;

        return [
            'id' => $video->id,
            'title' => $video->title,
            'description' => $video->description,
            'thumbnail' => $video->thumbnailUrl,
            'channel' => [
                'id' => $video->channelId,
                'title' => $video->channelTitle,
            ],
            'published_at' => $video->publishedAt->toIso8601String(),
            'duration' => $video->duration,
            'duration_formatted' => $video->getFormattedDuration(),
            'stats' => [
                'views' => $video->viewCount,
                'likes' => $video->likeCount,
            ],
            'urls' => [
                'watch' => route('watch', ['videoId' => $video->id]),
                'youtube' => $video->getWatchUrl(),
                'embed' => $video->getEmbedUrl(),
            ],
            'category_id' => $video->categoryId,
        ];
    }
}
