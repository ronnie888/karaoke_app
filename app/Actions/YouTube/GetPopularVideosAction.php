<?php

declare(strict_types=1);

namespace App\Actions\YouTube;

use App\DataTransferObjects\VideoResultDTO;
use App\Services\YouTubeService;
use Illuminate\Support\Collection;

final class GetPopularVideosAction
{
    public function __construct(
        private readonly YouTubeService $youtubeService
    ) {}

    /**
     * Execute the get popular videos action.
     *
     * @return Collection<int, VideoResultDTO>
     */
    public function execute(int $maxResults = 25, ?string $regionCode = null): Collection
    {
        if ($maxResults < 1 || $maxResults > 50) {
            throw new \InvalidArgumentException('Max results must be between 1 and 50');
        }

        return $this->youtubeService->getPopular($maxResults, $regionCode);
    }
}
