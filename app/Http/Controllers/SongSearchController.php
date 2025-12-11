<?php

namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\Request;

class SongSearchController extends Controller
{
    /**
     * Search songs with filters
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        $genre = $request->input('genre');
        $artist = $request->input('artist');
        $language = $request->input('language');
        $perPage = $request->input('per_page', 30);

        // Start with indexed songs only
        $songsQuery = Song::indexed();

        // Apply text search if query provided (using LIKE for simple database search)
        if ($query) {
            $searchTerm = '%' . $query . '%';
            $songsQuery->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', $searchTerm)
                  ->orWhere('artist', 'LIKE', $searchTerm)
                  ->orWhere('search_text', 'LIKE', $searchTerm);
            });
        }

        // Apply filters
        if ($genre) {
            $songsQuery->where('genre', $genre);
        }

        if ($artist) {
            $songsQuery->where('artist', 'LIKE', "%{$artist}%");
        }

        if ($language) {
            $songsQuery->where('language', $language);
        }

        // Order by relevance (title matches first, then play count)
        $songsQuery->orderByRaw("CASE WHEN title LIKE ? THEN 0 ELSE 1 END", [$query . '%'])
                   ->orderBy('play_count', 'desc');

        // Paginate results
        $songs = $songsQuery->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $songs->items(),
            'meta' => [
                'total' => $songs->total(),
                'per_page' => $songs->perPage(),
                'current_page' => $songs->currentPage(),
                'last_page' => $songs->lastPage(),
                'from' => $songs->firstItem(),
                'to' => $songs->lastItem(),
            ],
        ]);
    }

    /**
     * Browse songs by type (popular, recent, genre)
     */
    public function browse(Request $request)
    {
        $type = $request->input('type', 'popular');
        $genre = $request->input('genre');
        $limit = $request->input('limit', 50);

        $songs = match($type) {
            'popular' => Song::indexed()->popular($limit)->get(),
            'recent' => Song::indexed()->recent($limit)->get(),
            'genre' => $genre
                ? Song::indexed()->byGenre($genre)->popular($limit)->get()
                : Song::indexed()->popular($limit)->get(),
            default => Song::indexed()->popular($limit)->get(),
        };

        return response()->json([
            'success' => true,
            'data' => $songs,
            'type' => $type,
        ]);
    }

    /**
     * Get all available genres
     */
    public function genres()
    {
        $genres = Song::indexed()
            ->select('genre')
            ->whereNotNull('genre')
            ->groupBy('genre')
            ->orderBy('genre')
            ->pluck('genre')
            ->filter(); // Remove empty values

        return response()->json([
            'success' => true,
            'data' => $genres->values(),
        ]);
    }

    /**
     * Get all artists with song counts
     */
    public function artists()
    {
        $artists = Song::indexed()
            ->select('artist')
            ->selectRaw('COUNT(*) as song_count')
            ->whereNotNull('artist')
            ->groupBy('artist')
            ->orderBy('artist')
            ->get()
            ->map(fn($item) => [
                'name' => $item->artist,
                'song_count' => $item->song_count,
            ]);

        return response()->json([
            'success' => true,
            'data' => $artists,
        ]);
    }

    /**
     * Get songs by language
     */
    public function byLanguage(Request $request)
    {
        $language = $request->input('language', 'english');
        $limit = $request->input('limit', 50);

        $songs = Song::indexed()
            ->byLanguage($language)
            ->popular($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $songs,
            'language' => $language,
        ]);
    }

    /**
     * Get a single song by ID
     */
    public function show(Song $song)
    {
        if ($song->index_status !== 'completed') {
            abort(404, 'Song not available');
        }

        return response()->json([
            'success' => true,
            'data' => $song,
        ]);
    }
}
