<?php

namespace App\Http\Controllers;

use App\Models\KaraokeSession;
use App\Models\QueueItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function __construct()
    {
        //
    }

    /**
     * Add video to queue
     */
    public function add(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'video_id' => 'required|string',
            'title' => 'required|string',
            'thumbnail' => 'nullable|string',
            'channel_title' => 'nullable|string',
            'duration' => 'nullable|integer',
        ]);

        $session = KaraokeSession::getOrCreateForUser(auth()->id());

        // Check if queue is empty (no items or no playing item)
        $wasEmpty = $session->queueItems()->count() === 0 || !$session->currentItem();

        $queueItem = $session->addVideo([
            'id' => $validated['video_id'],
            'title' => $validated['title'],
            'thumbnail' => $validated['thumbnail'] ?? null,
            'channel_title' => $validated['channel_title'] ?? null,
            'duration' => $validated['duration'] ?? null,
        ]);

        // If queue was empty, auto-play this first song
        if ($wasEmpty) {
            $queueItem->update(['is_playing' => true]);
            $session->update([
                'current_playing_id' => $queueItem->video_id,
                'current_position' => $queueItem->position,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Video added to queue',
            'data' => $queueItem,
            'auto_played' => $wasEmpty,
        ]);
    }

    /**
     * Remove video from queue
     */
    public function remove(Request $request, int $itemId): JsonResponse
    {
        $session = KaraokeSession::getOrCreateForUser(auth()->id());

        $item = $session->queueItems()->find($itemId);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Queue item not found',
            ], 404);
        }

        $session->removeVideo($itemId);

        return response()->json([
            'success' => true,
            'message' => 'Video removed from queue',
        ]);
    }

    /**
     * Reorder queue items
     */
    public function reorder(Request $request): JsonResponse
    {
        // Handle simple drag-and-drop reordering
        if ($request->has('item_id')) {
            $validated = $request->validate([
                'item_id' => 'required|integer',
                'old_position' => 'required|integer',
                'new_position' => 'required|integer',
            ]);

            $session = KaraokeSession::getOrCreateForUser(auth()->id());
            $item = $session->queueItems()->findOrFail($validated['item_id']);

            $oldPos = $validated['old_position'];
            $newPos = $validated['new_position'];

            // Update positions of affected items
            if ($oldPos < $newPos) {
                // Moving down: shift items between old and new position up
                $session->queueItems()
                    ->where('position', '>', $oldPos)
                    ->where('position', '<=', $newPos)
                    ->decrement('position');
            } else {
                // Moving up: shift items between new and old position down
                $session->queueItems()
                    ->where('position', '>=', $newPos)
                    ->where('position', '<', $oldPos)
                    ->increment('position');
            }

            // Update the moved item's position
            $item->update(['position' => $newPos]);

            return response()->json([
                'success' => true,
                'message' => 'Queue reordered',
            ]);
        }

        // Handle batch reordering (original implementation)
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.position' => 'required|integer',
        ]);

        $session = KaraokeSession::getOrCreateForUser(auth()->id());

        foreach ($validated['items'] as $itemData) {
            $session->queueItems()
                ->where('id', $itemData['id'])
                ->update(['position' => $itemData['position']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Queue reordered',
        ]);
    }

    /**
     * Play next song in queue
     */
    public function next(Request $request): JsonResponse
    {
        $session = KaraokeSession::getOrCreateForUser(auth()->id());

        $nextItem = $session->playNext();

        if (!$nextItem) {
            return response()->json([
                'success' => false,
                'message' => 'No more songs in queue',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Playing next song',
            'data' => $nextItem,
        ]);
    }

    /**
     * Clear all queue items
     */
    public function clear(Request $request): JsonResponse
    {
        $session = KaraokeSession::getOrCreateForUser(auth()->id());

        $session->clearQueue();

        return response()->json([
            'success' => true,
            'message' => 'Queue cleared',
        ]);
    }

    /**
     * Get current queue
     */
    public function index(Request $request): JsonResponse
    {
        $session = KaraokeSession::getOrCreateForUser(auth()->id());

        $currentItem = $session->currentItem();
        $queueItems = $session->queueItems()->queued()->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => [
                'current' => $currentItem,
                'queue' => $queueItems,
                'session_id' => $session->id,
            ],
        ]);
    }

    /**
     * Start playing a specific video
     */
    public function play(Request $request, int $itemId): JsonResponse
    {
        $session = KaraokeSession::getOrCreateForUser(auth()->id());

        $item = $session->queueItems()->find($itemId);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Queue item not found',
            ], 404);
        }

        // Mark all as not playing
        $session->queueItems()->update(['is_playing' => false]);

        // Mark this one as playing
        $item->update(['is_playing' => true]);

        $session->update([
            'current_playing_id' => $item->video_id,
            'current_position' => $item->position,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Now playing',
            'data' => $item,
        ]);
    }
}
