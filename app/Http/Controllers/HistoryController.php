<?php

namespace App\Http\Controllers;

use App\Models\WatchHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(): View
    {
        $history = auth()->user()->watchHistory()
            ->recent()
            ->paginate(24);

        return view('history.index', compact('history'));
    }

    public function store(Request $request, string $videoId): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'thumbnail' => 'nullable|string',
            'watch_duration' => 'nullable|integer|min:0',
        ]);

        WatchHistory::record(
            auth()->id(),
            $videoId,
            $validated,
            $validated['watch_duration'] ?? 0
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Watch history recorded!',
            ]);
        }

        return back();
    }

    public function destroy(): RedirectResponse
    {
        WatchHistory::clearForUser(auth()->id());

        return back()->with('success', 'Watch history cleared!');
    }
}
