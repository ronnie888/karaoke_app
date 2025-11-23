@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Watch History</h1>
        @if($history->isNotEmpty())
            <form method="POST" action="{{ route('history.destroy') }}" onsubmit="return confirm('Are you sure you want to clear your entire watch history?')">
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
            <h3 class="text-lg font-medium text-gray-900 mb-2">No watch history</h3>
            <p class="text-gray-600 mb-6">Videos you watch will appear here!</p>
            <a href="{{ route('search') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md transition">
                Search for Videos
            </a>
        </div>
    @else
        <!-- History List -->
        <div class="bg-white rounded-lg shadow-sm divide-y divide-gray-200">
            @foreach($history as $item)
                <div class="p-4 hover:bg-gray-50 transition flex items-center space-x-4">
                    <a href="{{ route('watch', $item->video_id) }}" class="flex-shrink-0">
                        <img src="{{ $item->thumbnail }}" alt="{{ $item->title }}" class="w-40 h-24 object-cover rounded">
                    </a>
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('watch', $item->video_id) }}" class="text-gray-900 font-medium hover:text-primary-600 transition block">
                            {{ $item->title }}
                        </a>
                        <p class="text-sm text-gray-500 mt-1">
                            Watched {{ $item->watched_at->diffForHumans() }}
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
@endsection
