@props(['queueItems'])

<div class="bg-dark-850 rounded-lg overflow-hidden h-full flex flex-col">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-dark-700 flex items-center justify-between flex-shrink-0">
        <h2 class="text-xl font-bold text-white">Upcoming Queue</h2>
        @if($queueItems->count() > 0)
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-400">{{ $queueItems->count() }} songs</span>
                <button
                    onclick="clearQueue()"
                    class="text-sm text-red-400 hover:text-red-300 transition"
                >
                    Clear All
                </button>
            </div>
        @endif
    </div>

    <!-- Queue Items -->
    <div class="flex-1 overflow-y-auto custom-scrollbar queue-items-container">
        @forelse($queueItems as $index => $item)
            <div class="px-4 py-1">
                <x-queue-item :item="$item" :index="$index" :totalItems="$queueItems->count()" />
            </div>
        @empty
            <!-- Empty State -->
            <div class="py-16 px-6 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h3 class="text-lg font-medium text-white mb-2">Queue is Empty</h3>
                <p class="text-gray-400">Add songs from the browse section to build your karaoke queue!</p>
            </div>
        @endforelse
    </div>

    <!-- Skip Button (if queue has items) -->
    @if($queueItems->count() > 0)
        <div class="px-6 py-4 border-t border-dark-700 flex-shrink-0">
            <button
                onclick="skipCurrentSong()"
                class="w-full py-3 px-4 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition flex items-center justify-center space-x-2"
            >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M6 4l12 8-12 8V4zm13 0v16h2V4h-2z"/>
                </svg>
                <span>Skip to Next Song</span>
            </button>
        </div>
    @endif
</div>

@push('scripts')
<script>
async function clearQueue() {
    await window.queueManager.clearQueue();
}

async function skipCurrentSong() {
    await window.queueManager.playNext();
}
</script>
@endpush

@push('styles')
<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #1a2332;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #475569;
    border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}

/* Sortable drag states */
.sortable-ghost {
    opacity: 0.4;
    background: #334155 !important;
}

.sortable-drag {
    opacity: 1;
    cursor: grabbing !important;
}

.sortable-chosen {
    background: #334155 !important;
}

.sortable-fallback {
    opacity: 0.8;
    background: #1e293b !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
}
</style>
@endpush
