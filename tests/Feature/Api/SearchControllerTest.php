<?php

use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['youtube.api_key' => 'test-api-key']);
    config(['youtube.api_base' => 'https://www.googleapis.com/youtube/v3']);
});

test('API search returns JSON response', function () {
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

    $response = $this->getJson('/api/v1/search?q=karaoke');

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

test('API search requires query parameter', function () {
    $response = $this->getJson('/api/v1/search');

    $response->assertStatus(422)
        ->assertJsonValidationErrors('q');
});

test('API search validates query length', function () {
    $response = $this->getJson('/api/v1/search?q=a');

    $response->assertStatus(422)
        ->assertJsonValidationErrors('q');
});

test('API search validates maxResults parameter', function () {
    $response = $this->getJson('/api/v1/search?q=test&maxResults=100');

    $response->assertStatus(422)
        ->assertJsonValidationErrors('maxResults');
});

test('API search validates order parameter', function () {
    $response = $this->getJson('/api/v1/search?q=test&order=invalid');

    $response->assertStatus(422)
        ->assertJsonValidationErrors('order');
});

test('API search validates regionCode parameter', function () {
    $response = $this->getJson('/api/v1/search?q=test&regionCode=USA');

    $response->assertStatus(422)
        ->assertJsonValidationErrors('regionCode');
});

test('API search validates safeSearch parameter', function () {
    $response = $this->getJson('/api/v1/search?q=test&safeSearch=invalid');

    $response->assertStatus(422)
        ->assertJsonValidationErrors('safeSearch');
});

test('API search handles API errors', function () {
    Http::fake([
        '*/search*' => Http::response(['error' => 'API Error'], 400),
    ]);

    config(['youtube.errors.throw_exceptions' => false]);

    $response = $this->getJson('/api/v1/search?q=test');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [],
        ]);
});

test('API search returns empty array when no results', function () {
    Http::fake([
        '*/search*' => Http::response(['items' => []]),
    ]);

    $response = $this->getJson('/api/v1/search?q=nonexistent');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [],
        ]);
});

test('API search accepts all valid parameters', function () {
    Http::fake([
        '*/search*' => Http::response(['items' => []]),
    ]);

    $response = $this->getJson('/api/v1/search?' . http_build_query([
        'q' => 'karaoke',
        'maxResults' => 10,
        'order' => 'viewCount',
        'regionCode' => 'US',
        'safeSearch' => 'strict',
        'videoCategoryId' => '10',
        'videoDefinition' => 'high',
    ]));

    $response->assertOk();
});
