<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Karaoke Tube') }} - Dashboard</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.ts'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('styles')
</head>
<body class="h-full bg-dark-900 text-white antialiased">
    <div class="h-full flex flex-col">
        <!-- Header -->
        <header class="bg-dark-850 border-b border-dark-700 flex-shrink-0">
            <div class="max-w-[1920px] mx-auto px-2 sm:px-4 lg:px-8">
                <div class="flex items-center justify-between h-14 sm:h-16">
                    <!-- Logo -->
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-primary-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 3v9.28c-.47-.17-.97-.28-1.5-.28C8.01 12 6 14.01 6 16.5S8.01 21 10.5 21c2.31 0 4.2-1.75 4.45-4H15V6h4V3h-7z"/>
                            </svg>
                        </div>
                        <span class="text-base sm:text-xl font-bold text-white hidden xs:inline">KARAOKE TUBE</span>
                        <span class="text-base sm:text-xl font-bold text-white xs:hidden">KT</span>
                    </div>

                    <!-- Search Bar (Hidden on very small screens) -->
                    <div class="hidden sm:flex flex-1 max-w-2xl mx-4 md:mx-8">
                        <form action="{{ route('search') }}" method="GET" class="relative w-full">
                            <input
                                type="search"
                                name="q"
                                placeholder="Search..."
                                class="w-full px-3 sm:px-4 py-2 bg-dark-800 border border-dark-700 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm"
                            />
                            <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </button>
                        </form>
                    </div>

                    <!-- User Menu -->
                    <div class="flex items-center space-x-2 sm:space-x-4">
                        <a href="{{ route('home') }}" class="hidden md:inline text-gray-300 hover:text-white transition text-sm">
                            Home
                        </a>
                        <a href="{{ route('playlists.index') }}" class="text-gray-300 hover:text-white transition">
                            My Playlists
                        </a>
                        <a href="{{ route('favorites.index') }}" class="text-gray-300 hover:text-white transition">
                            Favorites
                        </a>
                        <a href="{{ route('history.index') }}" class="text-gray-300 hover:text-white transition">
                            History
                        </a>

                        <!-- User Dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <button
                                @click="open = !open"
                                class="flex items-center space-x-2 text-gray-300 hover:text-white transition"
                            >
                                <span>{{ Auth::user()->name }}</span>
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>

                            <div
                                x-show="open"
                                @click.away="open = false"
                                class="absolute right-0 mt-2 w-48 bg-dark-800 rounded-lg shadow-lg py-1 z-50"
                                style="display: none;"
                            >
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-300 hover:bg-dark-700">
                                    Profile
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-gray-300 hover:bg-dark-700">
                                        Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-hidden">
            <div class="h-full max-w-[1920px] mx-auto px-2 sm:px-4 lg:px-8 py-3 sm:py-6">
                <!-- 3-Column Grid Layout with Mobile Optimization -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-3 sm:gap-4 lg:gap-6 h-full">
                    <!-- Left Column: Now Playing (40% on lg, full width on mobile, 50% on tablet) -->
                    <div class="lg:col-span-5 md:col-span-2 h-[400px] sm:h-[500px] md:h-full">
                        <x-now-playing :current-item="$currentItem" :session-id="$session->id" />
                    </div>

                    <!-- Middle Column: Upcoming Queue (30% on lg, 50% on tablet, full on mobile) -->
                    <div class="lg:col-span-3 md:col-span-1 h-[350px] sm:h-[400px] md:h-full overflow-hidden">
                        <x-queue-list :queue-items="$queueItems" />
                    </div>

                    <!-- Right Column: Search & Browse (30% on lg, 50% on tablet, full on mobile) -->
                    <div class="lg:col-span-4 md:col-span-1 h-[400px] sm:h-[500px] md:h-full overflow-hidden">
                        <x-tabbed-browse :popular-songs="$popularSongs" :favorites="$favorites" />
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Toast Notifications -->
    <x-toast-container />

    @stack('scripts')
</body>
</html>

