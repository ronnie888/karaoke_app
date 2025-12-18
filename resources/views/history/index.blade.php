@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-24 md:pb-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Play History</h1>
        @if($history->isNotEmpty())
            <form method="POST" action="{{ route('history.destroy') }}" onsubmit="return confirm('Are you sure you want to clear your entire play history?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm text-red-600 hover:text-red-800 transition">
                    Clear History
                </button>
            </form>
        @endif
    </div>

    @if($history->isEmpty())
        <!-- Empty State -->
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No play history</h3>
            <p class="text-gray-600 mb-6">Songs you play will appear here!</p>
            <a href="{{ route('library') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md transition">
                Browse Library
            </a>
        </div>
    @else
        <!-- History List -->
        <div class="bg-white rounded-lg shadow-sm divide-y divide-gray-200">
            @foreach($history as $item)
                <div class="p-4 hover:bg-gray-50 transition flex items-center gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-gray-400 to-gray-600 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-gray-900 font-medium truncate">{{ $item->title }}</p>
                        <p class="text-sm text-gray-500">
                            Played {{ $item->watched_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $history->links() }}
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
