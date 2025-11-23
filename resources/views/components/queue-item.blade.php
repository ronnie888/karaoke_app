@props(['item', 'index'])

<div class="flex items-center space-x-3 p-3 bg-dark-800 hover:bg-dark-700 rounded-lg transition group queue-item" data-item-id="{{ $item->id }}">
    <!-- Drag Handle -->
    <div class="drag-handle flex-shrink-0 cursor-move text-gray-500 hover:text-gray-300 transition">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M9 3h2v2H9V3zm0 4h2v2H9V7zm0 4h2v2H9v-2zm0 4h2v2H9v-2zm0 4h2v2H9v-2zM13 3h2v2h-2V3zm0 4h2v2h-2V7zm0 4h2v2h-2v-2zm0 4h2v2h-2v-2zm0 4h2v2h-2v-2z"/>
        </svg>
    </div>

    <!-- Position Number -->
    <div class="flex-shrink-0 w-8 text-center">
        <span class="text-gray-400 font-medium">{{ $index + 1 }}</span>
    </div>

    <!-- Thumbnail -->
    <div class="flex-shrink-0">
        @if($item->thumbnail)
            <img
                src="{{ $item->thumbnail }}"
                alt="{{ $item->title }}"
                class="w-16 h-12 object-cover rounded"
            />
        @else
            <div class="w-16 h-12 bg-dark-700 rounded flex items-center justify-center">
                <svg class="w-6 h-6 text-gray-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/>
                </svg>
            </div>
        @endif
    </div>

    <!-- Song Info -->
    <div class="flex-1 min-w-0">
        <h4 class="text-white font-medium truncate">{{ $item->title }}</h4>
        <p class="text-sm text-gray-400 truncate">{{ $item->channel_title }}</p>
    </div>

    <!-- Duration -->
    @if($item->duration)
        <div class="flex-shrink-0 text-sm text-gray-400">
            {{ $item->formatted_duration }}
        </div>
    @endif

    <!-- Actions -->
    <div class="flex-shrink-0 flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition">
        <!-- Play Now -->
        <button
            onclick="playQueueItem({{ $item->id }})"
            class="p-2 rounded-full bg-primary-600 hover:bg-primary-700 text-white transition"
            title="Play Now"
        >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M8 5v14l11-7z"/>
            </svg>
        </button>

        <!-- Move Up -->
        @if($index > 0)
            <button
                onclick="moveQueueItem({{ $item->id }}, 'up')"
                class="p-2 rounded-full bg-dark-600 hover:bg-dark-500 text-white transition"
                title="Move Up"
            >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M7 14l5-5 5 5H7z"/>
                </svg>
            </button>
        @endif

        <!-- Move Down -->
        <button
            onclick="moveQueueItem({{ $item->id }}, 'down')"
            class="p-2 rounded-full bg-dark-600 hover:bg-dark-500 text-white transition"
            title="Move Down"
        >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M7 10l5 5 5-5H7z"/>
            </svg>
        </button>

        <!-- Remove -->
        <button
            onclick="removeFromQueue({{ $item->id }})"
            class="p-2 rounded-full bg-red-600 hover:bg-red-700 text-white transition"
            title="Remove"
        >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
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

async function moveQueueItem(itemId, direction) {
    // This is a simplified version - in production you'd want more sophisticated reordering
    console.log(`Move item ${itemId} ${direction}`);
    // For now, just reload - proper implementation would use drag-and-drop or the reorder endpoint
}
</script>
@endpush
@endonce
