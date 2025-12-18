@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pb-24 md:pb-8" x-data="libraryApp()">
    <!-- Header with Search (top-14 on mobile for mobile header, top-16 on desktop below fixed nav) -->
    <div class="sticky top-14 md:top-16 z-40 bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <!-- Search Bar -->
            <div class="relative">
                <input
                    type="text"
                    x-model="searchQuery"
                    @input.debounce.300ms="liveSearch()"
                    placeholder="Search {{ $totalSongs }} songs by title or artist..."
                    class="w-full pl-10 pr-4 py-3 text-base border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-gray-50"
                >
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <button x-show="searchQuery" @click="clearSearch()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Genre Filter Chips -->
            <div class="mt-3 flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
                <button
                    @click="setGenre('all')"
                    :class="currentGenre === 'all' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="px-4 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition"
                >
                    All
                </button>
                @foreach($genres as $genre)
                    <button
                        @click="setGenre('{{ $genre }}')"
                        :class="currentGenre === '{{ $genre }}' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        class="px-4 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition"
                    >
                        {{ $genre }}
                    </button>
                @endforeach
            </div>

            <!-- Sort Options -->
            <div class="mt-2 flex items-center justify-between text-sm">
                <span class="text-gray-500" x-text="songCountText"></span>
                <select x-model="sortBy" @change="applyFilters()" class="text-sm border-0 bg-transparent text-gray-600 font-medium focus:ring-0 cursor-pointer">
                    <option value="popular">Most Played</option>
                    <option value="az">A-Z</option>
                    <option value="recent">Recently Added</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Song List -->
    <div class="max-w-7xl mx-auto px-4 py-4">
        <!-- Live Search Results -->
        <div x-show="isSearching" class="space-y-2">
            <template x-for="song in searchResults" :key="song.id">
                <div class="bg-white rounded-lg shadow-sm p-3 flex items-center gap-3 hover:shadow-md transition">
                    <div class="w-12 h-12 bg-gradient-to-br from-primary-400 to-primary-600 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 3v9.28c-.47-.17-.97-.28-1.5-.28C8.01 12 6 14.01 6 16.5S8.01 21 10.5 21c2.31 0 4.2-1.75 4.45-4H15V6h4V3h-7z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 truncate" x-text="song.title"></p>
                        <p class="text-sm text-gray-500 truncate">
                            <span x-text="song.artist || 'Unknown Artist'"></span>
                            <span class="mx-1" x-show="song.genre">-</span>
                            <span x-text="song.genre" x-show="song.genre"></span>
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400" x-text="song.formatted_duration"></span>
                        <!-- Dropdown Menu for Search Results -->
                        <div x-data="{ openMenu: false }" class="relative">
                            <button @click="openMenu = !openMenu" class="p-2 bg-primary-600 hover:bg-primary-700 text-white rounded-full transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </button>
                            <div x-show="openMenu" @click.away="openMenu = false" x-transition
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border z-50 py-1">
                                <button @click="addToQueue(song.id); openMenu = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Add to Queue
                                </button>
                                <div class="border-t border-gray-100 my-1"></div>
                                <p class="px-4 py-1 text-xs text-gray-400 uppercase tracking-wide">Add to Playlist</p>
                                @if($playlists->count() > 0)
                                    @foreach($playlists as $playlist)
                                        <button @click="addToPlaylist(song.id, {{ $playlist->id }}); openMenu = false"
                                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                            </svg>
                                            {{ $playlist->name }}
                                        </button>
                                    @endforeach
                                @else
                                    <p class="px-4 py-2 text-xs text-gray-400 italic">No playlists yet</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <p x-show="searchResults.length === 0 && searchQuery.length >= 2" class="text-center text-gray-500 py-8">
                No songs found for "<span x-text="searchQuery"></span>"
            </p>
        </div>

        <!-- Paginated Song List -->
        <div x-show="!isSearching" class="space-y-2">
            @forelse($songs as $song)
                <div class="bg-white rounded-lg shadow-sm p-3 flex items-center gap-3 hover:shadow-md transition group">
                    <div class="w-12 h-12 bg-gradient-to-br from-primary-400 to-primary-600 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 3v9.28c-.47-.17-.97-.28-1.5-.28C8.01 12 6 14.01 6 16.5S8.01 21 10.5 21c2.31 0 4.2-1.75 4.45-4H15V6h4V3h-7z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 truncate">{{ $song->title }}</p>
                        <p class="text-sm text-gray-500 truncate">
                            {{ $song->artist ?? 'Unknown Artist' }}
                            @if($song->genre)
                                <span class="mx-1">-</span>
                                {{ $song->genre }}
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400">{{ $song->formatted_duration }}</span>
                        <!-- Dropdown Menu -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="p-2 bg-primary-600 hover:bg-primary-700 text-white rounded-full transition opacity-70 group-hover:opacity-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </button>
                            <!-- Dropdown Content -->
                            <div x-show="open" @click.away="open = false" x-transition
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border z-50 py-1">
                                <!-- Add to Queue -->
                                <button @click="addToQueue({{ $song->id }}); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Add to Queue
                                </button>

                                <div class="border-t border-gray-100 my-1"></div>
                                <p class="px-4 py-1 text-xs text-gray-400 uppercase tracking-wide">Add to Playlist</p>
                                @if($playlists->count() > 0)
                                    @foreach($playlists as $playlist)
                                        <button @click="addToPlaylist({{ $song->id }}, {{ $playlist->id }}); open = false"
                                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                            </svg>
                                            {{ $playlist->name }}
                                        </button>
                                    @endforeach
                                @else
                                    <p class="px-4 py-2 text-xs text-gray-400 italic">No playlists yet</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                    </svg>
                    <p class="mt-4 text-gray-500">No songs found</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($songs->hasPages() && !request('search'))
            <div class="mt-6">
                {{ $songs->links() }}
            </div>
        @endif
    </div>

    <!-- Mini Player (Fixed Bottom) -->
    @if($currentItem)
        <div class="fixed bottom-16 md:bottom-0 left-0 right-0 bg-white border-t shadow-lg z-30">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center gap-3">
                <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-white animate-pulse" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 3v9.28c-.47-.17-.97-.28-1.5-.28C8.01 12 6 14.01 6 16.5S8.01 21 10.5 21c2.31 0 4.2-1.75 4.45-4H15V6h4V3h-7z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-900 truncate">
                        {{ $currentItem->song?->title ?? $currentItem->title ?? 'Unknown' }}
                    </p>
                    <p class="text-sm text-gray-500 truncate">
                        {{ $currentItem->song?->artist ?? 'Unknown Artist' }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    @if($queueCount > 0)
                        <span class="text-xs bg-primary-100 text-primary-700 px-2 py-1 rounded-full font-medium">
                            {{ $queueCount }} in queue
                        </span>
                    @endif
                    <a href="{{ route('library.playing') }}" class="p-2 bg-primary-600 hover:bg-primary-700 text-white rounded-full transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Mobile Bottom Navigation -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg z-40">
        <div class="flex justify-around items-center h-16">
            <a href="{{ route('library') }}" class="flex flex-col items-center justify-center flex-1 py-2 text-primary-600">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 3v9.28c-.47-.17-.97-.28-1.5-.28C8.01 12 6 14.01 6 16.5S8.01 21 10.5 21c2.31 0 4.2-1.75 4.45-4H15V6h4V3h-7z"/>
                </svg>
                <span class="text-xs mt-1 font-medium">Library</span>
            </a>
            <a href="{{ route('library.playing') }}" class="flex flex-col items-center justify-center flex-1 py-2 text-gray-500 hover:text-primary-600 transition relative">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-xs mt-1">Playing</span>
                @if($queueCount > 0)
                    <span class="absolute top-1 right-1/4 w-5 h-5 bg-primary-600 text-white text-xs rounded-full flex items-center justify-center">
                        {{ $queueCount > 9 ? '9+' : $queueCount }}
                    </span>
                @endif
            </a>
            <a href="{{ route('playlists.index') }}" class="flex flex-col items-center justify-center flex-1 py-2 text-gray-500 hover:text-primary-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <span class="text-xs mt-1">Playlists</span>
            </a>
            <a href="{{ route('profile.edit') }}" class="flex flex-col items-center justify-center flex-1 py-2 text-gray-500 hover:text-primary-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="text-xs mt-1">Profile</span>
            </a>
        </div>
    </nav>

    <!-- Toast Notification -->
    <div x-show="showToast" x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-32 md:bottom-24 left-1/2 -translate-x-1/2 bg-gray-900 text-white px-4 py-2 rounded-lg shadow-lg z-50">
        <span x-text="toastMessage"></span>
    </div>
</div>

@push('scripts')
<script>
function libraryApp() {
    return {
        searchQuery: '{{ request('search', '') }}',
        currentGenre: '{{ request('genre', 'all') }}',
        sortBy: '{{ request('sort', 'popular') }}',
        searchResults: [],
        isSearching: false,
        showToast: false,
        toastMessage: '',
        totalSongs: {{ $totalSongs }},
        displayedSongs: {{ $songs->total() }},

        get songCountText() {
            if (this.isSearching) {
                return `${this.searchResults.length} results`;
            }
            return `${this.displayedSongs} of ${this.totalSongs} songs`;
        },

        setGenre(genre) {
            this.currentGenre = genre;
            this.applyFilters();
        },

        applyFilters() {
            const params = new URLSearchParams();
            if (this.currentGenre !== 'all') params.set('genre', this.currentGenre);
            if (this.sortBy !== 'popular') params.set('sort', this.sortBy);
            window.location.href = '{{ route('library') }}?' + params.toString();
        },

        async liveSearch() {
            if (this.searchQuery.length < 2) {
                this.isSearching = false;
                this.searchResults = [];
                return;
            }

            this.isSearching = true;

            try {
                const response = await fetch(`/library/search?q=${encodeURIComponent(this.searchQuery)}`);
                const data = await response.json();
                if (data.success) {
                    this.searchResults = data.data;
                }
            } catch (error) {
                console.error('Search error:', error);
            }
        },

        clearSearch() {
            this.searchQuery = '';
            this.isSearching = false;
            this.searchResults = [];
        },

        async addToQueue(songId) {
            try {
                const response = await fetch('/queue/add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ song_id: songId }),
                });

                const data = await response.json();
                this.toast(data.message || 'Added to queue');

                // Refresh page if this was the first item (to show mini-player)
                if (data.queueCount === 1) {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Queue error:', error);
                this.toast('Failed to add to queue');
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

<style>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
@endsection
