<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PlaylistController extends Controller
{
    public function index(): View
    {
        $playlists = auth()->user()->playlists()
            ->withCount('items')
            ->recent()
            ->paginate(12);

        return view('playlists.index', compact('playlists'));
    }

    public function create(): View
    {
        return view('playlists.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_public' => 'boolean',
        ]);

        $playlist = auth()->user()->playlists()->create($validated);

        return redirect()->route('playlists.show', $playlist)
            ->with('success', 'Playlist created successfully!');
    }

    public function show(Playlist $playlist): View
    {
        if (!$playlist->is_public && $playlist->user_id !== auth()->id()) {
            abort(403, 'This playlist is private.');
        }

        $playlist->load('items');

        return view('playlists.show', compact('playlist'));
    }

    public function edit(Playlist $playlist): View
    {
        Gate::authorize('update', $playlist);

        return view('playlists.edit', compact('playlist'));
    }

    public function update(Request $request, Playlist $playlist): RedirectResponse
    {
        Gate::authorize('update', $playlist);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_public' => 'boolean',
        ]);

        $playlist->update($validated);

        return redirect()->route('playlists.show', $playlist)
            ->with('success', 'Playlist updated successfully!');
    }

    public function destroy(Playlist $playlist): RedirectResponse
    {
        Gate::authorize('delete', $playlist);

        $playlist->delete();

        return redirect()->route('playlists.index')
            ->with('success', 'Playlist deleted successfully!');
    }

    public function addVideo(Request $request, Playlist $playlist): JsonResponse|RedirectResponse
    {
        Gate::authorize('update', $playlist);

        $validated = $request->validate([
            'video_id' => 'required|string',
            'title' => 'required|string',
            'thumbnail' => 'nullable|string',
            'duration' => 'nullable|integer',
        ]);

        // Check if video already exists in playlist
        $exists = $playlist->items()
            ->where('video_id', $validated['video_id'])
            ->exists();

        if ($exists) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Video already in playlist!',
                ], 400);
            }
            return back()->with('error', 'Video already in playlist!');
        }

        $playlist->addVideo($validated['video_id'], $validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Video added to playlist!',
            ]);
        }

        return back()->with('success', 'Video added to playlist!');
    }

    public function removeVideo(Playlist $playlist, int $itemId): RedirectResponse
    {
        Gate::authorize('update', $playlist);

        $playlist->removeVideo($itemId);

        return back()->with('success', 'Video removed from playlist!');
    }
}
