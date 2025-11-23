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
                    <span>{{ $playlist->items->count() }} {{ Str::plural('video', $playlist->items->count()) }}</span>
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
            <h3 class="text-lg font-medium text-gray-900 mb-2">No videos yet</h3>
            <p class="text-gray-600 mb-6">Start adding videos to your playlist from the search page!</p>
            <a href="{{ route('search') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md transition">
                Search for Videos
            </a>
        </div>
    @else
        <!-- Playlist Items -->
        <div class="bg-white rounded-lg shadow-sm divide-y divide-gray-200">
            @foreach($playlist->items as $item)
                <div class="p-4 hover:bg-gray-50 transition flex items-center space-x-4">
                    <div class="flex-shrink-0 text-gray-500 w-8">
                        {{ $item->position + 1 }}
                    </div>
                    <a href="{{ route('watch', $item->video_id) }}" class="flex-shrink-0">
                        <img src="{{ $item->thumbnail }}" alt="{{ $item->title }}" class="w-32 h-20 object-cover rounded">
                    </a>
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('watch', $item->video_id) }}" class="text-gray-900 font-medium hover:text-primary-600 transition">
                            {{ $item->title }}
                        </a>
                        <p class="text-sm text-gray-500 mt-1">
                            @if($item->duration)
                                Duration: {{ $item->formattedDuration }}
                            @endif
                        </p>
                    </div>
                    @can('update', $playlist)
                        <form method="POST" action="{{ route('playlists.removeVideo', [$playlist, $item->id]) }}" onsubmit="return confirm('Remove this video from the playlist?')">
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
        </div>
    @endif
</div>
@endsection
