@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-dark-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Search Bar -->
        <div class="mb-8">
            <x-search-bar :query="$query" />
        </div>

        <!-- Results Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">
                    Search Results
                    @if($query)
                        for "<span class="text-primary-500">{{ $query }}</span>"
                    @endif
                </h1>
                <p class="mt-1 text-sm text-gray-400">
                    {{ $total }} {{ Str::plural('result', $total) }} found
                </p>
            </div>

            <!-- Sort Dropdown (Mobile) -->
            <div class="md:hidden">
                <select
                    onchange="window.location.href = this.value"
                    class="block w-full pl-3 pr-10 py-2 text-base bg-dark-800 border-dark-700 text-white focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md"
                >
                    <option value="{{ route('search') }}?q={{ $query }}&order=relevance" {{ $order === 'relevance' ? 'selected' : '' }}>Relevance</option>
                    <option value="{{ route('search') }}?q={{ $query }}&order=date" {{ $order === 'date' ? 'selected' : '' }}>Upload Date</option>
                    <option value="{{ route('search') }}?q={{ $query }}&order=viewCount" {{ $order === 'viewCount' ? 'selected' : '' }}>View Count</option>
                    <option value="{{ route('search') }}?q={{ $query }}&order=rating" {{ $order === 'rating' ? 'selected' : '' }}>Rating</option>
                </select>
            </div>
        </div>

        <!-- Filters Button (Desktop) -->
        <div class="hidden md:flex items-center justify-between mb-6">
            <div class="flex items-center space-x-4">
                <button
                    onclick="toggleFilters()"
                    class="inline-flex items-center px-4 py-2 bg-dark-800 border border-dark-700 rounded-lg text-sm font-medium text-gray-300 hover:bg-dark-700 hover:text-white transition"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filters
                </button>
            </div>

            <!-- Sort Options (Desktop) -->
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-400">Sort by:</span>
                <a href="{{ route('search') }}?q={{ $query }}&order=relevance" class="px-3 py-1.5 text-sm rounded-md {{ $order === 'relevance' ? 'bg-primary-600 text-white' : 'bg-dark-800 text-gray-300 hover:bg-dark-700' }} transition">
                    Relevance
                </a>
                <a href="{{ route('search') }}?q={{ $query }}&order=date" class="px-3 py-1.5 text-sm rounded-md {{ $order === 'date' ? 'bg-primary-600 text-white' : 'bg-dark-800 text-gray-300 hover:bg-dark-700' }} transition">
                    Upload Date
                </a>
                <a href="{{ route('search') }}?q={{ $query }}&order=viewCount" class="px-3 py-1.5 text-sm rounded-md {{ $order === 'viewCount' ? 'bg-primary-600 text-white' : 'bg-dark-800 text-gray-300 hover:bg-dark-700' }} transition">
                    View Count
                </a>
                <a href="{{ route('search') }}?q={{ $query }}&order=rating" class="px-3 py-1.5 text-sm rounded-md {{ $order === 'rating' ? 'bg-primary-600 text-white' : 'bg-dark-800 text-gray-300 hover:bg-dark-700' }} transition">
                    Rating
                </a>
            </div>
        </div>

        <!-- Results Grid -->
        @if($results->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($results as $video)
                    <x-video-card :video="$video" />
                @endforeach
            </div>

            <!-- Load More / Pagination Placeholder -->
            <div class="mt-12 text-center">
                <p class="text-sm text-gray-500">
                    Showing {{ $total }} of {{ $total }} results
                </p>
                <!-- Future: Add pagination when implementing page tokens -->
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16">
                <svg class="mx-auto h-24 w-24 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-white">No results found</h3>
                <p class="mt-2 text-sm text-gray-400 max-w-md mx-auto">
                    We couldn't find any videos matching "{{ $query }}". Try different keywords or check your spelling.
                </p>
                <div class="mt-6">
                    <a
                        href="{{ route('home') }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Home
                    </a>
                </div>

                <!-- Popular Suggestions -->
                <div class="mt-8">
                    <p class="text-sm font-medium text-gray-300 mb-3">Try these popular searches:</p>
                    <div class="flex flex-wrap justify-center gap-2">
                        @foreach(['Bohemian Rhapsody', 'Sweet Caroline', 'Don\'t Stop Believin\'', 'Total Eclipse of the Heart'] as $suggestion)
                            <a
                                href="{{ route('search') }}?q={{ urlencode($suggestion . ' karaoke') }}"
                                class="inline-flex items-center px-3 py-1.5 border border-dark-700 rounded-full text-xs font-medium text-gray-300 bg-dark-800 hover:bg-dark-700 hover:text-white transition"
                            >
                                {{ $suggestion }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// Keyboard shortcut: / to focus search
document.addEventListener('keydown', (e) => {
    if (e.key === '/' && document.activeElement.tagName !== 'INPUT') {
        e.preventDefault();
        document.querySelector('input[name="q"]')?.focus();
    }
});

function toggleFilters() {
    // Placeholder for filters panel
    alert('Filters coming soon!');
}
</script>
@endpush
