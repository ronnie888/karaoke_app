@props([
    'videoId',
    'title',
    'thumbnail' => null,
    'duration' => null,
    'playlists' => [],
])

<div
    x-data="{
        open: false,
        loading: false,
        playlists: {{ json_encode($playlists->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'hasVideo' => $p->items->contains('video_id', $videoId)
        ])) }},

        async addToPlaylist(playlistId) {
            @guest
                window.location.href = '{{ route('login') }}';
                return;
            @endguest

            this.loading = true;

            try {
                const response = await fetch(`/playlists/${playlistId}/add`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        video_id: '{{ $videoId }}',
                        title: '{{ addslashes($title) }}',
                        thumbnail: '{{ addslashes($thumbnail ?? '') }}',
                        duration: {{ $duration ?? 'null' }},
                    }),
                });

                const data = await response.json();

                if (response.ok) {
                    // Update playlist to show it now has the video
                    const playlist = this.playlists.find(p => p.id === playlistId);
                    if (playlist) {
                        playlist.hasVideo = true;
                    }

                    this.open = false;

                    // Show success message
                    if (typeof window.showToast === 'function') {
                        window.showToast(data.message || 'Video added to playlist!', 'success');
                    } else {
                        alert(data.message || 'Video added to playlist!');
                    }
                } else {
                    alert(data.message || 'Failed to add video to playlist');
                }
            } catch (error) {
                console.error('Add to playlist error:', error);
                alert('Failed to add video to playlist. Please try again.');
            } finally {
                this.loading = false;
            }
        }
    }"
    @click.away="open = false"
    class="relative"
>
    <!-- Dropdown Button -->
    <button
        @click="open = !open"
        :disabled="loading"
        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition {{ $attributes->get('class') }}"
        :class="{ 'opacity-50 cursor-not-allowed': loading }"
    >
        <svg class="w-4 h-4 mr-1.5" :class="{ 'animate-pulse': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        <span>Add to Playlist</span>
        <svg class="w-4 h-4 ml-1.5" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <!-- Dropdown Menu -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-64 bg-white rounded-md shadow-lg z-50 border border-gray-200"
        style="display: none;"
    >
        <div class="py-1 max-h-64 overflow-y-auto">
            @auth
                @if($playlists->isEmpty())
                    <!-- Empty State -->
                    <div class="px-4 py-3 text-sm text-gray-500 text-center">
                        <p class="mb-2">No playlists yet</p>
                        <a
                            href="{{ route('playlists.create') }}"
                            class="text-primary-600 hover:text-primary-700 font-medium"
                        >
                            Create your first playlist
                        </a>
                    </div>
                @else
                    <!-- Playlist List -->
                    <template x-for="playlist in playlists" :key="playlist.id">
                        <button
                            @click="addToPlaylist(playlist.id)"
                            :disabled="playlist.hasVideo || loading"
                            class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 transition flex items-center justify-between"
                            :class="{
                                'opacity-50 cursor-not-allowed': playlist.hasVideo,
                                'cursor-pointer': !playlist.hasVideo
                            }"
                        >
                            <span class="flex-1 truncate" x-text="playlist.name"></span>
                            <span x-show="playlist.hasVideo" class="ml-2 text-xs text-green-600 flex items-center">
                                <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Added
                            </span>
                        </button>
                    </template>

                    <!-- Divider -->
                    <div class="border-t border-gray-200 my-1"></div>
                @endif

                <!-- Create New Playlist Link -->
                <a
                    href="{{ route('playlists.create') }}"
                    class="block px-4 py-2 text-sm text-primary-600 hover:bg-gray-100 transition font-medium"
                >
                    <svg class="w-4 h-4 inline mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create New Playlist
                </a>
            @else
                <!-- Guest State -->
                <div class="px-4 py-3 text-sm text-gray-500 text-center">
                    <a
                        href="{{ route('login') }}"
                        class="text-primary-600 hover:text-primary-700 font-medium"
                    >
                        Login to add to playlist
                    </a>
                </div>
            @endauth
        </div>
    </div>
</div>
