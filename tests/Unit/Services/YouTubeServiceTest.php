<?php

use App\DataTransferObjects\VideoSearchDTO;
use App\Services\YouTubeService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(Tests\TestCase::class);

beforeEach(function () {
    config(['youtube.api_key' => 'test-api-key']);
    config(['youtube.api_base' => 'https://www.googleapis.com/youtube/v3']);
    Cache::flush();
});

test('it throws exception when API key is not configured', function () {
    Config::set('youtube.api_key', '');

    expect(fn () => new YouTubeService())
        ->toThrow(\RuntimeException::class, 'YouTube API key is not configured');
});

test('it can search for videos', function () {
    Http::fake([
        '*/search*' => Http::response([
            'items' => [
                [
                    'id' => ['videoId' => 'test123'],
                    'snippet' => [
                        'title' => 'Test Video',
                        'description' => 'Test Description',
                        'thumbnails' => [
                            'high' => ['url' => 'https://example.com/thumb.jpg'],
                        ],
                        'channelId' => 'channel123',
                        'channelTitle' => 'Test Channel',
                        'publishedAt' => '2024-01-01T00:00:00Z',
                    ],
                ],
            ],
        ]),
    ]);

    $service = new YouTubeService();
    $searchDTO = new VideoSearchDTO(query: 'karaoke');

    $results = $service->search($searchDTO);

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe('test123')
        ->and($results->first()->title)->toBe('Test Video');
});

test('it caches search results', function () {
    Http::fake([
        '*/search*' => Http::response([
            'items' => [
                [
                    'id' => ['videoId' => 'test123'],
                    'snippet' => [
                        'title' => 'Test Video',
                        'description' => 'Test',
                        'thumbnails' => ['high' => ['url' => 'https://example.com/thumb.jpg']],
                        'channelId' => 'ch1',
                        'channelTitle' => 'Channel',
                        'publishedAt' => '2024-01-01T00:00:00Z',
                    ],
                ],
            ],
        ]),
    ]);

    $service = new YouTubeService();
    $searchDTO = new VideoSearchDTO(query: 'test');

    // First request - hits API
    $firstResults = $service->search($searchDTO);

    // Second request - should hit cache
    $secondResults = $service->search($searchDTO);

    expect($firstResults->toArray())->toEqual($secondResults->toArray());

    // Should only make one HTTP request
    Http::assertSentCount(1);
});

test('it can get video details', function () {
    Http::fake([
        '*/videos*' => Http::response([
            'items' => [
                [
                    'id' => 'test123',
                    'snippet' => [
                        'title' => 'Test Video',
                        'description' => 'Test',
                        'thumbnails' => ['high' => ['url' => 'https://example.com/thumb.jpg']],
                        'channelId' => 'ch1',
                        'channelTitle' => 'Channel',
                        'publishedAt' => '2024-01-01T00:00:00Z',
                    ],
                    'contentDetails' => [
                        'duration' => 'PT3M45S',
                    ],
                    'statistics' => [
                        'viewCount' => '1000',
                        'likeCount' => '50',
                    ],
                ],
            ],
        ]),
    ]);

    $service = new YouTubeService();
    $video = $service->getVideo('test123');

    expect($video)->not()->toBeNull()
        ->and($video->id)->toBe('test123')
        ->and($video->duration)->toBe(225) // 3m 45s = 225 seconds
        ->and($video->viewCount)->toBe(1000);
});

test('it returns null for non-existent video', function () {
    Http::fake([
        '*/videos*' => Http::response(['items' => []]),
    ]);

    $service = new YouTubeService();
    $video = $service->getVideo('nonexistent');

    expect($video)->toBeNull();
});

test('it can get popular videos', function () {
    Http::fake([
        '*/videos*' => Http::response([
            'items' => [
                [
                    'id' => 'pop1',
                    'snippet' => [
                        'title' => 'Popular Video',
                        'description' => 'Popular',
                        'thumbnails' => ['high' => ['url' => 'https://example.com/thumb.jpg']],
                        'channelId' => 'ch1',
                        'channelTitle' => 'Channel',
                        'publishedAt' => '2024-01-01T00:00:00Z',
                    ],
                ],
            ],
        ]),
    ]);

    $service = new YouTubeService();
    $results = $service->getPopular(10);

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe('pop1');
});

test('it handles API errors gracefully', function () {
    Http::fake([
        '*/search*' => Http::response(['error' => 'API Error'], 400),
    ]);

    Config::set('youtube.errors.throw_exceptions', false);

    $service = new YouTubeService();
    $searchDTO = new VideoSearchDTO(query: 'test');

    $results = $service->search($searchDTO);

    expect($results)->toBeEmpty();
});

test('it can check if API is configured', function () {
    $service = new YouTubeService();

    expect($service->isConfigured())->toBeTrue();

    Config::set('youtube.api_key', 'your_api_key_here');
    $service2 = new YouTubeService();

    expect($service2->isConfigured())->toBeFalse();
});

test('it can clear cache', function () {
    $service = new YouTubeService();

    expect($service->clearCache())->toBeTrue();
});
