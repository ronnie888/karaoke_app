@extends('layouts.app')

@section('content')
<div class="relative bg-gradient-to-br from-primary-500 to-primary-600 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-32">
        <div class="text-center">
            <!-- Hero Title -->
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold tracking-tight mb-6">
                Find Your Perfect Karaoke Song
            </h1>
            <p class="text-xl sm:text-2xl text-primary-100 mb-12 max-w-3xl mx-auto">
                Search millions of karaoke videos from YouTube and start singing now
            </p>

            <!-- Search Bar -->
            <div class="max-w-2xl mx-auto">
                <x-search-bar
                    :query="''"
                    :placeholder="'Search for karaoke songs, artists, or genres...'"
                />
            </div>

            <!-- Quick Links -->
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="{{ route('search') }}?q=karaoke+pop" class="inline-flex items-center px-4 py-2 border border-white/30 rounded-full text-sm font-medium text-white bg-white/10 hover:bg-white/20 transition">
                    Pop Hits
                </a>
                <a href="{{ route('search') }}?q=karaoke+rock" class="inline-flex items-center px-4 py-2 border border-white/30 rounded-full text-sm font-medium text-white bg-white/10 hover:bg-white/20 transition">
                    Rock Classics
                </a>
                <a href="{{ route('search') }}?q=karaoke+ballad" class="inline-flex items-center px-4 py-2 border border-white/30 rounded-full text-sm font-medium text-white bg-white/10 hover:bg-white/20 transition">
                    Ballads
                </a>
                <a href="{{ route('search') }}?q=karaoke+country" class="inline-flex items-center px-4 py-2 border border-white/30 rounded-full text-sm font-medium text-white bg-white/10 hover:bg-white/20 transition">
                    Country
                </a>
                <a href="{{ route('search') }}?q=karaoke+disney" class="inline-flex items-center px-4 py-2 border border-white/30 rounded-full text-sm font-medium text-white bg-white/10 hover:bg-white/20 transition">
                    Disney Songs
                </a>
            </div>
        </div>
    </div>

    <!-- Decorative wave -->
    <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 120" class="w-full h-12 sm:h-24 fill-gray-50">
            <path d="M0,64L48,69.3C96,75,192,85,288,80C384,75,480,53,576,48C672,43,768,53,864,58.7C960,64,1056,64,1152,58.7C1248,53,1344,43,1392,37.3L1440,32L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"></path>
        </svg>
    </div>
</div>

<!-- Features Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Feature 1 -->
        <div class="text-center p-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-primary-100 text-primary-600 rounded-full mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Search Millions of Songs</h3>
            <p class="text-gray-600">Access YouTube's vast library of karaoke tracks instantly</p>
        </div>

        <!-- Feature 2 -->
        <div class="text-center p-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-primary-100 text-primary-600 rounded-full mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Play Instantly</h3>
            <p class="text-gray-600">No downloads required. Start singing right away</p>
        </div>

        <!-- Feature 3 -->
        <div class="text-center p-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-primary-100 text-primary-600 rounded-full mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Mobile Friendly</h3>
            <p class="text-gray-600">Works perfectly on all devices and screen sizes</p>
        </div>
    </div>
</div>

<!-- Popular Searches -->
<div class="bg-white border-t border-gray-200 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Popular Searches</h2>
        <div class="flex flex-wrap gap-2">
            @foreach([
                'Bohemian Rhapsody karaoke',
                'Don\'t Stop Believin\' karaoke',
                'Sweet Caroline karaoke',
                'Livin\' on a Prayer karaoke',
                'I Will Survive karaoke',
                'Total Eclipse of the Heart karaoke',
                'Let It Be karaoke',
                'Hotel California karaoke',
            ] as $popularSearch)
                <a
                    href="{{ route('search') }}?q={{ urlencode($popularSearch) }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-full text-sm text-gray-700 bg-white hover:bg-gray-50 transition"
                >
                    {{ str_replace(' karaoke', '', $popularSearch) }}
                </a>
            @endforeach
        </div>
    </div>
</div>
@endsection
