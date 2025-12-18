@props(['popularSongs', 'favorites', 'currentVideoId' => null, 'librarySongs' => collect(), 'librarySongsCount' => 0])

<div class="bg-dark-850 rounded-lg overflow-hidden h-full flex flex-col" id="tabbed-browse" x-data="{ activeTab: 'library' }">
    <!-- Header with Tabs -->
    <div class="px-6 py-4 border-b border-dark-700 flex-shrink-0">
        <h2 class="text-xl font-bold text-white mb-4">Search & Browse</h2>

        <!-- Tabs - Scrollable on mobile -->
        <div class="flex space-x-2 overflow-x-auto pb-2 -mb-2 scrollbar-hide">
            <button
                @click="activeTab = 'library'"
                :class="activeTab === 'library' ? 'bg-green-600 text-white' : 'bg-dark-700 text-gray-300 hover:bg-dark-600'"
                class="px-4 py-2 rounded-lg font-medium transition whitespace-nowrap flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 3v9.28c-.47-.17-.97-.28-1.5-.28C8.01 12 6 14.01 6 16.5S8.01 21 10.5 21c2.31 0 4.2-1.75 4.45-4H15V6h4V3h-7z"/>
                </svg>
                Library
                @if($librarySongsCount > 0)
                    <span class="text-xs bg-green-700 px-1.5 py-0.5 rounded-full">{{ number_format($librarySongsCount) }}</span>
                @endif
            </button>
            <button
                @click="activeTab = 'favorites'"
                :class="activeTab === 'favorites' ? 'bg-primary-600 text-white' : 'bg-dark-700 text-gray-300 hover:bg-dark-600'"
                class="px-4 py-2 rounded-lg font-medium transition whitespace-nowrap"
            >
                Favorites
            </button>
        </div>
    </div>

    <!-- Content Area -->
    <div class="flex-1 overflow-y-auto custom-scrollbar">
        <!-- Library Tab (Local Songs) -->
        <div x-show="activeTab === 'library'" class="p-4 space-y-3">
            <!-- Song List -->
            <div class="space-y-2">
                    @forelse($librarySongs as $song)
                        <div class="flex items-center space-x-3 p-3 bg-dark-800 hover:bg-dark-700 rounded-lg transition group">
                            <div class="w-12 h-12 bg-green-900/30 rounded flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 3v9.28c-.47-.17-.97-.28-1.5-.28C8.01 12 6 14.01 6 16.5S8.01 21 10.5 21c2.31 0 4.2-1.75 4.45-4H15V6h4V3h-7z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-white font-medium truncate">{{ $song->title }}</h4>
                                <p class="text-sm text-gray-400 truncate">{{ $song->artist ?? 'Unknown Artist' }}</p>
                                <div class="flex items-center gap-2 text-xs text-gray-500 mt-1">
                                    @if($song->genre)
                                        <span class="bg-dark-700 px-1.5 py-0.5 rounded">{{ $song->genre }}</span>
                                    @endif
                                    <span>{{ $song->formatted_duration }}</span>
                                    @if($song->play_count > 0)
                                        <span>{{ $song->play_count }} plays</span>
                                    @endif
                                </div>
                            </div>
                            <button
                                onclick="addLibrarySongToQueue({{ $song->id }})"
                                class="p-2 rounded-full bg-green-600 hover:bg-green-700 text-white transition flex-shrink-0 opacity-0 group-hover:opacity-100"
                                title="Add to Queue"
                            >
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                                </svg>
                            </button>
                        </div>
                    @empty
                        <div class="py-8 text-center text-gray-400">
                            <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 3v9.28c-.47-.17-.97-.28-1.5-.28C8.01 12 6 14.01 6 16.5S8.01 21 10.5 21c2.31 0 4.2-1.75 4.45-4H15V6h4V3h-7z"/>
                            </svg>
                            <p class="font-medium">No songs in library yet</p>
                            <p class="text-sm mt-2">Songs will appear here after indexing.</p>
                        </div>
                    @endforelse
            </div>
        </div>

        <!-- Favorites Tab -->
        <div x-show="activeTab === 'favorites'" class="p-4 space-y-2">
            @forelse($favorites as $favorite)
                <div class="flex items-center space-x-3 p-3 bg-dark-800 hover:bg-dark-700 rounded-lg transition">
                    @if($favorite->thumbnail)
                        <img src="{{ $favorite->thumbnail }}" alt="{{ $favorite->title }}" class="w-16 h-12 object-cover rounded" />
                    @else
                        <div class="w-16 h-12 bg-dark-700 rounded flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/>
                            </svg>
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <h4 class="text-white font-medium truncate">{{ $favorite->title }}</h4>
                    </div>
                    <button
                        onclick="addFavoriteToQueue('{{ $favorite->video_id }}', '{{ addslashes($favorite->title) }}', '{{ $favorite->thumbnail }}')"
                        class="p-2 rounded-full bg-primary-600 hover:bg-primary-700 text-white transition"
                        title="Add to Queue"
                    >
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                        </svg>
                    </button>
                </div>
            @empty
                <div class="py-8 text-center text-gray-400">
                    <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    <p>No favorite songs yet</p>
                    <p class="text-sm mt-2">Heart songs while browsing to add them here!</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
async function addFavoriteToQueue(videoId, title, thumbnail) {
    await window.queueManager.addToQueue({
        video_id: videoId,
        title: title,
        thumbnail: thumbnail,
        channel_title: '',
        duration: null,
    });
}

// Add local song from library to queue
async function addLibrarySongToQueue(songId) {
    try {
        const response = await fetch('/queue/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ song_id: songId }),
        });

        const data = await response.json();

        if (data.success) {
            // Show toast notification
            if (typeof showToast !== 'undefined' && showToast.success) {
                showToast.success('Song added to queue!');
            } else if (window.showToast) {
                window.showToast('Song added to queue!', 'success');
            }

            // Refresh queue display without page reload
            if (window.queueManager) {
                await window.queueManager.fetchQueue();
                window.queueManager.refreshQueueDisplay();
            }

            // If auto-played, reload page to show player
            if (data.auto_played) {
                setTimeout(() => window.location.reload(), 500);
            }
        } else {
            console.error('Failed to add song:', data.message);
            if (typeof showToast !== 'undefined' && showToast.error) {
                showToast.error('Failed to add song');
            } else if (window.showToast) {
                window.showToast('Failed to add song', 'error');
            }
        }
    } catch (error) {
        console.error('Error adding song to queue:', error);
        if (typeof showToast !== 'undefined' && showToast.error) {
            showToast.error('Error adding song');
        } else if (window.showToast) {
            window.showToast('Error adding song', 'error');
        }
    }
}
</script>
@endpush
