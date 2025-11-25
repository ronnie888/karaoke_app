<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\VideoSearchDTO;
use App\Models\Favorite;
use App\Models\KaraokeSession;
use App\Models\Playlist;
use App\Services\YouTubeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private YouTubeService $youtubeService
    ) {
        //
    }

    /**
     * Show the karaoke dashboard
     */
    public function index(Request $request): View
    {
        $session = KaraokeSession::getOrCreateForUser(auth()->id());

        // Get current playing item and queue
        $currentItem = $session->currentItem();
        $queueItems = $session->queueItems()->queued()->ordered()->get();

        // Get user's playlists for "Add to Playlist" dropdown
        $playlists = Playlist::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        // Get popular karaoke songs (cached)
        $popularSongs = $this->getPopularSongs();

        // Get user's favorites
        $favorites = Favorite::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('karaoke.dashboard', compact(
            'session',
            'currentItem',
            'queueItems',
            'playlists',
            'popularSongs',
            'favorites'
        ));
    }

    /**
     * Get popular karaoke songs
     */
    private function getPopularSongs()
    {
        return cache()->remember('popular_karaoke_songs', 7200, function () {
            $searchDTO = new VideoSearchDTO(
                query: 'karaoke popular songs',
                maxResults: 20,
                order: 'viewCount'
            );

            try {
                return $this->youtubeService->search($searchDTO);
            } catch (\Exception $e) {
                \Log::error('Failed to fetch popular songs', ['error' => $e->getMessage()]);

                return collect();
            }
        });
    }

    /**
     * Get trending karaoke songs
     */
    public function trending(Request $request)
    {
        $searchDTO = new VideoSearchDTO(
            query: 'karaoke',
            maxResults: 20,
            order: 'date'
        );

        try {
            $results = $this->youtubeService->search($searchDTO);

            return response()->json([
                'success' => true,
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch trending songs',
            ], 500);
        }
    }

    /**
     * Search by genre
     */
    public function genre(Request $request, string $genre)
    {
        $searchDTO = new VideoSearchDTO(
            query: "{$genre} karaoke",
            maxResults: 20,
            order: 'relevance'
        );

        try {
            $results = $this->youtubeService->search($searchDTO);

            return response()->json([
                'success' => true,
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch genre songs',
            ], 500);
        }
    }

    /**
     * Get top 3 related songs based on currently playing video
     */
    public function top3(Request $request, string $videoId)
    {
        try {
            $results = $this->youtubeService->getRelatedVideos($videoId, 3);

            return response()->json([
                'success' => true,
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch related songs',
            ], 500);
        }
    }
}
