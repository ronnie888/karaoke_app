<?php

namespace Tests\Feature\Integration;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Complete User Journey Integration Tests
 *
 * Tests the entire flow from registration to video playback
 */
class UserJourneyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: New user can register, search, and create first playlist
     */
    public function test_new_user_complete_journey(): void
    {
        // Mock YouTube API
        Http::fake([
            'youtube.googleapis.com/youtube/v3/search*' => Http::response([
                'items' => [
                    [
                        'id' => ['videoId' => 'test_video_1'],
                        'snippet' => [
                            'title' => 'Test Karaoke Song',
                            'description' => 'Test Description',
                            'channelId' => 'test_channel',
                            'channelTitle' => 'Test Channel',
                            'publishedAt' => '2024-01-01T00:00:00Z',
                            'thumbnails' => [
                                'high' => ['url' => 'https://example.com/thumb.jpg'],
                            ],
                        ],
                    ],
                ],
            ]),
            'youtube.googleapis.com/youtube/v3/videos*' => Http::response([
                'items' => [
                    [
                        'id' => 'test_video_1',
                        'snippet' => [
                            'title' => 'Test Karaoke Song',
                            'description' => 'Test Description',
                            'channelId' => 'test_channel',
                            'channelTitle' => 'Test Channel',
                            'publishedAt' => '2024-01-01T00:00:00Z',
                            'thumbnails' => [
                                'high' => ['url' => 'https://example.com/thumb.jpg'],
                            ],
                        ],
                        'contentDetails' => ['duration' => 'PT3M45S'],
                        'statistics' => [
                            'viewCount' => '1000',
                            'likeCount' => '100',
                        ],
                    ],
                ],
            ]),
        ]);

        // 1. Visit home page
        $response = $this->get('/');
        $response->assertOk();

        // 2. Register new user
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertRedirect('/');

        // Verify user created
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);

        // 3. Search for video (as authenticated user)
        $response = $this->actingAs($user)->get('/search?q=karaoke');
        $response->assertOk();
        $response->assertSee('Test Karaoke Song');

        // 4. Create playlist
        $response = $this->actingAs($user)->post('/playlists', [
            'name' => 'My First Playlist',
            'description' => 'Test playlist description',
            'is_public' => false,
        ]);
        $response->assertRedirect();

        // Verify playlist created
        $this->assertDatabaseHas('playlists', [
            'user_id' => $user->id,
            'name' => 'My First Playlist',
        ]);

        // 5. Add video to playlist
        $playlist = $user->playlists()->first();
        $response = $this->actingAs($user)->post("/playlists/{$playlist->id}/add", [
            'video_id' => 'test_video_1',
            'title' => 'Test Karaoke Song',
            'thumbnail' => 'https://example.com/thumb.jpg',
            'duration' => 225,
        ]);
        $response->assertRedirect();

        // Verify video added to playlist
        $this->assertDatabaseHas('playlist_items', [
            'playlist_id' => $playlist->id,
            'video_id' => 'test_video_1',
        ]);

        // 6. View playlist
        $response = $this->actingAs($user)->get("/playlists/{$playlist->id}");
        $response->assertOk();
        $response->assertSee('My First Playlist');
        $response->assertSee('Test Karaoke Song');

        // 7. Visit watch page
        $response = $this->actingAs($user)->get('/watch/test_video_1');
        $response->assertOk();
        $response->assertSee('Test Karaoke Song');

        // 8. Verify playlists loaded on watch page (for dropdown)
        $response->assertViewHas('playlists', function ($playlists) use ($playlist) {
            return $playlists->contains('id', $playlist->id);
        });
    }

    /**
     * Test: Guest user can search but must login for features
     */
    public function test_guest_user_journey(): void
    {
        // Mock YouTube API
        Http::fake([
            'youtube.googleapis.com/youtube/v3/search*' => Http::response([
                'items' => [
                    [
                        'id' => ['videoId' => 'test_video_1'],
                        'snippet' => [
                            'title' => 'Test Karaoke Song',
                            'description' => 'Test Description',
                            'channelId' => 'test_channel',
                            'channelTitle' => 'Test Channel',
                            'publishedAt' => '2024-01-01T00:00:00Z',
                            'thumbnails' => [
                                'high' => ['url' => 'https://example.com/thumb.jpg'],
                            ],
                        ],
                    ],
                ],
            ]),
            'youtube.googleapis.com/youtube/v3/videos*' => Http::response([
                'items' => [
                    [
                        'id' => 'test_video_1',
                        'snippet' => [
                            'title' => 'Test Karaoke Song',
                            'description' => 'Test Description',
                            'channelId' => 'test_channel',
                            'channelTitle' => 'Test Channel',
                            'publishedAt' => '2024-01-01T00:00:00Z',
                            'thumbnails' => [
                                'high' => ['url' => 'https://example.com/thumb.jpg'],
                            ],
                        ],
                        'contentDetails' => ['duration' => 'PT3M45S'],
                        'statistics' => [
                            'viewCount' => '1000',
                            'likeCount' => '100',
                        ],
                    ],
                ],
            ]),
        ]);

        // 1. Guest can visit home
        $response = $this->get('/');
        $response->assertOk();

        // 2. Guest can search
        $response = $this->get('/search?q=karaoke');
        $response->assertOk();
        $response->assertSee('Test Karaoke Song');

        // 3. Guest can watch videos
        $response = $this->get('/watch/test_video_1');
        $response->assertOk();
        $response->assertSee('Test Karaoke Song');

        // 4. Guest sees login prompt for playlists
        $response = $this->get('/playlists');
        $response->assertRedirect('/login');

        // 5. Guest sees login prompt for favorites
        $response = $this->get('/favorites');
        $response->assertRedirect('/login');

        // 6. Guest sees login prompt for history
        $response = $this->get('/history');
        $response->assertRedirect('/login');
    }

    /**
     * Test: Authenticated user navigation shows counts
     */
    public function test_authenticated_user_sees_navigation_counts(): void
    {
        $user = User::factory()->create();

        // Create some data for the user
        $playlist = $user->playlists()->create([
            'name' => 'Test Playlist',
            'description' => 'Test',
        ]);

        $user->favorites()->create([
            'video_id' => 'test_video_1',
            'title' => 'Test Video',
            'thumbnail' => 'https://example.com/thumb.jpg',
        ]);

        $user->watchHistory()->create([
            'video_id' => 'test_video_2',
            'title' => 'Test Video 2',
            'watched_at' => now(),
        ]);

        // Visit home page
        $response = $this->actingAs($user)->get('/');
        $response->assertOk();

        // Check that navigation counts are loaded
        $response->assertViewHas('playlistsCount', 1);
        $response->assertViewHas('favoritesCount', 1);
        $response->assertViewHas('historyCount', 1);
    }
}
