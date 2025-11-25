@props(['popularSongs', 'favorites', 'currentVideoId' => null])

<div class="bg-dark-850 rounded-lg overflow-hidden h-full flex flex-col" x-data="{ activeTab: 'popular', loading: false, songs: [], currentVideoId: '{{ $currentVideoId }}' }">
    <!-- Header with Tabs -->
    <div class="px-6 py-4 border-b border-dark-700 flex-shrink-0">
        <h2 class="text-xl font-bold text-white mb-4">Search & Browsing</h2>

        <!-- Tabs -->
        <div class="flex space-x-2">
            <button
                @click="activeTab = 'top3'; loadTop3(currentVideoId)"
                :class="activeTab === 'top3' ? 'bg-red-600 text-white' : 'bg-dark-700 text-gray-300 hover:bg-dark-600'"
                class="px-4 py-2 rounded-lg font-medium transition"
                :disabled="!currentVideoId"
                :title="!currentVideoId ? 'Play a song to see recommendations' : 'Top 3 recommendations'"
            >
                TOP 3
            </button>
            <button
                @click="activeTab = 'popular'"
                :class="activeTab === 'popular' ? 'bg-primary-600 text-white' : 'bg-dark-700 text-gray-300 hover:bg-dark-600'"
                class="px-4 py-2 rounded-lg font-medium transition"
            >
                Popular
            </button>
            <button
                @click="activeTab = 'trending'; loadTrending()"
                :class="activeTab === 'trending' ? 'bg-primary-600 text-white' : 'bg-dark-700 text-gray-300 hover:bg-dark-600'"
                class="px-4 py-2 rounded-lg font-medium transition"
            >
                Trending
            </button>
            <button
                @click="activeTab = 'genre'"
                :class="activeTab === 'genre' ? 'bg-primary-600 text-white' : 'bg-dark-700 text-gray-300 hover:bg-dark-600'"
                class="px-4 py-2 rounded-lg font-medium transition"
            >
                By Genre
            </button>
            <button
                @click="activeTab = 'favorites'"
                :class="activeTab === 'favorites' ? 'bg-primary-600 text-white' : 'bg-dark-700 text-gray-300 hover:bg-dark-600'"
                class="px-4 py-2 rounded-lg font-medium transition"
            >
                Favorites
            </button>
        </div>
    </div>

    <!-- Content Area -->
    <div class="flex-1 overflow-y-auto custom-scrollbar">
        <!-- TOP 3 Tab -->
        <div x-show="activeTab === 'top3'" class="p-4">
            <div x-show="!currentVideoId" class="py-8 text-center text-gray-400">
                <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                </svg>
                <p class="font-medium">No song playing</p>
                <p class="text-sm mt-2">Play a song to see TOP 3 recommendations!</p>
            </div>

            <div x-show="loading && currentVideoId" class="py-8 text-center">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-red-500"></div>
                <p class="text-gray-400 mt-2">Loading TOP 3 recommendations...</p>
            </div>

            <div x-show="!loading && songs.length > 0" class="space-y-4">
                <template x-for="(song, index) in songs" :key="song.id">
                    <div class="flex items-start space-x-4 p-4 bg-dark-800 hover:bg-dark-700 rounded-lg transition border-l-4 border-red-600">
                        <div class="flex-shrink-0 w-12 h-12 bg-red-600 text-white rounded-full flex items-center justify-center text-xl font-bold">
                            <span x-text="index + 1"></span>
                        </div>
                        <img :src="song.thumbnail" :alt="song.title" class="w-24 h-18 object-cover rounded flex-shrink-0" />
                        <div class="flex-1 min-w-0">
                            <h4 class="text-white font-semibold text-lg truncate" x-text="song.title"></h4>
                            <p class="text-sm text-gray-400 truncate" x-text="song.channel.title"></p>
                        </div>
                        <button
                            @click="addToQueue(song)"
                            class="p-3 rounded-full bg-red-600 hover:bg-red-700 text-white transition flex-shrink-0"
                            title="Add to Queue"
                        >
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>

            <div x-show="!loading && songs.length === 0 && currentVideoId" class="py-8 text-center text-gray-400">
                <p>No recommendations found</p>
            </div>
        </div>

        <!-- Popular Songs Tab -->
        <div x-show="activeTab === 'popular'" class="p-4 space-y-2">
            @forelse($popularSongs as $song)
                <x-browse-song-item :song="$song" />
            @empty
                <div class="py-8 text-center text-gray-400">
                    <p>No popular songs available</p>
                </div>
            @endforelse
        </div>

        <!-- Trending Songs Tab -->
        <div x-show="activeTab === 'trending'" class="p-4">
            <div x-show="loading" class="py-8 text-center">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500"></div>
                <p class="text-gray-400 mt-2">Loading trending songs...</p>
            </div>

            <div x-show="!loading && songs.length > 0" class="space-y-2">
                <template x-for="song in songs" :key="song.id">
                    <div class="flex items-center space-x-3 p-3 bg-dark-800 hover:bg-dark-700 rounded-lg transition">
                        <img :src="song.thumbnail" :alt="song.title" class="w-16 h-12 object-cover rounded" />
                        <div class="flex-1 min-w-0">
                            <h4 class="text-white font-medium truncate" x-text="song.title"></h4>
                            <p class="text-sm text-gray-400 truncate" x-text="song.channel.title"></p>
                        </div>
                        <button
                            @click="addToQueue(song)"
                            class="p-2 rounded-full bg-primary-600 hover:bg-primary-700 text-white transition"
                            title="Add to Queue"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Genre Tab -->
        <div x-show="activeTab === 'genre'" class="p-4">
            <div class="grid grid-cols-2 gap-3">
                @foreach(['Pop', 'Rock', 'Ballad', 'Country', 'R&B', 'Hip Hop', 'Jazz', 'Disney', 'Classic', 'K-Pop', 'Latin', 'Anime'] as $genre)
                    <button
                        @click="loadGenre('{{ $genre }}')"
                        class="px-4 py-3 bg-dark-800 hover:bg-dark-700 text-white rounded-lg transition font-medium text-center"
                    >
                        {{ $genre }}
                    </button>
                @endforeach
            </div>

            <div x-show="loading" class="py-8 text-center mt-4">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500"></div>
                <p class="text-gray-400 mt-2">Loading genre songs...</p>
            </div>

            <div x-show="!loading && songs.length > 0" class="mt-4 space-y-2">
                <template x-for="song in songs" :key="song.id">
                    <div class="flex items-center space-x-3 p-3 bg-dark-800 hover:bg-dark-700 rounded-lg transition">
                        <img :src="song.thumbnail" :alt="song.title" class="w-16 h-12 object-cover rounded" />
                        <div class="flex-1 min-w-0">
                            <h4 class="text-white font-medium truncate" x-text="song.title"></h4>
                            <p class="text-sm text-gray-400 truncate" x-text="song.channel.title"></p>
                        </div>
                        <button
                            @click="addToQueue(song)"
                            class="p-2 rounded-full bg-primary-600 hover:bg-primary-700 text-white transition"
                            title="Add to Queue"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                            </svg>
                        </button>
                    </div>
                </template>
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
async function loadTrending() {
    const app = Alpine.$data(document.querySelector('[x-data]'));
    app.loading = true;
    app.songs = [];

    try {
        const response = await fetch('/dashboard/trending');
        const data = await response.json();

        if (data.success) {
            app.songs = data.data;
        }
    } catch (error) {
        console.error('Error loading trending:', error);
    } finally {
        app.loading = false;
    }
}

async function loadGenre(genre) {
    const app = Alpine.$data(document.querySelector('[x-data]'));
    app.loading = true;
    app.songs = [];

    try {
        const response = await fetch(`/dashboard/genre/${encodeURIComponent(genre)}`);
        const data = await response.json();

        if (data.success) {
            app.songs = data.data;
        }
    } catch (error) {
        console.error('Error loading genre:', error);
    } finally {
        app.loading = false;
    }
}

async function addToQueue(song) {
    await window.queueManager.addToQueue({
        video_id: song.id,
        title: song.title,
        thumbnail: song.thumbnail,
        channel_title: song.channel?.title || '',
        duration: song.duration || null,
    });
}

async function addFavoriteToQueue(videoId, title, thumbnail) {
    await window.queueManager.addToQueue({
        video_id: videoId,
        title: title,
        thumbnail: thumbnail,
        channel_title: '',
        duration: null,
    });
}

async function loadTop3(videoId) {
    if (!videoId) {
        console.warn('No video ID provided for TOP 3');
        return;
    }

    const app = Alpine.$data(document.querySelector('[x-data]'));
    app.loading = true;
    app.songs = [];

    try {
        const response = await fetch(`/dashboard/top3/${encodeURIComponent(videoId)}`);
        const data = await response.json();

        if (data.success) {
            app.songs = data.data;
        }
    } catch (error) {
        console.error('Error loading TOP 3:', error);
    } finally {
        app.loading = false;
    }
}
</script>
@endpush
