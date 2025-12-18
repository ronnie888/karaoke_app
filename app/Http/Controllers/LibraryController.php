<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\KaraokeSession;
use App\Models\Playlist;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LibraryController extends Controller
{
    /**
     * Show the library - main authenticated landing page
     */
    public function index(Request $request): View
    {
        // Get session for mini-player
        $session = KaraokeSession::getOrCreateForUser(auth()->id());
        $currentItem = $session->currentItem()?->load('song');

        // Get all available genres
        $genres = Song::indexed()
            ->whereNotNull('genre')
            ->where('genre', '!=', '')
            ->distinct()
            ->pluck('genre')
            ->sort()
            ->values();

        // Build query with filters
        $query = Song::indexed();

        // Genre filter
        if ($request->filled('genre') && $request->genre !== 'all') {
            $query->where('genre', $request->genre);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('artist', 'LIKE', "%{$search}%");
            });
        }

        // Sort
        $sort = $request->get('sort', 'popular');
        switch ($sort) {
            case 'az':
                $query->orderBy('title', 'asc');
                break;
            case 'recent':
                $query->orderBy('created_at', 'desc');
                break;
            case 'popular':
            default:
                $query->orderBy('play_count', 'desc');
                break;
        }

        // Paginate
        $songs = $query->paginate(50)->withQueryString();

        // Count total
        $totalSongs = Song::indexed()->count();

        // Get queue count for mini-player
        $queueCount = $session->queueItems()->queued()->count();

        // Get user's playlists for "Add to Playlist" dropdown
        $playlists = Playlist::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return view('library.index', compact(
            'songs',
            'genres',
            'totalSongs',
            'currentItem',
            'queueCount',
            'session',
            'playlists'
        ));
    }

    /**
     * Show the now playing / queue view
     */
    public function playing(Request $request): View
    {
        $session = KaraokeSession::getOrCreateForUser(auth()->id());

        // Get current playing item and queue
        $currentItem = $session->currentItem()?->load('song');
        $queueItems = $session->queueItems()->queued()->ordered()->with('song')->get();

        // Get user's playlists for dropdown
        $playlists = Playlist::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return view('library.playing', compact(
            'session',
            'currentItem',
            'queueItems',
            'playlists'
        ));
    }

    /**
     * API: Search songs for live search
     */
    public function search(Request $request)
    {
        $search = $request->get('q', '');

        if (strlen($search) < 2) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $songs = Song::indexed()
            ->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('artist', 'LIKE', "%{$search}%");
            })
            ->orderBy('play_count', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($song) {
                return [
                    'id' => $song->id,
                    'title' => $song->title,
                    'artist' => $song->artist,
                    'genre' => $song->genre,
                    'duration' => $song->duration,
                    'formatted_duration' => $song->formatted_duration,
                    'play_count' => $song->play_count,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $songs,
        ]);
    }
}
