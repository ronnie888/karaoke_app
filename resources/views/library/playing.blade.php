@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-900 pb-24 md:pb-8" x-data="playingApp()" x-init="init()">
    <!-- Header -->
    <div class="sticky top-0 z-40 bg-gray-900/95 backdrop-blur border-b border-gray-800">
        <div class="max-w-3xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('library') }}" class="text-gray-400 hover:text-white transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <span class="hidden sm:inline">Library</span>
            </a>
            <h1 class="text-lg font-semibold text-white">Now Playing</h1>
            <div class="w-16"></div>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 py-6">
        <!-- Current Playing -->
        @if($currentItem)
            <div class="mb-8">
                <!-- Video Player -->
                @if($currentItem->song)
                    <div class="relative aspect-video bg-black rounded-xl overflow-hidden shadow-2xl mb-4">
                        <video
                            id="karaokePlayer"
                            class="w-full h-full"
                            controls
                            autoplay
                            @ended="onVideoEnded()"
                            @timeupdate="onTimeUpdate($event)"
                            @loadedmetadata="onMetadataLoaded($event)"
                        >
                            <source src="{{ route('songs.stream', $currentItem->song->id) }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                @else
                    <!-- Fallback for non-song items -->
                    <div class="aspect-video bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-24 h-24 text-white/80" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 3v9.28c-.47-.17-.97-.28-1.5-.28C8.01 12 6 14.01 6 16.5S8.01 21 10.5 21c2.31 0 4.2-1.75 4.45-4H15V6h4V3h-7z"/>
                        </svg>
                    </div>
                @endif

                <!-- Song Info -->
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-white mb-2">
                        {{ $currentItem->song?->title ?? $currentItem->title ?? 'Unknown' }}
                    </h2>
                    <p class="text-gray-400 text-lg">
                        {{ $currentItem->song?->artist ?? 'Unknown Artist' }}
                    </p>
                    @if($currentItem->song?->genre)
                        <span class="inline-block mt-2 px-3 py-1 bg-gray-800 text-gray-300 text-sm rounded-full">
                            {{ $currentItem->song->genre }}
                        </span>
                    @endif
                </div>

                <!-- Custom Progress Bar -->
                <div class="mt-4 max-w-2xl mx-auto">
                    <div class="relative h-1 bg-gray-700 rounded-full cursor-pointer group" @click="seekTo($event)">
                        <div class="h-1 bg-primary-500 rounded-full transition-all" :style="'width: ' + progressPercent + '%'"></div>
                        <div class="absolute top-1/2 -translate-y-1/2 w-3 h-3 bg-white rounded-full shadow opacity-0 group-hover:opacity-100 transition" :style="'left: ' + progressPercent + '%'"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                        <span x-text="currentTimeFormatted">0:00</span>
                        <span x-text="durationFormatted">{{ $currentItem->song?->formatted_duration ?? '0:00' }}</span>
                    </div>
                </div>

                <!-- Playback Controls -->
                <div class="flex items-center justify-center gap-6 mt-4">
                    <button @click="previousSong()" class="p-3 text-gray-400 hover:text-white transition" title="Previous">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M6 6h2v12H6zm3.5 6l8.5 6V6z"/>
                        </svg>
                    </button>
                    <button @click="togglePlay()" class="p-4 bg-white text-gray-900 rounded-full hover:scale-105 transition-transform shadow-lg">
                        <svg x-show="!isPlaying" class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                        <svg x-show="isPlaying" class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                        </svg>
                    </button>
                    <button @click="nextSong()" class="p-3 text-gray-400 hover:text-white transition" title="Next">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M6 18l8.5-6L6 6v12zM16 6v12h2V6h-2z"/>
                        </svg>
                    </button>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16">
                <div class="w-32 h-32 mx-auto bg-gray-800 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-16 h-16 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-white mb-2">Nothing Playing</h2>
                <p class="text-gray-400 mb-6">Add songs from the library to start your karaoke session!</p>
                <a href="{{ route('library') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-full font-medium transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Browse Library
                </a>
            </div>
        @endif

        <!-- Queue Section -->
        <div class="mt-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    Up Next
                    @if($queueItems->count() > 0)
                        <span class="text-sm text-gray-500">({{ $queueItems->count() }})</span>
                    @endif
                </h3>
                @if($queueItems->count() > 0)
                    <button @click="clearQueue()" class="text-sm text-red-400 hover:text-red-300 transition">
                        Clear All
                    </button>
                @endif
            </div>

            @if($queueItems->count() > 0)
                <div class="space-y-2">
                    @foreach($queueItems as $index => $item)
                        <div class="bg-gray-800 rounded-lg p-3 flex items-center gap-3 group hover:bg-gray-750 transition" data-queue-id="{{ $item->id }}">
                            <!-- Position -->
                            <span class="w-6 text-center text-gray-500 text-sm">{{ $index + 1 }}</span>

                            <!-- Play Button / Song Icon -->
                            <button @click="playSong({{ $item->id }})" class="w-10 h-10 bg-gray-700 hover:bg-primary-600 rounded flex items-center justify-center flex-shrink-0 transition group/play" title="Play now">
                                <!-- Music note (default) -->
                                <svg class="w-5 h-5 text-gray-400 group-hover/play:hidden" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 3v9.28c-.47-.17-.97-.28-1.5-.28C8.01 12 6 14.01 6 16.5S8.01 21 10.5 21c2.31 0 4.2-1.75 4.45-4H15V6h4V3h-7z"/>
                                </svg>
                                <!-- Play icon (on hover) -->
                                <svg class="w-5 h-5 text-white hidden group-hover/play:block" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </button>

                            <!-- Song Info (also clickable to play) -->
                            <div class="flex-1 min-w-0 cursor-pointer" @click="playSong({{ $item->id }})">
                                <p class="text-white font-medium truncate hover:text-primary-400 transition">
                                    {{ $item->song?->title ?? $item->title ?? 'Unknown' }}
                                </p>
                                <p class="text-sm text-gray-500 truncate">
                                    {{ $item->song?->artist ?? 'Unknown Artist' }}
                                </p>
                            </div>

                            <!-- Duration -->
                            <span class="text-xs text-gray-500">
                                {{ $item->song?->formatted_duration ?? '0:00' }}
                            </span>

                            <!-- Reorder Buttons - Always visible -->
                            @if($queueItems->count() > 1)
                                <div class="flex items-center gap-0.5">
                                    <button @click="moveUp({{ $item->id }})"
                                            class="p-1.5 rounded transition {{ $index > 0 ? 'text-gray-400 hover:text-white hover:bg-gray-700' : 'text-gray-700 cursor-not-allowed' }}"
                                            title="Move up"
                                            {{ $index === 0 ? 'disabled' : '' }}>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                        </svg>
                                    </button>
                                    <button @click="moveDown({{ $item->id }})"
                                            class="p-1.5 rounded transition {{ $index < $queueItems->count() - 1 ? 'text-gray-400 hover:text-white hover:bg-gray-700' : 'text-gray-700 cursor-not-allowed' }}"
                                            title="Move down"
                                            {{ $index === $queueItems->count() - 1 ? 'disabled' : '' }}>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                </div>
                            @endif

                            <!-- More Options Menu -->
                            @if($item->song)
                                <div x-data="{ openMenu: false }" class="relative">
                                    <button @click="openMenu = !openMenu" class="p-1.5 text-gray-500 hover:text-white transition" title="More options">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                                        </svg>
                                    </button>
                                    <div x-show="openMenu" @click.away="openMenu = false" x-transition
                                         class="absolute right-0 bottom-full mb-2 w-48 bg-gray-800 rounded-lg shadow-lg border border-gray-700 z-50 py-1">
                                        <p class="px-4 py-1 text-xs text-gray-500 uppercase tracking-wide">Add to Playlist</p>
                                        @if($playlists->count() > 0)
                                            @foreach($playlists as $playlist)
                                                <button @click="addToPlaylist({{ $item->song->id }}, {{ $playlist->id }}); openMenu = false"
                                                        class="w-full text-left px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                                    </svg>
                                                    {{ $playlist->name }}
                                                </button>
                                            @endforeach
                                        @else
                                            <p class="px-4 py-2 text-xs text-gray-500 italic">No playlists yet</p>
                                        @endif
                                        <div class="border-t border-gray-700 my-1"></div>
                                        <button @click="removeFromQueue({{ $item->id }}); openMenu = false"
                                                class="w-full text-left px-4 py-2 text-sm text-red-400 hover:bg-gray-700 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Remove from Queue
                                        </button>
                                    </div>
                                </div>
                            @else
                                <!-- Remove Button for non-song items -->
                                <button @click="removeFromQueue({{ $item->id }})" class="p-1.5 text-gray-500 hover:text-red-400 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Add More Songs -->
                <div class="mt-4 text-center">
                    <a href="{{ route('library') }}" class="inline-flex items-center gap-2 text-primary-400 hover:text-primary-300 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add More Songs
                    </a>
                </div>
            @else
                <div class="bg-gray-800 rounded-lg p-8 text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    <p class="text-gray-400">Your queue is empty</p>
                    <a href="{{ route('library') }}" class="inline-flex items-center gap-2 mt-4 text-primary-400 hover:text-primary-300 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Songs from Library
                    </a>
                </div>
            @endif
        </div>

        <!-- Save to Playlist -->
        @if($queueItems->count() > 0 && $playlists->count() > 0)
            <div class="mt-6 p-4 bg-gray-800 rounded-lg">
                <h4 class="text-sm font-medium text-gray-300 mb-3">Save queue to playlist</h4>
                <div class="flex gap-2">
                    <select x-model="selectedPlaylist" class="flex-1 bg-gray-700 border-gray-600 text-white rounded-lg text-sm focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Select playlist...</option>
                        @foreach($playlists as $playlist)
                            <option value="{{ $playlist->id }}">{{ $playlist->name }}</option>
                        @endforeach
                    </select>
                    <button @click="saveToPlaylist()" :disabled="!selectedPlaylist" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 disabled:bg-gray-600 disabled:cursor-not-allowed text-white rounded-lg text-sm transition">
                        Save
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Mobile Bottom Navigation -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-gray-900 border-t border-gray-800 z-40">
        <div class="flex justify-around items-center h-16">
            <a href="{{ route('library') }}" class="flex flex-col items-center justify-center flex-1 py-2 text-gray-500 hover:text-white transition">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 3v9.28c-.47-.17-.97-.28-1.5-.28C8.01 12 6 14.01 6 16.5S8.01 21 10.5 21c2.31 0 4.2-1.75 4.45-4H15V6h4V3h-7z"/>
                </svg>
                <span class="text-xs mt-1">Library</span>
            </a>
            <a href="{{ route('library.playing') }}" class="flex flex-col items-center justify-center flex-1 py-2 text-primary-500">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/>
                </svg>
                <span class="text-xs mt-1 font-medium">Playing</span>
            </a>
            <a href="{{ route('playlists.index') }}" class="flex flex-col items-center justify-center flex-1 py-2 text-gray-500 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <span class="text-xs mt-1">Playlists</span>
            </a>
            <a href="{{ route('profile.edit') }}" class="flex flex-col items-center justify-center flex-1 py-2 text-gray-500 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="text-xs mt-1">Profile</span>
            </a>
        </div>
    </nav>

    <!-- Toast Notification -->
    <div x-show="showToast" x-transition class="fixed bottom-20 left-1/2 -translate-x-1/2 bg-gray-700 text-white px-4 py-2 rounded-lg shadow-lg z-50">
        <span x-text="toastMessage"></span>
    </div>
</div>

@push('scripts')
<script>
function playingApp() {
    return {
        isPlaying: true, // Video autoplays
        showToast: false,
        toastMessage: '',
        selectedPlaylist: '',
        progressPercent: 0,
        currentTimeFormatted: '0:00',
        durationFormatted: '0:00',
        videoElement: null,

        init() {
            this.videoElement = document.getElementById('karaokePlayer');
            if (this.videoElement) {
                // Sync isPlaying state with video
                this.videoElement.addEventListener('play', () => this.isPlaying = true);
                this.videoElement.addEventListener('pause', () => this.isPlaying = false);
            }
        },

        togglePlay() {
            if (!this.videoElement) return;

            if (this.videoElement.paused) {
                this.videoElement.play();
            } else {
                this.videoElement.pause();
            }
        },

        formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        },

        onTimeUpdate(event) {
            const video = event.target;
            if (video.duration) {
                this.progressPercent = (video.currentTime / video.duration) * 100;
                this.currentTimeFormatted = this.formatTime(video.currentTime);
            }
        },

        onMetadataLoaded(event) {
            const video = event.target;
            this.durationFormatted = this.formatTime(video.duration);
        },

        async onVideoEnded() {
            // Auto-play next song
            await this.nextSong();
        },

        seekTo(event) {
            if (!this.videoElement) return;

            const progressBar = event.currentTarget;
            const rect = progressBar.getBoundingClientRect();
            const clickX = event.clientX - rect.left;
            const percent = clickX / rect.width;
            this.videoElement.currentTime = percent * this.videoElement.duration;
        },

        async nextSong() {
            try {
                const response = await fetch('/queue/next', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    this.toast(data.message || 'No more songs');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },

        async previousSong() {
            // TODO: Implement previous song functionality
            this.toast('Previous song not implemented yet');
        },

        async playSong(itemId) {
            try {
                const response = await fetch(`/queue/play/${itemId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    this.toast(data.message || 'Failed to play song');
                }
            } catch (error) {
                console.error('Error:', error);
                this.toast('Failed to play song');
            }
        },

        async removeFromQueue(itemId) {
            try {
                const response = await fetch(`/queue/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await response.json();
                this.toast(data.message || 'Removed from queue');
                window.location.reload();
            } catch (error) {
                console.error('Error:', error);
                this.toast('Failed to remove from queue');
            }
        },

        async moveUp(itemId) {
            try {
                const response = await fetch(`/queue/${itemId}/move-up`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },

        async moveDown(itemId) {
            try {
                const response = await fetch(`/queue/${itemId}/move-down`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },

        async clearQueue() {
            if (!confirm('Clear entire queue?')) return;

            try {
                const response = await fetch('/queue/clear', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await response.json();
                this.toast(data.message || 'Queue cleared');
                window.location.reload();
            } catch (error) {
                console.error('Error:', error);
                this.toast('Failed to clear queue');
            }
        },

        async saveToPlaylist() {
            if (!this.selectedPlaylist) return;

            try {
                const response = await fetch(`/playlists/${this.selectedPlaylist}/add-queue`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await response.json();
                this.toast(data.message || 'Saved to playlist');
            } catch (error) {
                console.error('Error:', error);
                this.toast('Failed to save to playlist');
            }
        },

        async addToPlaylist(songId, playlistId) {
            try {
                const response = await fetch(`/playlists/${playlistId}/add-song`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ song_id: songId }),
                });
                const data = await response.json();
                if (data.success) {
                    this.toast(data.message || 'Added to playlist');
                } else {
                    this.toast(data.message || 'Failed to add to playlist');
                }
            } catch (error) {
                console.error('Playlist error:', error);
                this.toast('Failed to add to playlist');
            }
        },

        toast(message) {
            this.toastMessage = message;
            this.showToast = true;
            setTimeout(() => this.showToast = false, 2000);
        }
    }
}
</script>
@endpush
@endsection
