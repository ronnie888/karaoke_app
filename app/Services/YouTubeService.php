<?php

declare(strict_types=1);

namespace App\Services;

use App\DataTransferObjects\VideoResultDTO;
use App\DataTransferObjects\VideoSearchDTO;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Psr\SimpleCache\InvalidArgumentException;

class YouTubeService
{
    private PendingRequest $client;

    private string $apiKey;

    private string $apiBase;

    /**
     * Create a new YouTubeService instance.
     */
    public function __construct()
    {
        $this->apiKey = config('youtube.api_key', '');
        $this->apiBase = config('youtube.api_base', 'https://www.googleapis.com/youtube/v3');

        if (empty($this->apiKey)) {
            throw new \RuntimeException('YouTube API key is not configured. Please set YOUTUBE_API_KEY in your .env file.');
        }

        $client = Http::baseUrl($this->apiBase)
            ->timeout(30)
            ->retry(3, 1000)
            ->withHeaders([
                'Accept' => 'application/json',
            ]);

        // For local development on Windows, disable SSL verification if needed
        if (config('app.env') === 'local' && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $client = $client->withOptions(['verify' => false]);
        }

        $this->client = $client;
    }

    /**
     * Search for videos on YouTube.
     *
     * @return Collection<int, VideoResultDTO>
     *
     * @throws RequestException
     * @throws InvalidArgumentException
     */
    public function search(VideoSearchDTO $searchDTO): Collection
    {
        $cacheKey = $this->getCacheKey('search', $searchDTO->query, $searchDTO->toArray());

        if (config('youtube.cache.enabled', true)) {
            $cached = $this->getCacheStore()->get($cacheKey);

            if ($cached !== null) {
                Log::info('YouTube search cache hit', ['query' => $searchDTO->query]);

                return $cached;
            }
        }

        Log::info('YouTube API search request', ['query' => $searchDTO->query]);

        $params = array_merge(
            $searchDTO->toArray(),
            ['key' => $this->apiKey]
        );

        $startTime = microtime(true);

        try {
            $response = $this->client->get('/search', $params);

            $response->throw();

            $duration = (microtime(true) - $startTime) * 1000;

            if ($duration > config('youtube.logging.slow_query_threshold', 1000)) {
                Log::warning('Slow YouTube API query', [
                    'query' => $searchDTO->query,
                    'duration_ms' => $duration,
                ]);
            }

            $data = $response->json();

            $results = $this->transformSearchResults($data['items'] ?? []);

            if (config('youtube.cache.enabled', true)) {
                $this->getCacheStore()->put(
                    $cacheKey,
                    $results,
                    config('youtube.cache.search_ttl', 3600)
                );
            }

            return $results;
        } catch (RequestException $e) {
            Log::error('YouTube API search failed', [
                'query' => $searchDTO->query,
                'error' => $e->getMessage(),
                'response' => $e->response->json(),
            ]);

            if (config('youtube.errors.throw_exceptions', false)) {
                throw $e;
            }

            return collect();
        }
    }

    /**
     * Get detailed information about a specific video.
     *
     * @throws RequestException
     * @throws InvalidArgumentException
     */
    public function getVideo(string $videoId): ?VideoResultDTO
    {
        $cacheKey = $this->getCacheKey('video', $videoId);

        if (config('youtube.cache.enabled', true)) {
            $cached = $this->getCacheStore()->get($cacheKey);

            if ($cached !== null) {
                Log::info('YouTube video cache hit', ['video_id' => $videoId]);

                return $cached;
            }
        }

        Log::info('YouTube API video request', ['video_id' => $videoId]);

        try {
            $response = $this->client->get('/videos', [
                'id' => $videoId,
                'part' => 'snippet,contentDetails,statistics',
                'key' => $this->apiKey,
            ]);

            $response->throw();

            $data = $response->json();

            if (empty($data['items'])) {
                Log::warning('YouTube video not found', ['video_id' => $videoId]);

                return null;
            }

            $videoData = $data['items'][0];
            $videoData['id'] = ['videoId' => $videoId]; // Normalize ID format

            $result = VideoResultDTO::fromYouTubeResponse($videoData);

            if (config('youtube.cache.enabled', true)) {
                $this->getCacheStore()->put(
                    $cacheKey,
                    $result,
                    config('youtube.cache.video_ttl', 86400)
                );
            }

            return $result;
        } catch (RequestException $e) {
            Log::error('YouTube API video fetch failed', [
                'video_id' => $videoId,
                'error' => $e->getMessage(),
            ]);

            if (config('youtube.errors.throw_exceptions', false)) {
                throw $e;
            }

            return null;
        }
    }

    /**
     * Get videos related to a specific video.
     * Since relatedToVideoId is deprecated, we extract keywords from the video title
     * and search for similar karaoke videos.
     *
     * @return Collection<int, VideoResultDTO>
     *
     * @throws RequestException
     * @throws InvalidArgumentException
     */
    public function getRelatedVideos(string $videoId, int $maxResults = 3): Collection
    {
        $cacheKey = $this->getCacheKey('related', $videoId, ['maxResults' => $maxResults]);

        if (config('youtube.cache.enabled', true)) {
            $cached = $this->getCacheStore()->get($cacheKey);

            if ($cached !== null) {
                Log::info('YouTube related videos cache hit', ['video_id' => $videoId]);

                return $cached;
            }
        }

        Log::info('YouTube API related videos request', ['video_id' => $videoId]);

        try {
            // First, get the current video's details to extract keywords
            $currentVideo = $this->getVideo($videoId);

            if (! $currentVideo) {
                Log::warning('Could not fetch video details for related videos', ['video_id' => $videoId]);

                return collect();
            }

            // Extract search keywords from the title
            $searchQuery = $this->extractSearchKeywordsFromTitle($currentVideo->title);

            // Search for similar karaoke videos
            $response = $this->client->get('/search', [
                'part' => 'snippet',
                'q' => $searchQuery,
                'type' => 'video',
                'maxResults' => min($maxResults + 5, 50), // Get a few extra to filter out the current video
                'key' => $this->apiKey,
            ]);

            $response->throw();

            $data = $response->json();

            $results = $this->transformSearchResults($data['items'] ?? [])
                ->filter(fn ($video) => $video->id !== $videoId) // Exclude the current video
                ->take($maxResults); // Limit to requested amount

            if (config('youtube.cache.enabled', true)) {
                $this->getCacheStore()->put(
                    $cacheKey,
                    $results,
                    config('youtube.cache.search_ttl', 3600)
                );
            }

            return $results;
        } catch (RequestException $e) {
            Log::error('YouTube API related videos fetch failed', [
                'video_id' => $videoId,
                'error' => $e->getMessage(),
            ]);

            if (config('youtube.errors.throw_exceptions', false)) {
                throw $e;
            }

            return collect();
        }
    }

    /**
     * Extract search keywords from a video title for finding similar content.
     * Removes common karaoke-specific words and extracts artist/song info.
     */
    private function extractSearchKeywordsFromTitle(string $title): string
    {
        // Convert to lowercase for easier processing
        $normalized = strtolower($title);

        // Remove common karaoke-related terms
        $removeTerms = [
            'karaoke',
            'with lyrics',
            'lyrics',
            'instrumental',
            'official',
            'video',
            'hd',
            'hq',
            '(lower key)',
            '(higher key)',
            '(original key)',
            'backing track',
            'sing along',
            'lyric video',
        ];

        foreach ($removeTerms as $term) {
            $normalized = str_replace($term, '', $normalized);
        }

        // Remove content within parentheses and brackets
        $normalized = preg_replace('/\([^)]*\)/', '', $normalized);
        $normalized = preg_replace('/\[[^\]]*\]/', '', $normalized);

        // Clean up extra spaces
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        $normalized = trim($normalized);

        // Add "karaoke" back to ensure we get karaoke results
        return $normalized . ' karaoke';
    }

    /**
     * Get popular/trending videos.
     *
     * @return Collection<int, VideoResultDTO>
     *
     * @throws RequestException
     * @throws InvalidArgumentException
     */
    public function getPopular(int $maxResults = 25, ?string $regionCode = null): Collection
    {
        $region = $regionCode ?? config('youtube.search.region_code', 'US');
        $cacheKey = $this->getCacheKey('popular', $region, ['maxResults' => $maxResults]);

        if (config('youtube.cache.enabled', true)) {
            $cached = $this->getCacheStore()->get($cacheKey);

            if ($cached !== null) {
                Log::info('YouTube popular cache hit', ['region' => $region]);

                return $cached;
            }
        }

        Log::info('YouTube API popular request', ['region' => $region]);

        try {
            $response = $this->client->get('/videos', [
                'part' => 'snippet,contentDetails,statistics',
                'chart' => 'mostPopular',
                'regionCode' => $region,
                'maxResults' => min($maxResults, 50),
                'videoCategoryId' => config('youtube.search.karaoke_category', '10'),
                'key' => $this->apiKey,
            ]);

            $response->throw();

            $data = $response->json();

            $results = $this->transformVideoResults($data['items'] ?? []);

            if (config('youtube.cache.enabled', true)) {
                $this->getCacheStore()->put(
                    $cacheKey,
                    $results,
                    config('youtube.cache.popular_ttl', 7200)
                );
            }

            return $results;
        } catch (RequestException $e) {
            Log::error('YouTube API popular fetch failed', [
                'region' => $region,
                'error' => $e->getMessage(),
            ]);

            if (config('youtube.errors.throw_exceptions', false)) {
                throw $e;
            }

            return collect();
        }
    }

    /**
     * Clear YouTube cache.
     */
    public function clearCache(?string $pattern = null): bool
    {
        try {
            if ($pattern === null) {
                // For drivers that support tags, flush the tagged cache
                // For file driver, this will flush the entire cache
                $driver = config('youtube.cache.driver');
                if (in_array($driver, ['redis', 'memcached'], true)) {
                    return $this->getCacheStore()->flush();
                }

                // For file driver, we cannot flush tagged cache, so just return true
                // Note: Individual cache items will expire based on TTL
                return true;
            }

            // Clear specific pattern
            $prefix = config('youtube.cache.prefix', 'youtube');
            $cacheKey = "{$prefix}:{$pattern}";

            return Cache::driver(config('youtube.cache.driver'))
                ->forget($cacheKey);
        } catch (\Exception $e) {
            Log::error('Failed to clear YouTube cache', [
                'pattern' => $pattern,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Transform search results to DTO collection.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return Collection<int, VideoResultDTO>
     */
    private function transformSearchResults(array $items): Collection
    {
        return collect($items)
            ->filter(function (array $item): bool {
                // Only include video results (filter out channels, playlists, etc.)
                if (isset($item['id']['kind'])) {
                    return $item['id']['kind'] === 'youtube#video';
                }

                // If no kind specified, check if we have a valid video ID
                return isset($item['id']['videoId']) || (isset($item['id']) && is_string($item['id']) && ! empty($item['id']));
            })
            ->map(
                fn (array $item): VideoResultDTO => VideoResultDTO::fromYouTubeResponse($item)
            )
            ->values(); // Re-index the collection
    }

    /**
     * Transform video results to DTO collection.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return Collection<int, VideoResultDTO>
     */
    private function transformVideoResults(array $items): Collection
    {
        return collect($items)->map(function (array $item): VideoResultDTO {
            // Normalize ID format for videos.list response
            if (! isset($item['id']['videoId'])) {
                $item['id'] = ['videoId' => $item['id']];
            }

            return VideoResultDTO::fromYouTubeResponse($item);
        });
    }

    /**
     * Generate cache key for YouTube API responses.
     *
     * @param  array<string, mixed>  $params
     */
    private function getCacheKey(string $type, string $identifier, array $params = []): string
    {
        $prefix = config('youtube.cache.prefix', 'youtube');
        $hash = md5(json_encode($params) ?: '');

        return "{$prefix}:{$type}:{$identifier}:{$hash}";
    }

    /**
     * Get cache store with or without tags based on driver support.
     *
     * @return \Illuminate\Contracts\Cache\Repository
     */
    private function getCacheStore()
    {
        $driver = config('youtube.cache.driver');
        $store = Cache::driver($driver);

        // Only use tags if the driver supports them (Redis, Memcached)
        // File and database drivers don't support tags
        if (in_array($driver, ['redis', 'memcached'], true)) {
            return $store->tags(config('youtube.cache.tags', []));
        }

        return $store;
    }

    /**
     * Check if YouTube API is configured.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->apiKey) && $this->apiKey !== 'your_api_key_here';
    }

    /**
     * Test YouTube API connection.
     */
    public function testConnection(): bool
    {
        try {
            $response = $this->client->get('/search', [
                'q' => 'test',
                'maxResults' => 1,
                'part' => 'snippet',
                'type' => 'video',
                'key' => $this->apiKey,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('YouTube API connection test failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
