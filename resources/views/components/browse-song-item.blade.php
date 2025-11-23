@props(['song'])

<div class="flex items-center space-x-3 p-3 bg-dark-800 hover:bg-dark-700 rounded-lg transition">
    <!-- Thumbnail -->
    @if($song->thumbnailUrl ?? null)
        <img
            src="{{ $song->thumbnailUrl }}"
            alt="{{ $song->title }}"
            class="w-16 h-12 object-cover rounded"
        />
    @else
        <div class="w-16 h-12 bg-dark-700 rounded flex items-center justify-center">
            <svg class="w-6 h-6 text-gray-500" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/>
            </svg>
        </div>
    @endif

    <!-- Song Info -->
    <div class="flex-1 min-w-0">
        <h4 class="text-white font-medium truncate">{{ $song->title }}</h4>
        <p class="text-sm text-gray-400 truncate">{{ $song->channelTitle ?? 'Unknown Artist' }}</p>
    </div>

    <!-- Add to Queue Button -->
    <button
        onclick="addSongToQueue('{{ $song->id }}', '{{ addslashes($song->title) }}', '{{ $song->thumbnailUrl ?? '' }}', '{{ addslashes($song->channelTitle ?? '') }}', {{ $song->duration ?? 'null' }})"
        class="p-2 rounded-full bg-primary-600 hover:bg-primary-700 text-white transition flex-shrink-0"
        title="Add to Queue"
    >
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
        </svg>
    </button>
</div>

@once
@push('scripts')
<script>
async function addSongToQueue(videoId, title, thumbnail, channelTitle, duration) {
    await window.queueManager.addToQueue({
        video_id: videoId,
        title: title,
        thumbnail: thumbnail,
        channel_title: channelTitle,
        duration: duration,
    });
}
</script>
@endpush
@endonce
