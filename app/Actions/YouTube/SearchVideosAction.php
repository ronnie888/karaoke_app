<?php

declare(strict_types=1);

namespace App\Actions\YouTube;

use App\DataTransferObjects\VideoResultDTO;
use App\DataTransferObjects\VideoSearchDTO;
use App\Services\YouTubeService;
use Illuminate\Support\Collection;

final class SearchVideosAction
{
    public function __construct(
        private readonly YouTubeService $youtubeService
    ) {}

    /**
     * Execute the video search action.
     *
     * @return Collection<int, VideoResultDTO>
     */
    public function execute(VideoSearchDTO $searchDTO): Collection
    {
        return $this->youtubeService->search($searchDTO);
    }

    /**
     * Execute search from array data.
     *
     * @param  array<string, mixed>  $data
     * @return Collection<int, VideoResultDTO>
     */
    public function executeFromArray(array $data): Collection
    {
        $searchDTO = VideoSearchDTO::fromArray($data);

        return $this->execute($searchDTO);
    }
}
