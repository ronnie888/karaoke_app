<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use Carbon\Carbon;

readonly class VideoResultDTO
{
    /**
     * Create a new VideoResultDTO instance.
     *
     * @param  string  $id  YouTube video ID
     * @param  string  $title  Video title
     * @param  string  $description  Video description
     * @param  string  $thumbnailUrl  Thumbnail URL
     * @param  string  $channelId  Channel ID
     * @param  string  $channelTitle  Channel name
     * @param  Carbon  $publishedAt  Publication date
     * @param  int|null  $duration  Video duration in seconds
     * @param  int|null  $viewCount  View count
     * @param  int|null  $likeCount  Like count
     * @param  string|null  $categoryId  Video category ID
     */
    public function __construct(
        public string $id,
        public string $title,
        public string $description,
        public string $thumbnailUrl,
        public string $channelId,
        public string $channelTitle,
        public Carbon $publishedAt,
        public ?int $duration = null,
        public ?int $viewCount = null,
        public ?int $likeCount = null,
        public ?string $categoryId = null,
    ) {}

    /**
     * Create DTO from YouTube API response item.
     *
     * @param  array<string, mixed>  $item
     */
    public static function fromYouTubeResponse(array $item): self
    {
        $snippet = $item['snippet'] ?? [];
        $statistics = $item['statistics'] ?? [];
        $contentDetails = $item['contentDetails'] ?? [];

        // Handle different ID formats from YouTube API
        $videoId = '';
        if (isset($item['id'])) {
            $videoId = is_array($item['id'])
                ? ($item['id']['videoId'] ?? '')
                : $item['id'];
        }

        return new self(
            id: $videoId,
            title: $snippet['title'] ?? 'Untitled',
            description: $snippet['description'] ?? '',
            thumbnailUrl: $snippet['thumbnails']['high']['url']
                ?? $snippet['thumbnails']['medium']['url']
                ?? $snippet['thumbnails']['default']['url']
                ?? '',
            channelId: $snippet['channelId'] ?? '',
            channelTitle: $snippet['channelTitle'] ?? 'Unknown',
            publishedAt: isset($snippet['publishedAt'])
                ? Carbon::parse($snippet['publishedAt'])
                : now(),
            duration: isset($contentDetails['duration'])
                ? self::parseDuration($contentDetails['duration'])
                : null,
            viewCount: isset($statistics['viewCount'])
                ? (int) $statistics['viewCount']
                : null,
            likeCount: isset($statistics['likeCount'])
                ? (int) $statistics['likeCount']
                : null,
            categoryId: $snippet['categoryId'] ?? null,
        );
    }

    /**
     * Convert ISO 8601 duration to seconds.
     */
    private static function parseDuration(string $duration): int
    {
        preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?/', $duration, $matches);

        $hours = (int) ($matches[1] ?? 0);
        $minutes = (int) ($matches[2] ?? 0);
        $seconds = (int) ($matches[3] ?? 0);

        return ($hours * 3600) + ($minutes * 60) + $seconds;
    }

    /**
     * Get formatted duration (HH:MM:SS or MM:SS).
     */
    public function getFormattedDuration(): ?string
    {
        if ($this->duration === null) {
            return null;
        }

        $hours = floor($this->duration / 3600);
        $minutes = floor(($this->duration % 3600) / 60);
        $seconds = $this->duration % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Get YouTube watch URL.
     */
    public function getWatchUrl(): string
    {
        return "https://www.youtube.com/watch?v={$this->id}";
    }

    /**
     * Get YouTube embed URL.
     */
    public function getEmbedUrl(): string
    {
        return "https://www.youtube.com/embed/{$this->id}";
    }

    /**
     * Convert DTO to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'thumbnail' => $this->thumbnailUrl,
            'channel' => [
                'id' => $this->channelId,
                'title' => $this->channelTitle,
            ],
            'published_at' => $this->publishedAt->toIso8601String(),
            'duration' => $this->duration,
            'duration_formatted' => $this->getFormattedDuration(),
            'stats' => [
                'views' => $this->viewCount,
                'likes' => $this->likeCount,
            ],
            'urls' => [
                'watch' => $this->getWatchUrl(),
                'embed' => $this->getEmbedUrl(),
            ],
            'category_id' => $this->categoryId,
        ];
    }
}
