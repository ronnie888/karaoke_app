<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

readonly class VideoSearchDTO
{
    /**
     * Create a new VideoSearchDTO instance.
     *
     * @param  string  $query  The search query
     * @param  int  $maxResults  Maximum number of results to return
     * @param  string  $order  Result ordering (relevance, date, rating, title, viewCount)
     * @param  string|null  $regionCode  ISO 3166-1 alpha-2 country code
     * @param  string  $safeSearch  Safe search mode (none, moderate, strict)
     * @param  string|null  $videoCategoryId  Video category filter
     * @param  string|null  $videoDefinition  Video quality (any, high, standard)
     * @param  string|null  $videoEmbeddable  Filter embeddable videos (any, true)
     * @param  string|null  $pageToken  Pagination token
     */
    public function __construct(
        public string $query,
        public int $maxResults = 25,
        public string $order = 'relevance',
        public ?string $regionCode = null,
        public string $safeSearch = 'moderate',
        public ?string $videoCategoryId = null,
        public ?string $videoDefinition = null,
        public ?string $videoEmbeddable = 'true',
        public ?string $pageToken = null,
    ) {
        $this->validate();
    }

    /**
     * Validate the DTO properties.
     */
    private function validate(): void
    {
        if (empty($this->query)) {
            throw new \InvalidArgumentException('Search query cannot be empty');
        }

        if ($this->maxResults < 1 || $this->maxResults > 50) {
            throw new \InvalidArgumentException('Max results must be between 1 and 50');
        }

        $validOrders = ['relevance', 'date', 'rating', 'title', 'viewCount'];
        if (! in_array($this->order, $validOrders, true)) {
            throw new \InvalidArgumentException('Order must be one of: ' . implode(', ', $validOrders));
        }

        $validSafeSearch = ['none', 'moderate', 'strict'];
        if (! in_array($this->safeSearch, $validSafeSearch, true)) {
            throw new \InvalidArgumentException('Safe search must be one of: ' . implode(', ', $validSafeSearch));
        }

        if ($this->videoEmbeddable !== null) {
            $validEmbeddable = ['any', 'true'];
            if (! in_array($this->videoEmbeddable, $validEmbeddable, true)) {
                throw new \InvalidArgumentException('videoEmbeddable must be one of: ' . implode(', ', $validEmbeddable));
            }
        }
    }

    /**
     * Convert DTO to array for API request.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'q' => $this->query,
            'maxResults' => $this->maxResults,
            'order' => $this->order,
            'regionCode' => $this->regionCode ?? config('youtube.search.region_code'),
            'safeSearch' => $this->safeSearch,
            'videoCategoryId' => $this->videoCategoryId,
            'videoDefinition' => $this->videoDefinition,
            'videoEmbeddable' => $this->videoEmbeddable,
            'pageToken' => $this->pageToken,
            'type' => 'video', // Always search for videos only (required when using videoEmbeddable)
            'part' => 'snippet', // Required part parameter
        ], fn ($value) => $value !== null);
    }

    /**
     * Create DTO from request data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            query: $data['q'] ?? $data['query'] ?? '',
            maxResults: (int) ($data['maxResults'] ?? config('youtube.search.max_results', 25)),
            order: $data['order'] ?? config('youtube.search.default_order', 'relevance'),
            regionCode: $data['regionCode'] ?? null,
            safeSearch: $data['safeSearch'] ?? config('youtube.search.safe_search', 'moderate'),
            videoCategoryId: $data['videoCategoryId'] ?? null,
            videoDefinition: $data['videoDefinition'] ?? null,
            videoEmbeddable: $data['videoEmbeddable'] ?? config('youtube.search.video_embeddable', 'true'),
            pageToken: $data['pageToken'] ?? null,
        );
    }
}
