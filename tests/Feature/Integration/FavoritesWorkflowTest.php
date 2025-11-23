<?php

namespace Tests\Feature\Integration;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Favorites Workflow Integration Tests
 *
 * Tests complete favorites management from adding to removing
 */
class FavoritesWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: User can favorite videos and view favorites page
     */
    public function test_complete_favorites_workflow(): void
    {
        $user = User::factory()->create();

        // Mock YouTube API
        Http::fake([
            'youtube.googleapis.com/*' => Http::response([
                'items' => [
                    [
                        'id' => ['videoId' => 'test_video_1'],
                        'snippet' => [
                            'title' => 'Favorite Song 1',
                            'description' => 'Test',
                            'channelId' => 'test_channel',
                            'channelTitle' => 'Test Channel',
                            'publishedAt' => '2024-01-01T00:00:00Z',
                            'thumbnails' => [
                                'high' => ['url' => 'https://example.com/thumb1.jpg'],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        // 1. Add video to favorites
        $response = $this->actingAs($user)->post('/favorites/test_video_1', [
            'title' => 'Favorite Song 1',
            'thumbnail' => 'https://example.com/thumb1.jpg',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Video added to favorites!');

        // Verify favorite created
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'video_id' => 'test_video_1',
            'title' => 'Favorite Song 1',
        ]);

        // 2. Try to favorite same video again (should fail)
        $response = $this->actingAs($user)->post('/favorites/test_video_1', [
            'title' => 'Favorite Song 1',
            'thumbnail' => 'https://example.com/thumb1.jpg',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Video is already in favorites!');

        // 3. Add another favorite
        $response = $this->actingAs($user)->post('/favorites/test_video_2', [
            'title' => 'Favorite Song 2',
            'thumbnail' => 'https://example.com/thumb2.jpg',
        ]);
        $response->assertRedirect();

        // 4. View favorites page
        $response = $this->actingAs($user)->get('/favorites');
        $response->assertOk();
        $response->assertSee('Favorite Song 1');
        $response->assertSee('Favorite Song 2');

        // Verify favorites returned
        $response->assertViewHas('favorites', function ($favorites) {
            return $favorites->count() === 2;
        });

        // 5. Remove favorite
        $response = $this->actingAs($user)->delete('/favorites/test_video_1');
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Video removed from favorites!');

        // Verify removed
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'video_id' => 'test_video_1',
        ]);

        // 6. View favorites page again (should only show one now)
        $response = $this->actingAs($user)->get('/favorites');
        $response->assertOk();
        $response->assertDontSee('Favorite Song 1');
        $response->assertSee('Favorite Song 2');
    }

    /**
     * Test: AJAX favorite toggle from video cards and watch page
     */
    public function test_ajax_favorite_toggle(): void
    {
        $user = User::factory()->create();

        // Add favorite via JSON request (AJAX)
        $response = $this->actingAs($user)
            ->postJson('/favorites/test_video_1', [
                'title' => 'Test Song',
                'thumbnail' => 'https://example.com/thumb.jpg',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Video added to favorites!',
        ]);

        // Verify added
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'video_id' => 'test_video_1',
        ]);

        // Try to add again via AJAX (should fail)
        $response = $this->actingAs($user)
            ->postJson('/favorites/test_video_1', [
                'title' => 'Test Song',
                'thumbnail' => 'https://example.com/thumb.jpg',
            ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Video is already in favorites!',
        ]);

        // Remove via JSON request (AJAX)
        $response = $this->actingAs($user)
            ->deleteJson('/favorites/test_video_1');

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Video removed from favorites!',
        ]);

        // Verify removed
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'video_id' => 'test_video_1',
        ]);
    }

    /**
     * Test: Favorite status is shown correctly on video cards
     */
    public function test_favorite_status_on_video_cards(): void
    {
        $user = User::factory()->create();

        // Add favorite
        $user->favorites()->create([
            'video_id' => 'test_video_1',
            'title' => 'Favorited Song',
            'thumbnail' => 'https://example.com/thumb.jpg',
        ]);

        // Mock YouTube API
        Http::fake([
            'youtube.googleapis.com/youtube/v3/search*' => Http::response([
                'items' => [
                    [
                        'id' => ['videoId' => 'test_video_1'],
                        'snippet' => [
                            'title' => 'Favorited Song',
                            'description' => 'Test',
                            'channelId' => 'test_channel',
                            'channelTitle' => 'Test Channel',
                            'publishedAt' => '2024-01-01T00:00:00Z',
                            'thumbnails' => [
                                'high' => ['url' => 'https://example.com/thumb.jpg'],
                            ],
                        ],
                    ],
                    [
                        'id' => ['videoId' => 'test_video_2'],
                        'snippet' => [
                            'title' => 'Not Favorited Song',
                            'description' => 'Test',
                            'channelId' => 'test_channel',
                            'channelTitle' => 'Test Channel',
                            'publishedAt' => '2024-01-01T00:00:00Z',
                            'thumbnails' => [
                                'high' => ['url' => 'https://example.com/thumb2.jpg'],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        // Visit search page
        $response = $this->actingAs($user)->get('/search?q=test');
        $response->assertOk();

        // Both videos should be shown
        $response->assertSee('Favorited Song');
        $response->assertSee('Not Favorited Song');
    }

    /**
     * Test: Favorite status on watch page
     */
    public function test_favorite_status_on_watch_page(): void
    {
        $user = User::factory()->create();

        // Add favorite
        $user->favorites()->create([
            'video_id' => 'test_video_1',
            'title' => 'Favorited Song',
            'thumbnail' => 'https://example.com/thumb.jpg',
        ]);

        // Mock YouTube API
        Http::fake([
            'youtube.googleapis.com/*' => Http::response([
                'items' => [
                    [
                        'id' => 'test_video_1',
                        'snippet' => [
                            'title' => 'Favorited Song',
                            'description' => 'Test',
                            'channelId' => 'test_channel',
                            'channelTitle' => 'Test Channel',
                            'publishedAt' => '2024-01-01T00:00:00Z',
                            'thumbnails' => [
                                'high' => ['url' => 'https://example.com/thumb.jpg'],
                            ],
                        ],
                        'contentDetails' => ['duration' => 'PT3M'],
                        'statistics' => ['viewCount' => '1000', 'likeCount' => '100'],
                    ],
                ],
            ]),
        ]);

        // Visit watch page
        $response = $this->actingAs($user)->get('/watch/test_video_1');
        $response->assertOk();
        $response->assertSee('Favorited Song');
    }

    /**
     * Test: Empty favorites page shows proper message
     */
    public function test_empty_favorites_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/favorites');
        $response->assertOk();
        $response->assertSee('No favorites yet');
        $response->assertSee('Search for Videos');
    }

    /**
     * Test: Favorites page pagination
     */
    public function test_favorites_pagination(): void
    {
        $user = User::factory()->create();

        // Create 30 favorites
        for ($i = 1; $i <= 30; $i++) {
            $user->favorites()->create([
                'video_id' => "test_video_{$i}",
                'title' => "Song {$i}",
                'thumbnail' => "https://example.com/thumb{$i}.jpg",
            ]);
        }

        // First page
        $response = $this->actingAs($user)->get('/favorites');
        $response->assertOk();

        // Should have pagination
        $response->assertViewHas('favorites', function ($favorites) {
            return $favorites->count() === 12; // Default per page
        });
    }

    /**
     * Test: Guest user cannot favorite videos
     */
    public function test_guest_cannot_favorite(): void
    {
        // Try to favorite without auth
        $response = $this->post('/favorites/test_video_1', [
            'title' => 'Test Song',
            'thumbnail' => 'https://example.com/thumb.jpg',
        ]);

        $response->assertRedirect('/login');

        // Try to view favorites
        $response = $this->get('/favorites');
        $response->assertRedirect('/login');
    }

    /**
     * Test: User can only see their own favorites
     */
    public function test_user_only_sees_own_favorites(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // User1 favorites a video
        $user1->favorites()->create([
            'video_id' => 'test_video_1',
            'title' => 'User 1 Favorite',
            'thumbnail' => 'https://example.com/thumb.jpg',
        ]);

        // User2 favorites a different video
        $user2->favorites()->create([
            'video_id' => 'test_video_2',
            'title' => 'User 2 Favorite',
            'thumbnail' => 'https://example.com/thumb2.jpg',
        ]);

        // User1 should only see their favorite
        $response = $this->actingAs($user1)->get('/favorites');
        $response->assertOk();
        $response->assertSee('User 1 Favorite');
        $response->assertDontSee('User 2 Favorite');

        // User2 should only see their favorite
        $response = $this->actingAs($user2)->get('/favorites');
        $response->assertOk();
        $response->assertSee('User 2 Favorite');
        $response->assertDontSee('User 1 Favorite');
    }
}
