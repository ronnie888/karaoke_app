<?php

namespace Tests\Feature\Integration;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Playlist Workflow Integration Tests
 *
 * Tests complete playlist management workflows from creation to deletion
 */
class PlaylistWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: User can manage playlists from search to playback
     */
    public function test_complete_playlist_workflow(): void
    {
        $user = User::factory()->create();

        // Mock YouTube API
        Http::fake([
            'youtube.googleapis.com/*' => Http::response([
                'items' => [
                    [
                        'id' => ['videoId' => 'test_video_1'],
                        'snippet' => [
                            'title' => 'Test Song 1',
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

        // 1. Create playlist
        $response = $this->actingAs($user)->post('/playlists', [
            'name' => 'My Karaoke Night',
            'description' => 'Songs for Friday night',
            'is_public' => false,
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $playlist = $user->playlists()->first();
        $this->assertNotNull($playlist);
        $this->assertEquals('My Karaoke Night', $playlist->name);

        // 2. Add video to playlist
        $response = $this->actingAs($user)->post("/playlists/{$playlist->id}/add", [
            'video_id' => 'test_video_1',
            'title' => 'Test Song 1',
            'thumbnail' => 'https://example.com/thumb1.jpg',
            'duration' => 180,
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Video added to playlist!');

        // Verify video added
        $this->assertDatabaseHas('playlist_items', [
            'playlist_id' => $playlist->id,
            'video_id' => 'test_video_1',
            'position' => 0,
        ]);

        // 3. Add another video
        $response = $this->actingAs($user)->post("/playlists/{$playlist->id}/add", [
            'video_id' => 'test_video_2',
            'title' => 'Test Song 2',
            'thumbnail' => 'https://example.com/thumb2.jpg',
            'duration' => 200,
        ]);
        $response->assertRedirect();

        // Verify second video has correct position
        $this->assertDatabaseHas('playlist_items', [
            'playlist_id' => $playlist->id,
            'video_id' => 'test_video_2',
            'position' => 1,
        ]);

        // 4. Try to add duplicate video (should fail)
        $response = $this->actingAs($user)->post("/playlists/{$playlist->id}/add", [
            'video_id' => 'test_video_1',
            'title' => 'Test Song 1',
            'thumbnail' => 'https://example.com/thumb1.jpg',
            'duration' => 180,
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Video already in playlist!');

        // 5. View playlist
        $response = $this->actingAs($user)->get("/playlists/{$playlist->id}");
        $response->assertOk();
        $response->assertSee('My Karaoke Night');
        $response->assertSee('Test Song 1');
        $response->assertSee('Test Song 2');

        // 6. Remove video from playlist
        $item = $playlist->items()->where('video_id', 'test_video_1')->first();
        $response = $this->actingAs($user)->delete("/playlists/{$playlist->id}/remove/{$item->id}");
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Video removed from playlist!');

        // Verify video removed
        $this->assertDatabaseMissing('playlist_items', [
            'id' => $item->id,
        ]);

        // Verify remaining item re-positioned
        $this->assertDatabaseHas('playlist_items', [
            'playlist_id' => $playlist->id,
            'video_id' => 'test_video_2',
            'position' => 0, // Should be reordered to position 0
        ]);

        // 7. Edit playlist
        $response = $this->actingAs($user)->put("/playlists/{$playlist->id}", [
            'name' => 'My Updated Playlist',
            'description' => 'Updated description',
            'is_public' => true,
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Playlist updated successfully!');

        // Verify updated
        $this->assertDatabaseHas('playlists', [
            'id' => $playlist->id,
            'name' => 'My Updated Playlist',
            'is_public' => true,
        ]);

        // 8. Delete playlist
        $response = $this->actingAs($user)->delete("/playlists/{$playlist->id}");
        $response->assertRedirect('/playlists');
        $response->assertSessionHas('success', 'Playlist deleted successfully!');

        // Verify soft deleted
        $this->assertSoftDeleted('playlists', [
            'id' => $playlist->id,
        ]);
    }

    /**
     * Test: AJAX add to playlist from watch page
     */
    public function test_ajax_add_to_playlist_workflow(): void
    {
        $user = User::factory()->create();
        $playlist = $user->playlists()->create([
            'name' => 'Test Playlist',
            'description' => 'Test',
        ]);

        // Add video via JSON request (AJAX)
        $response = $this->actingAs($user)
            ->postJson("/playlists/{$playlist->id}/add", [
                'video_id' => 'test_video_1',
                'title' => 'Test Song',
                'thumbnail' => 'https://example.com/thumb.jpg',
                'duration' => 180,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Video added to playlist!',
        ]);

        // Verify added
        $this->assertDatabaseHas('playlist_items', [
            'playlist_id' => $playlist->id,
            'video_id' => 'test_video_1',
        ]);

        // Try to add duplicate (should fail with JSON error)
        $response = $this->actingAs($user)
            ->postJson("/playlists/{$playlist->id}/add", [
                'video_id' => 'test_video_1',
                'title' => 'Test Song',
                'thumbnail' => 'https://example.com/thumb.jpg',
                'duration' => 180,
            ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Video already in playlist!',
        ]);
    }

    /**
     * Test: User cannot access other user's private playlists
     */
    public function test_playlist_authorization(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // User1 creates private playlist
        $playlist = $user1->playlists()->create([
            'name' => 'Private Playlist',
            'description' => 'Test',
            'is_public' => false,
        ]);

        // User2 cannot view private playlist
        $response = $this->actingAs($user2)->get("/playlists/{$playlist->id}");
        $response->assertForbidden();

        // User2 cannot edit playlist
        $response = $this->actingAs($user2)->put("/playlists/{$playlist->id}", [
            'name' => 'Hacked',
        ]);
        $response->assertForbidden();

        // User2 cannot delete playlist
        $response = $this->actingAs($user2)->delete("/playlists/{$playlist->id}");
        $response->assertForbidden();

        // User2 cannot add videos to playlist
        $response = $this->actingAs($user2)->post("/playlists/{$playlist->id}/add", [
            'video_id' => 'test_video',
            'title' => 'Test',
        ]);
        $response->assertForbidden();
    }

    /**
     * Test: Public playlists are viewable by everyone
     */
    public function test_public_playlist_access(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // User1 creates public playlist
        $playlist = $user1->playlists()->create([
            'name' => 'Public Playlist',
            'description' => 'Everyone can see this',
            'is_public' => true,
        ]);

        $playlist->items()->create([
            'video_id' => 'test_video_1',
            'title' => 'Test Song',
            'thumbnail' => 'https://example.com/thumb.jpg',
            'duration' => 180,
            'position' => 0,
        ]);

        // User2 can view public playlist
        $response = $this->actingAs($user2)->get("/playlists/{$playlist->id}");
        $response->assertOk();
        $response->assertSee('Public Playlist');
        $response->assertSee('Test Song');

        // But User2 cannot edit it
        $response = $this->actingAs($user2)->put("/playlists/{$playlist->id}", [
            'name' => 'Hacked',
        ]);
        $response->assertForbidden();
    }

    /**
     * Test: Playlist displays on watch page for adding videos
     */
    public function test_playlists_available_on_watch_page(): void
    {
        $user = User::factory()->create();

        // Create multiple playlists
        $playlist1 = $user->playlists()->create(['name' => 'Playlist 1', 'description' => 'Test']);
        $playlist2 = $user->playlists()->create(['name' => 'Playlist 2', 'description' => 'Test']);

        // Mock YouTube API
        Http::fake([
            'youtube.googleapis.com/*' => Http::response([
                'items' => [
                    [
                        'id' => 'test_video_1',
                        'snippet' => [
                            'title' => 'Test Song',
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

        // Verify playlists are loaded
        $response->assertViewHas('playlists', function ($playlists) use ($playlist1, $playlist2) {
            return $playlists->count() === 2
                && $playlists->contains('id', $playlist1->id)
                && $playlists->contains('id', $playlist2->id);
        });
    }
}
