<?php

use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['youtube.api_key' => 'test-api-key']);
    config(['youtube.api_base' => 'https://www.googleapis.com/youtube/v3']);
});

test('API video details returns JSON response', function () {
    Http::fake([
        '*/videos*' => Http::response([
            'items' => [
                [
                    'id' => 'dQw4w9WgXcQ',
                    'snippet' => [
                        'title' => 'Test Video',
                        'description' => 'Test Description',
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

    $response = $this->getJson('/api/v1/videos/dQw4w9WgXcQ');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'title',
                'description',
                'thumbnail',
                'channel',
                'stats',
                'duration',
                'published_at',
                'urls',
            ],
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => 'dQw4w9WgXcQ',
            ],
        ]);
});

test('API video details validates video ID format', function () {
    $response = $this->getJson('/api/v1/videos/invalid');

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('API video details returns 404 for non-existent video', function () {
    Http::fake([
        '*/videos*' => Http::response(['items' => []]),
    ]);

    $response = $this->getJson('/api/v1/videos/dQw4w9WgXcQ');

    $response->assertNotFound()
        ->assertJson([
            'success' => false,
            'message' => 'Video not found or unavailable.',
        ]);
});

test('API video details handles API errors', function () {
    Http::fake([
        '*/videos*' => Http::response(['error' => 'API Error'], 400),
    ]);

    config(['youtube.errors.throw_exceptions' => false]);

    $response = $this->getJson('/api/v1/videos/dQw4w9WgXcQ');

    $response->assertNotFound();
});

test('API popular videos returns JSON response', function () {
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

    $response = $this->getJson('/api/v1/popular');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'thumbnail',
                    'channel',
                    'published_at',
                    'urls',
                ],
            ],
        ])
        ->assertJson([
            'success' => true,
        ]);
});

test('API popular videos validates maxResults parameter', function () {
    $response = $this->getJson('/api/v1/popular?maxResults=100');

    $response->assertStatus(422)
        ->assertJsonValidationErrors('maxResults');
});

test('API popular videos validates regionCode parameter', function () {
    $response = $this->getJson('/api/v1/popular?regionCode=USA');

    $response->assertStatus(422)
        ->assertJsonValidationErrors('regionCode');
});

test('API popular videos accepts valid parameters', function () {
    Http::fake([
        '*/videos*' => Http::response(['items' => []]),
    ]);

    $response = $this->getJson('/api/v1/popular?maxResults=10&regionCode=US');

    $response->assertOk();
});

test('API popular videos handles API errors', function () {
    Http::fake([
        '*/videos*' => Http::response(['error' => 'API Error'], 400),
    ]);

    config(['youtube.errors.throw_exceptions' => false]);

    $response = $this->getJson('/api/v1/popular');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [],
        ]);
});

test('API popular videos returns empty array when no results', function () {
    Http::fake([
        '*/videos*' => Http::response(['items' => []]),
    ]);

    $response = $this->getJson('/api/v1/popular');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [],
        ]);
});
