<?php

use App\DataTransferObjects\VideoResultDTO;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['youtube.api_key' => 'test-api-key']);
    config(['youtube.api_base' => 'https://www.googleapis.com/youtube/v3']);
});

test('home page renders successfully', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertViewIs('karaoke.index')
        ->assertViewHas('title')
        ->assertViewHas('description');
});

test('search page requires query parameter', function () {
    $response = $this->get('/search');

    $response->assertSessionHasErrors('q');
});

test('search page returns results for valid query', function () {
    Http::fake([
        '*/search*' => Http::response([
            'items' => [
                [
                    'id' => ['videoId' => 'test123'],
                    'snippet' => [
                        'title' => 'Test Karaoke Video',
                        'description' => 'A test video',
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

    $response = $this->get('/search?q=karaoke');

    $response->assertOk()
        ->assertViewIs('karaoke.search')
        ->assertViewHas('query', 'karaoke')
        ->assertViewHas('results')
        ->assertViewHas('total', 1);
});

test('search validates minimum query length', function () {
    $response = $this->get('/search?q=a');

    $response->assertSessionHasErrors('q');
});

test('search validates maximum query length', function () {
    $longQuery = str_repeat('a', 101);

    $response = $this->get('/search?q=' . $longQuery);

    $response->assertSessionHasErrors('q');
});

test('search validates maxResults parameter', function () {
    $response = $this->get('/search?q=test&maxResults=100');

    $response->assertSessionHasErrors('maxResults');
});

test('search validates order parameter', function () {
    $response = $this->get('/search?q=test&order=invalid');

    $response->assertSessionHasErrors('order');
});

test('watch page displays video player', function () {
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

    $response = $this->get('/watch/dQw4w9WgXcQ');

    $response->assertOk()
        ->assertViewIs('karaoke.watch')
        ->assertViewHas('video')
        ->assertViewHas('title');
});

test('watch page validates video ID format', function () {
    $response = $this->get('/watch/invalid');

    $response->assertRedirect(route('home'))
        ->assertSessionHas('error');
});

test('watch page handles non-existent video', function () {
    Http::fake([
        '*/videos*' => Http::response(['items' => []]),
    ]);

    $response = $this->get('/watch/dQw4w9WgXcQ');

    $response->assertRedirect(route('home'))
        ->assertSessionHas('error', 'Video not found or unavailable.');
});

test('search handles API errors gracefully', function () {
    Http::fake([
        '*/search*' => Http::response(['error' => 'API Error'], 400),
    ]);

    config(['youtube.errors.throw_exceptions' => false]);

    $response = $this->get('/search?q=test');

    $response->assertOk()
        ->assertViewIs('karaoke.search')
        ->assertViewHas('total', 0);
});

test('watch handles API errors gracefully', function () {
    Http::fake([
        '*/videos*' => Http::response(['error' => 'API Error'], 400),
    ]);

    config(['youtube.errors.throw_exceptions' => false]);

    $response = $this->get('/watch/test123');

    $response->assertRedirect(route('home'))
        ->assertSessionHas('error');
});
