<?php

use App\Actions\YouTube\SearchVideosAction;
use App\DataTransferObjects\VideoSearchDTO;
use App\Services\YouTubeService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

uses(Tests\TestCase::class);

beforeEach(function () {
    Config::set('youtube.api_key', 'test-key');

    Http::fake([
        '*/search*' => Http::response([
            'items' => [
                [
                    'id' => ['videoId' => 'abc123'],
                    'snippet' => [
                        'title' => 'Karaoke Video',
                        'description' => 'Test',
                        'thumbnails' => ['high' => ['url' => 'https://example.com/thumb.jpg']],
                        'channelId' => 'channel1',
                        'channelTitle' => 'Test Channel',
                        'publishedAt' => '2024-01-01T00:00:00Z',
                    ],
                ],
            ],
        ]),
    ]);
});

test('it can execute video search', function () {
    $service = new YouTubeService();
    $action = new SearchVideosAction($service);

    $searchDTO = new VideoSearchDTO(query: 'karaoke');
    $results = $action->execute($searchDTO);

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe('abc123')
        ->and($results->first()->title)->toBe('Karaoke Video');
});

test('it can execute search from array', function () {
    $service = new YouTubeService();
    $action = new SearchVideosAction($service);

    $results = $action->executeFromArray([
        'q' => 'karaoke',
        'maxResults' => 10,
    ]);

    expect($results)->toHaveCount(1);
});
