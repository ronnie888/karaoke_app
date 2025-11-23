@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<div class="relative bg-dark-900 text-white overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 bg-gradient-to-br from-primary-600/10 to-transparent"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-32">
        <div class="text-center">
            <!-- Hero Title -->
            <div class="flex items-center justify-center mb-6">
                <div class="w-16 h-16 sm:w-20 sm:h-20 bg-primary-600 rounded-lg flex items-center justify-center shadow-lg mr-4">
                    <svg class="w-10 h-10 sm:w-12 sm:h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 3v9.28c-.47-.17-.97-.28-1.5-.28C8.01 12 6 14.01 6 16.5S8.01 21 10.5 21c2.31 0 4.2-1.75 4.45-4H15V6h4V3h-7z"/>
                    </svg>
                </div>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold tracking-tight">
                    <span class="text-white">KARAOKE</span>
                    <span class="text-primary-500"> TUBE</span>
                </h1>
            </div>
            
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-white mb-6">
                Find Your Perfect Karaoke Song
            </h2>
            <p class="text-xl sm:text-2xl text-gray-300 mb-12 max-w-3xl mx-auto">
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
                <a href="{{ route('search') }}?q=karaoke+pop" class="inline-flex items-center px-5 py-2.5 border border-primary-500/50 rounded-full text-sm font-medium text-white bg-primary-600/20 hover:bg-primary-600/40 transition backdrop-blur-sm">
                    🎵 Pop Hits
                </a>
                <a href="{{ route('search') }}?q=karaoke+rock" class="inline-flex items-center px-5 py-2.5 border border-primary-500/50 rounded-full text-sm font-medium text-white bg-primary-600/20 hover:bg-primary-600/40 transition backdrop-blur-sm">
                    🎸 Rock Classics
                </a>
                <a href="{{ route('search') }}?q=karaoke+ballad" class="inline-flex items-center px-5 py-2.5 border border-primary-500/50 rounded-full text-sm font-medium text-white bg-primary-600/20 hover:bg-primary-600/40 transition backdrop-blur-sm">
                    💝 Ballads
                </a>
                <a href="{{ route('search') }}?q=karaoke+country" class="inline-flex items-center px-5 py-2.5 border border-primary-500/50 rounded-full text-sm font-medium text-white bg-primary-600/20 hover:bg-primary-600/40 transition backdrop-blur-sm">
                    🤠 Country
                </a>
                <a href="{{ route('search') }}?q=karaoke+disney" class="inline-flex items-center px-5 py-2.5 border border-primary-500/50 rounded-full text-sm font-medium text-white bg-primary-600/20 hover:bg-primary-600/40 transition backdrop-blur-sm">
                    ✨ Disney Songs
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="bg-dark-850 py-16 border-t border-dark-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="text-center p-6 bg-dark-800 rounded-lg border border-dark-700 hover:border-primary-500/50 transition">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-primary-600/20 text-primary-500 rounded-full mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">Search Millions of Songs</h3>
                <p class="text-gray-400">Access YouTube's vast library of karaoke tracks instantly</p>
            </div>

            <!-- Feature 2 -->
            <div class="text-center p-6 bg-dark-800 rounded-lg border border-dark-700 hover:border-primary-500/50 transition">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-primary-600/20 text-primary-500 rounded-full mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">Play Instantly</h3>
                <p class="text-gray-400">No downloads required. Start singing right away</p>
            </div>

            <!-- Feature 3 -->
            <div class="text-center p-6 bg-dark-800 rounded-lg border border-dark-700 hover:border-primary-500/50 transition">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-primary-600/20 text-primary-500 rounded-full mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-white mb-2">Mobile Friendly</h3>
                <p class="text-gray-400">Works perfectly on all devices and screen sizes</p>
            </div>
        </div>
    </div>
</div>

<!-- Popular Searches -->
<div class="bg-dark-900 border-t border-dark-700 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-white mb-6">🔥 Popular Searches</h2>
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
                    class="inline-flex items-center px-4 py-2 border border-dark-700 rounded-full text-sm text-gray-300 bg-dark-800 hover:bg-dark-700 hover:text-white hover:border-primary-500/50 transition"
                >
                    {{ str_replace(' karaoke', '', $popularSearch) }}
                </a>
            @endforeach
        </div>
    </div>
</div>
@endsection
