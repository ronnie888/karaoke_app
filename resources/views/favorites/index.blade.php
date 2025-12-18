@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-24 md:pb-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">My Favorites</h1>

    @if($favorites->isEmpty())
        <!-- Empty State -->
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No favorites yet</h3>
            <p class="text-gray-600 mb-6">Browse the library and favorite your best karaoke songs!</p>
            <a href="{{ route('library') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md transition">
                Browse Library
            </a>
        </div>
    @else
        <!-- Favorites List -->
        <div class="bg-white rounded-lg shadow-sm divide-y divide-gray-200">
            @foreach($favorites as $favorite)
                <div class="p-4 hover:bg-gray-50 transition flex items-center gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-red-400 to-red-600 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 truncate">{{ $favorite->title }}</p>
                        <p class="text-sm text-gray-500">
                            Added {{ $favorite->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <form method="POST" action="{{ route('favorites.destroy', $favorite->video_id) }}" onsubmit="return confirm('Remove from favorites?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 text-gray-400 hover:text-red-600 transition" title="Remove from favorites">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $favorites->links() }}
        </div>
    @endif
</div>

<!-- Mobile Bottom Navigation -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg z-40">
    <div class="flex justify-around items-center h-16">
        <a href="{{ route('library') }}" class="flex flex-col items-center justify-center flex-1 py-2 text-gray-500 hover:text-primary-600 transition">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 3v9.28c-.47-.17-.97-.28-1.5-.28C8.01 12 6 14.01 6 16.5S8.01 21 10.5 21c2.31 0 4.2-1.75 4.45-4H15V6h4V3h-7z"/>
            </svg>
            <span class="text-xs mt-1">Library</span>
        </a>
        <a href="{{ route('library.playing') }}" class="flex flex-col items-center justify-center flex-1 py-2 text-gray-500 hover:text-primary-600 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-xs mt-1">Playing</span>
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
@endsection
