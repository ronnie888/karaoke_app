@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">My Favorites</h1>

    @if($favorites->isEmpty())
        <!-- Empty State -->
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No favorites yet</h3>
            <p class="text-gray-600 mb-6">Start favoriting videos to build your collection!</p>
            <a href="{{ route('search') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md transition">
                Search for Videos
            </a>
        </div>
    @else
        <!-- Favorites Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($favorites as $favorite)
                <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition overflow-hidden">
                    <a href="{{ route('watch', $favorite->video_id) }}" class="block">
                        <div class="relative aspect-video bg-gray-200">
                            <img src="{{ $favorite->thumbnail }}" alt="{{ $favorite->title }}" loading="lazy" class="w-full h-full object-cover">
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 line-clamp-2 hover:text-primary-500 transition">
                                {{ $favorite->title }}
                            </h3>
                            <p class="text-xs text-gray-500 mt-2">
                                Added {{ $favorite->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </a>
                    <div class="border-t border-gray-200 px-4 py-2">
                        <form method="POST" action="{{ route('favorites.destroy', $favorite->video_id) }}" onsubmit="return confirm('Remove from favorites?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 hover:text-red-800 transition flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                </svg>
                                Remove from Favorites
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $favorites->links() }}
        </div>
    @endif
</div>
@endsection
