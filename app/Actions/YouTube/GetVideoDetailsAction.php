<?php

declare(strict_types=1);

namespace App\Actions\YouTube;

use App\DataTransferObjects\VideoResultDTO;
use App\Services\YouTubeService;

final class GetVideoDetailsAction
{
    public function __construct(
        private readonly YouTubeService $youtubeService
    ) {}

    /**
     * Execute the get video details action.
     */
    public function execute(string $videoId): ?VideoResultDTO
    {
        if (empty($videoId)) {
            throw new \InvalidArgumentException('Video ID cannot be empty');
        }

        // Validate YouTube video ID format
        if (! $this->isValidYouTubeId($videoId)) {
            throw new \InvalidArgumentException('Invalid YouTube video ID format');
        }

        return $this->youtubeService->getVideo($videoId);
    }

    /**
     * Validate YouTube video ID format.
     */
    private function isValidYouTubeId(string $videoId): bool
    {
        // YouTube video IDs are 11 characters long and contain alphanumeric, dash, and underscore
        return preg_match('/^[a-zA-Z0-9_-]{11}$/', $videoId) === 1;
    }
}
