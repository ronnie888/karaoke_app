@props(['item', 'index', 'totalItems' => 0])

<div class="flex items-center space-x-2 p-2 bg-dark-800 hover:bg-dark-700 rounded-lg transition group queue-item" data-item-id="{{ $item->id }}" data-position="{{ $item->position }}">
    <!-- Position Number -->
    <div class="flex-shrink-0 w-6 text-center">
        <span class="text-gray-500 text-sm font-medium">{{ $index + 1 }}</span>
    </div>

    <!-- Play Button -->
    <button
        onclick="playQueueItem({{ $item->id }})"
        class="flex-shrink-0 w-8 h-8 bg-dark-700 rounded-full flex items-center justify-center hover:bg-primary-600 transition"
        title="Play Now"
    >
        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24">
            <path d="M8 5v14l11-7z"/>
        </svg>
    </button>

    <!-- Song Info - Takes most space -->
    <div class="flex-1 min-w-0 pr-2">
        <h4 class="text-white text-sm font-medium leading-tight" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="{{ $item->title }}">{{ $item->title }}</h4>
        @if($item->channel_title || ($item->song && $item->song->artist))
            <p class="text-xs text-gray-500 truncate">{{ $item->channel_title ?? ($item->song ? $item->song->artist : '') }}</p>
        @endif
    </div>

    <!-- Duration -->
    <div class="flex-shrink-0 text-xs text-gray-500 w-10 text-right">
        {{ $item->formatted_duration ?? '4:00' }}
    </div>

    <!-- Actions - Always visible on mobile, hover on desktop -->
    <div class="flex-shrink-0 flex items-center space-x-1">
        <!-- Move Up -->
        @if($index > 0)
            <button
                onclick="moveQueueItem({{ $item->id }}, {{ $item->position }}, 'up')"
                class="p-1.5 rounded bg-dark-600 hover:bg-dark-500 text-gray-400 hover:text-white transition"
                title="Move Up"
            >
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M7 14l5-5 5 5H7z"/>
                </svg>
            </button>
        @endif

        <!-- Move Down -->
        @if($index < $totalItems - 1)
            <button
                onclick="moveQueueItem({{ $item->id }}, {{ $item->position }}, 'down')"
                class="p-1.5 rounded bg-dark-600 hover:bg-dark-500 text-gray-400 hover:text-white transition"
                title="Move Down"
            >
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M7 10l5 5 5-5H7z"/>
                </svg>
            </button>
        @endif

        <!-- Remove -->
        <button
            onclick="removeFromQueue({{ $item->id }})"
            class="p-1.5 rounded bg-dark-600 hover:bg-red-600 text-gray-400 hover:text-white transition"
            title="Remove"
        >
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/>
            </svg>
        </button>
    </div>
</div>

@once
@push('scripts')
<script>
async function playQueueItem(itemId) {
    try {
        const response = await fetch(`/queue/play/${itemId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            }
        });

        const data = await response.json();

        if (data.success) {
            window.location.reload();
        } else {
            alert('Failed to play song');
        }
    } catch (error) {
        console.error('Error playing song:', error);
        alert('Failed to play song');
    }
}

async function removeFromQueue(itemId) {
    if (!confirm('Remove this song from queue?')) return;
    await window.queueManager.removeFromQueue(itemId);
}

async function moveQueueItem(itemId, currentPosition, direction) {
    const newPosition = direction === 'up' ? currentPosition - 1 : currentPosition + 1;

    try {
        const response = await fetch('/queue/reorder', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                item_id: itemId,
                old_position: currentPosition,
                new_position: newPosition,
            })
        });

        const data = await response.json();

        if (data.success) {
            // Reload to show updated order
            window.location.reload();
        } else {
            showToast?.error(data.message || 'Failed to reorder queue');
        }
    } catch (error) {
        console.error('Error reordering queue:', error);
        showToast?.error('Failed to reorder queue');
    }
}
</script>
@endpush
@endonce
