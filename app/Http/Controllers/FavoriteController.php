<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(): View
    {
        $favorites = auth()->user()->favorites()
            ->recent()
            ->paginate(24);

        return view('favorites.index', compact('favorites'));
    }

    public function store(Request $request, string $videoId): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'thumbnail' => 'nullable|string',
        ]);

        // Check if already favorited
        $exists = Favorite::where('user_id', auth()->id())
            ->where('video_id', $videoId)
            ->exists();

        if ($exists) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Video is already in favorites!',
                ], 400);
            }
            return back()->with('error', 'Video is already in favorites!');
        }

        Favorite::toggle(auth()->id(), $videoId, $validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Video added to favorites!',
            ]);
        }

        return back()->with('success', 'Video added to favorites!');
    }

    public function destroy(string $videoId): JsonResponse|RedirectResponse
    {
        $deleted = Favorite::where('user_id', auth()->id())
            ->where('video_id', $videoId)
            ->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Video removed from favorites!',
            ]);
        }

        return back()->with('success', 'Video removed from favorites!');
    }
}
