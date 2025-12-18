@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Playlist Header -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $playlist->name }}</h1>
                @if($playlist->description)
                    <p class="text-gray-600">{{ $playlist->description }}</p>
                @endif
                <div class="flex items-center space-x-4 mt-3 text-sm text-gray-500">
                    <span>{{ $playlist->items->count() }} {{ Str::plural('song', $playlist->items->count()) }}</span>
                    <span>Created {{ $playlist->created_at->diffForHumans() }}</span>
                    <span class="flex items-center">
                        @if($playlist->is_public)
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Public
                        @else
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Private
                        @endif
                    </span>
                </div>
            </div>
            @can('update', $playlist)
                <div class="flex space-x-2">
                    <button onclick="openAddSongModal()" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md text-sm font-medium transition">
                        Add Songs
                    </button>
                    <a href="{{ route('playlists.edit', $playlist) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        Edit Playlist
                    </a>
                </div>
            @endcan
        </div>
    </div>

    @if($playlist->items->isEmpty())
        <!-- Empty State -->
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No songs yet</h3>
            <p class="text-gray-600 mb-6">Start adding songs to your playlist from the library!</p>
            @can('update', $playlist)
                <button onclick="openAddSongModal()" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md transition">
                    Add Songs from Library
                </button>
            @endcan
        </div>
    @else
        <!-- Playlist Items -->
        <div class="bg-white rounded-lg shadow-sm divide-y divide-gray-200" x-data="playlistItems()">
            @foreach($playlist->items as $item)
                <div class="p-4 hover:bg-gray-50 transition flex items-center space-x-4 group">
                    <div class="flex-shrink-0 text-gray-500 w-8">
                        {{ $item->position + 1 }}
                    </div>
                    <!-- Clickable Song Icon - Add to Queue -->
                    <button
                        @click="addToQueue({{ $item->song_id ?? 'null' }})"
                        class="flex-shrink-0 w-12 h-12 bg-green-100 hover:bg-primary-600 rounded flex items-center justify-center transition group/icon cursor-pointer"
                        title="Add to queue"
                        @if(!$item->song_id) disabled @endif
                    >
                        <!-- Music note (default) -->
                        <svg class="w-6 h-6 text-green-600 group-hover/icon:hidden" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 3v9.28c-.47-.17-.97-.28-1.5-.28C8.01 12 6 14.01 6 16.5S8.01 21 10.5 21c2.31 0 4.2-1.75 4.45-4H15V6h4V3h-7z"/>
                        </svg>
                        <!-- Plus icon (on hover) -->
                        <svg class="w-6 h-6 text-white hidden group-hover/icon:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>
                    <!-- Clickable Song Info - Add to Queue -->
                    <button
                        @click="addToQueue({{ $item->song_id ?? 'null' }})"
                        class="flex-1 min-w-0 text-left cursor-pointer hover:text-primary-600 transition"
                        @if(!$item->song_id) disabled @endif
                    >
                        <p class="text-gray-900 font-medium group-hover:text-primary-600 transition">
                            {{ $item->song ? $item->song->title : $item->title }}
                        </p>
                        <p class="text-sm text-gray-500 mt-1">
                            @if($item->song)
                                {{ $item->song->artist ?? 'Unknown Artist' }}
                                @if($item->song->genre)
                                    <span class="mx-1">•</span>
                                    {{ $item->song->genre }}
                                @endif
                            @endif
                            <span class="mx-1">•</span>
                            {{ $item->formatted_duration }}
                        </p>
                    </button>
                    <!-- Add to Queue Button (explicit) -->
                    @if($item->song_id)
                        <button
                            @click="addToQueue({{ $item->song_id }})"
                            class="px-3 py-1.5 bg-gray-100 hover:bg-primary-600 hover:text-white text-gray-700 rounded text-sm font-medium transition opacity-0 group-hover:opacity-100"
                            title="Add to queue"
                        >
                            + Queue
                        </button>
                    @endif
                    @can('update', $playlist)
                        <form method="POST" action="{{ route('playlists.removeSong', [$playlist, $item->id]) }}" onsubmit="return confirm('Remove this song from the playlist?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-gray-400 hover:text-red-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    @endcan
                </div>
            @endforeach

            <!-- Toast notification -->
            <div
                x-show="showToast"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-2"
                class="fixed bottom-4 right-4 bg-gray-800 text-white px-4 py-2 rounded-lg shadow-lg z-50"
                x-text="toastMessage"
            ></div>
        </div>
    @endif
</div>

<!-- Add Song Modal -->
@can('update', $playlist)
<div id="addSongModal" class="fixed inset-0 z-50 hidden" x-data="addSongModal()">
    <div class="fixed inset-0 bg-black/50" onclick="closeAddSongModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[80vh] flex flex-col" onclick="event.stopPropagation()">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b flex justify-between items-center flex-shrink-0">
                <h3 class="text-xl font-semibold text-gray-900">Add Songs to Playlist</h3>
                <button onclick="closeAddSongModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Search Input -->
            <div class="px-6 py-4 border-b flex-shrink-0">
                <div class="relative">
                    <input
                        type="text"
                        id="songSearchInput"
                        placeholder="Search library by title or artist..."
                        class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        oninput="searchSongs(this.value)"
                    >
                    <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <!-- Search Results -->
            <div class="flex-1 overflow-y-auto px-6 py-4" id="songSearchResults">
                <p class="text-gray-500 text-center py-8">Search for songs to add to your playlist</p>
            </div>
        </div>
    </div>
</div>

<script>
    const playlistId = {{ $playlist->id }};
    let searchTimeout;

    function openAddSongModal() {
        document.getElementById('addSongModal').classList.remove('hidden');
        document.getElementById('songSearchInput').focus();
    }

    function closeAddSongModal() {
        document.getElementById('addSongModal').classList.add('hidden');
        document.getElementById('songSearchInput').value = '';
        document.getElementById('songSearchResults').innerHTML = '<p class="text-gray-500 text-center py-8">Search for songs to add to your playlist</p>';
    }

    function searchSongs(query) {
        clearTimeout(searchTimeout);

        if (query.length < 2) {
            document.getElementById('songSearchResults').innerHTML = '<p class="text-gray-500 text-center py-8">Type at least 2 characters to search</p>';
            return;
        }

        document.getElementById('songSearchResults').innerHTML = '<p class="text-gray-500 text-center py-8">Searching...</p>';

        searchTimeout = setTimeout(async () => {
            try {
                const response = await fetch(`/api/songs/search?q=${encodeURIComponent(query)}`);
                const data = await response.json();

                if (data.success && data.data.length > 0) {
                    let html = '<div class="space-y-2">';
                    data.data.forEach(song => {
                        html += `
                            <div class="flex items-center space-x-3 p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                                <div class="w-10 h-10 bg-green-100 rounded flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 3v9.28c-.47-.17-.97-.28-1.5-.28C8.01 12 6 14.01 6 16.5S8.01 21 10.5 21c2.31 0 4.2-1.75 4.45-4H15V6h4V3h-7z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 truncate">${song.title}</p>
                                    <p class="text-sm text-gray-500 truncate">${song.artist || 'Unknown Artist'}</p>
                                </div>
                                <button onclick="addSongToPlaylist(${song.id})" class="px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white rounded text-sm font-medium transition">
                                    Add
                                </button>
                            </div>
                        `;
                    });
                    html += '</div>';
                    document.getElementById('songSearchResults').innerHTML = html;
                } else {
                    document.getElementById('songSearchResults').innerHTML = '<p class="text-gray-500 text-center py-8">No songs found</p>';
                }
            } catch (error) {
                console.error('Search error:', error);
                document.getElementById('songSearchResults').innerHTML = '<p class="text-red-500 text-center py-8">Error searching songs</p>';
            }
        }, 300);
    }

    async function addSongToPlaylist(songId) {
        try {
            const response = await fetch(`/playlists/${playlistId}/add-song`, {
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
                // Reload the page to show the updated playlist
                window.location.reload();
            } else {
                alert(data.message || 'Failed to add song');
            }
        } catch (error) {
            console.error('Error adding song:', error);
            alert('Error adding song to playlist');
        }
    }
</script>
@endcan

<script>
    function playlistItems() {
        return {
            showToast: false,
            toastMessage: '',

            toast(message, duration = 3000) {
                this.toastMessage = message;
                this.showToast = true;
                setTimeout(() => {
                    this.showToast = false;
                }, duration);
            },

            async addToQueue(songId) {
                if (!songId) {
                    this.toast('Cannot add this song to queue');
                    return;
                }

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

                    if (data.success) {
                        this.toast('Added to queue!');
                    } else {
                        this.toast(data.message || 'Failed to add to queue');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.toast('Failed to add to queue');
                }
            }
        };
    }
</script>
@endsection
