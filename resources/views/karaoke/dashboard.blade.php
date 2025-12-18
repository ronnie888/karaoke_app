<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Karaoke Tube') }} - Dashboard</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.ts'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('styles')
</head>
<body class="h-full bg-dark-900 text-white antialiased">
    <div class="h-full flex flex-col">
        <!-- Header -->
        <header class="bg-dark-850 border-b border-dark-700 flex-shrink-0">
            <div class="max-w-[1920px] mx-auto px-2 sm:px-4 lg:px-8">
                <div class="flex items-center justify-between h-14 sm:h-16">
                    <!-- Logo -->
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-primary-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 3v9.28c-.47-.17-.97-.28-1.5-.28C8.01 12 6 14.01 6 16.5S8.01 21 10.5 21c2.31 0 4.2-1.75 4.45-4H15V6h4V3h-7z"/>
                            </svg>
                        </div>
                        <span class="text-base sm:text-xl font-bold text-white hidden xs:inline">KARAOKE TUBE</span>
                        <span class="text-base sm:text-xl font-bold text-white xs:hidden">KT</span>
                    </div>

                    <!-- Search Bar (Hidden on very small screens) -->
                    <div class="hidden sm:flex flex-1 max-w-2xl mx-4 md:mx-8" x-data="librarySearchHeader()">
                        <div class="relative w-full">
                            <input
                                type="search"
                                x-model="query"
                                @input.debounce.300ms="search"
                                @focus="showResults = true"
                                @keydown.escape="showResults = false"
                                placeholder="Search library..."
                                class="w-full px-3 sm:px-4 py-2 bg-dark-800 border border-dark-700 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm"
                            />
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg x-show="!searching" class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <svg x-show="searching" class="w-4 h-4 sm:w-5 sm:h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>

                            <!-- Search Results Dropdown -->
                            <div
                                x-show="showResults && (results.length > 0 || (query.length >= 2 && !searching))"
                                @click.away="showResults = false"
                                class="absolute top-full left-0 right-0 mt-2 bg-dark-800 border border-dark-700 rounded-lg shadow-xl z-50 max-h-96 overflow-y-auto"
                            >
                                <template x-if="results.length > 0">
                                    <div class="py-2">
                                        <p class="px-4 py-1 text-xs text-gray-500" x-text="results.length + ' songs found'"></p>
                                        <template x-for="song in results" :key="song.id">
                                            <button
                                                @click="addToQueue(song.id); showResults = false; query = ''"
                                                class="w-full flex items-center space-x-3 px-4 py-2 hover:bg-dark-700 transition text-left"
                                            >
                                                <div class="w-10 h-10 bg-green-900/30 rounded flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 3v9.28c-.47-.17-.97-.28-1.5-.28C8.01 12 6 14.01 6 16.5S8.01 21 10.5 21c2.31 0 4.2-1.75 4.45-4H15V6h4V3h-7z"/>
                                                    </svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-white font-medium truncate text-sm" x-text="song.title"></p>
                                                    <p class="text-gray-400 text-xs truncate" x-text="song.artist || 'Unknown Artist'"></p>
                                                </div>
                                                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                                                </svg>
                                            </button>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="results.length === 0 && query.length >= 2 && !searching">
                                    <div class="px-4 py-3 text-gray-400 text-sm text-center">
                                        No songs found matching "<span x-text="query"></span>"
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="flex items-center space-x-2 sm:space-x-4">
                        <a href="{{ route('home') }}" class="hidden md:inline text-gray-300 hover:text-white transition text-sm">
                            Home
                        </a>
                        <a href="{{ route('playlists.index') }}" class="text-gray-300 hover:text-white transition">
                            My Playlists
                        </a>
                        <a href="{{ route('favorites.index') }}" class="text-gray-300 hover:text-white transition">
                            Favorites
                        </a>
                        <a href="{{ route('history.index') }}" class="text-gray-300 hover:text-white transition">
                            History
                        </a>

                        <!-- User Dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <button
                                @click="open = !open"
                                class="flex items-center space-x-2 text-gray-300 hover:text-white transition"
                            >
                                <span>{{ Auth::user()->name }}</span>
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>

                            <div
                                x-show="open"
                                @click.away="open = false"
                                class="absolute right-0 mt-2 w-48 bg-dark-800 rounded-lg shadow-lg py-1 z-50"
                                style="display: none;"
                            >
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-300 hover:bg-dark-700">
                                    Profile
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-gray-300 hover:bg-dark-700">
                                        Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-auto md:overflow-hidden" x-data="{ mobileView: 'player' }">
            <div class="h-full max-w-[1920px] mx-auto px-2 sm:px-4 lg:px-8 py-3 sm:py-6">

                <!-- Mobile Tab Navigation (visible only on small screens) -->
                <div class="md:hidden flex mb-3 bg-dark-800 rounded-lg p-1">
                    <button
                        @click="mobileView = 'player'"
                        :class="mobileView === 'player' ? 'bg-primary-600 text-white' : 'text-gray-400'"
                        class="flex-1 py-2 px-3 rounded-md text-sm font-medium transition"
                    >
                        Now Playing
                    </button>
                    <button
                        @click="mobileView = 'queue'"
                        :class="mobileView === 'queue' ? 'bg-primary-600 text-white' : 'text-gray-400'"
                        class="flex-1 py-2 px-3 rounded-md text-sm font-medium transition"
                    >
                        Queue
                    </button>
                    <button
                        @click="mobileView = 'browse'"
                        :class="mobileView === 'browse' ? 'bg-primary-600 text-white' : 'text-gray-400'"
                        class="flex-1 py-2 px-3 rounded-md text-sm font-medium transition"
                    >
                        Browse
                    </button>
                </div>

                <!-- 3-Column Grid Layout (Desktop/Tablet) -->
                <div class="hidden md:grid md:grid-cols-2 lg:grid-cols-12 gap-3 sm:gap-4 lg:gap-6 h-full">
                    <!-- Left Column: Now Playing -->
                    <div class="lg:col-span-5 md:col-span-2 h-full">
                        <x-now-playing :current-item="$currentItem" :session-id="$session->id" />
                    </div>

                    <!-- Middle Column: Upcoming Queue -->
                    <div class="lg:col-span-3 md:col-span-1 h-full overflow-hidden">
                        <x-queue-list :queue-items="$queueItems" />
                    </div>

                    <!-- Right Column: Search & Browse -->
                    <div class="lg:col-span-4 md:col-span-1 h-full overflow-hidden">
                        <x-tabbed-browse
                            :popular-songs="$popularSongs"
                            :favorites="$favorites"
                            :current-video-id="$currentItem?->video_id"
                            :library-songs="$librarySongs"
                            :library-songs-count="$librarySongsCount"
                        />
                    </div>
                </div>

                <!-- Mobile Single View -->
                <div class="md:hidden">
                    <!-- Now Playing (Mobile) -->
                    <div x-show="mobileView === 'player'" class="h-[calc(100vh-180px)]">
                        <x-now-playing :current-item="$currentItem" :session-id="$session->id" />
                    </div>

                    <!-- Queue (Mobile) -->
                    <div x-show="mobileView === 'queue'" class="h-[calc(100vh-180px)]">
                        <x-queue-list :queue-items="$queueItems" />
                    </div>

                    <!-- Browse (Mobile) -->
                    <div x-show="mobileView === 'browse'" class="h-[calc(100vh-180px)]">
                        <x-tabbed-browse
                            :popular-songs="$popularSongs"
                            :favorites="$favorites"
                            :current-video-id="$currentItem?->video_id"
                            :library-songs="$librarySongs"
                            :library-songs-count="$librarySongsCount"
                        />
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Toast Notifications -->
    <x-toast-container />

    <!-- Library Search Header Script -->
    <script>
    function librarySearchHeader() {
        return {
            query: '',
            results: [],
            searching: false,
            showResults: false,

            async search() {
                if (this.query.length < 2) {
                    this.results = [];
                    return;
                }

                this.searching = true;

                try {
                    const response = await fetch(`/api/songs/search?q=${encodeURIComponent(this.query)}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await response.json();

                    if (data.success) {
                        this.results = data.data;
                        this.showResults = true;
                    } else {
                        this.results = [];
                    }
                } catch (error) {
                    console.error('Error searching library:', error);
                    this.results = [];
                } finally {
                    this.searching = false;
                }
            },

            async addToQueue(songId) {
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
                        if (window.showToast) {
                            window.showToast('Song added to queue!', 'success');
                        }

                        if (window.queueManager) {
                            await window.queueManager.fetchQueue();
                            window.queueManager.refreshQueueDisplay();
                        }

                        if (data.auto_played) {
                            setTimeout(() => window.location.reload(), 500);
                        }
                    } else {
                        if (window.showToast) {
                            window.showToast('Failed to add song', 'error');
                        }
                    }
                } catch (error) {
                    console.error('Error adding song to queue:', error);
                    if (window.showToast) {
                        window.showToast('Error adding song', 'error');
                    }
                }
            }
        };
    }
    </script>

    @stack('scripts')
</body>
</html>

