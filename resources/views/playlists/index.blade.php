@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">My Playlists</h1>
        <a href="{{ route('playlists.create') }}" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md text-sm font-medium transition">
            Create Playlist
        </a>
    </div>

    @if($playlists->isEmpty())
        <!-- Empty State -->
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No playlists yet</h3>
            <p class="text-gray-600 mb-6">Create your first playlist to organize your favorite karaoke songs!</p>
            <a href="{{ route('playlists.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md transition">
                Create Your First Playlist
            </a>
        </div>
    @else
        <!-- Playlists Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($playlists as $playlist)
                <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition overflow-hidden">
                    <a href="{{ route('playlists.show', $playlist) }}" class="block p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $playlist->name }}</h3>
                        @if($playlist->description)
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $playlist->description }}</p>
                        @endif
                        <div class="flex items-center justify-between text-sm text-gray-500">
                            <span>{{ $playlist->items_count }} {{ Str::plural('song', $playlist->items_count) }}</span>
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
                    </a>
                    <div class="border-t border-gray-200 px-6 py-3 flex justify-end space-x-2">
                        <a href="{{ route('playlists.edit', $playlist) }}" class="text-sm text-gray-600 hover:text-gray-900 transition">Edit</a>
                        <form method="POST" action="{{ route('playlists.destroy', $playlist) }}" onsubmit="return confirm('Are you sure you want to delete this playlist?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800 transition">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $playlists->links() }}
        </div>
    @endif
</div>
@endsection
