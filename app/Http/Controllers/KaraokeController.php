<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\YouTube\GetVideoDetailsAction;
use App\Actions\YouTube\SearchVideosAction;
use App\Http\Requests\SearchRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Karaoke Controller
 *
 * Handles web UI routes for video search and playback.
 * Delegates business logic to Actions and returns Blade views.
 */
class KaraokeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly SearchVideosAction $searchAction,
        private readonly GetVideoDetailsAction $videoDetailsAction,
    ) {}

    /**
     * Display the home page with search interface.
     *
     * GET /
     */
    public function index(): View
    {
        return view('karaoke.index', [
            'title' => 'Karaoke Tube - Search YouTube Karaoke Videos',
            'description' => 'Search and play karaoke videos from YouTube',
        ]);
    }

    /**
     * Handle search requests and display results.
     *
     * GET /search?q=karaoke&maxResults=25
     */
    public function search(SearchRequest $request): View|RedirectResponse
    {
        try {
            // Get validated search parameters
            $params = $request->getSearchParams();

            // Execute search action
            $results = $this->searchAction->executeFromArray($params);

            return view('karaoke.search', [
                'query' => $params['query'],
                'results' => $results,
                'total' => $results->count(),
                'maxResults' => $params['maxResults'],
                'order' => $params['order'],
            ]);
        } catch (\Exception $e) {
            // Log error
            logger()->error('Search failed', [
                'query' => $request->input('q'),
                'error' => $e->getMessage(),
            ]);

            // Redirect back with error message
            return redirect()->route('home')
                ->with('error', 'Search failed. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display video player page.
     *
     * GET /watch/{videoId}
     */
    public function watch(string $videoId): View|RedirectResponse
    {
        try {
            // Validate video ID format
            if (! preg_match('/^[a-zA-Z0-9_-]{11}$/', $videoId)) {
                return redirect()->route('home')
                    ->with('error', 'Invalid video ID format.');
            }

            // Get video details
            $video = $this->videoDetailsAction->execute($videoId);

            // Check if video exists
            if ($video === null) {
                return redirect()->route('home')
                    ->with('error', 'Video not found or unavailable.');
            }

            // Load user's playlists if authenticated
            $playlists = auth()->check()
                ? auth()->user()->playlists()->with('items')->recent()->get()
                : collect();

            return view('karaoke.watch', [
                'video' => $video,
                'title' => $video->title . ' - Karaoke Tube',
                'description' => $video->description,
                'playlists' => $playlists,
            ]);
        } catch (\Exception $e) {
            // Log error
            logger()->error('Failed to load video', [
                'video_id' => $videoId,
                'error' => $e->getMessage(),
            ]);

            // Redirect back with error message
            return redirect()->route('home')
                ->with('error', 'Failed to load video. Please try again.')
                ->withInput();
        }
    }
}
