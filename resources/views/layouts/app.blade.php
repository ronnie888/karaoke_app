<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Karaoke Tube') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo & Main Nav -->
                <div class="flex items-center space-x-8">
                    <!-- Logo -->
                    <a href="{{ route('home') }}" class="flex items-center">
                        <svg class="w-8 h-8 text-primary-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 3v9.28c-.47-.17-.97-.28-1.5-.28C8.01 12 6 14.01 6 16.5S8.01 21 10.5 21c2.31 0 4.2-1.75 4.45-4H15V6h4V3h-7z"/>
                        </svg>
                        <span class="ml-2 text-xl font-bold text-gray-900">Karaoke Tube</span>
                    </a>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex items-center space-x-4">
                        <a href="{{ route('search') }}" class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition">
                            Search
                        </a>
                    </div>
                </div>

                <!-- Right Side Navigation -->
                <div class="flex items-center space-x-4">
                    @auth
                        <!-- Desktop User Menu -->
                        <div class="hidden md:flex items-center space-x-4">
                            <a href="{{ route('playlists.index') }}" class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition inline-flex items-center">
                                My Playlists
                                @if(isset($playlistsCount) && $playlistsCount > 0)
                                    <span class="ml-1.5 px-2 py-0.5 text-xs font-semibold text-white bg-primary-600 rounded-full">{{ $playlistsCount }}</span>
                                @endif
                            </a>
                            <a href="{{ route('favorites.index') }}" class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition inline-flex items-center">
                                Favorites
                                @if(isset($favoritesCount) && $favoritesCount > 0)
                                    <span class="ml-1.5 px-2 py-0.5 text-xs font-semibold text-white bg-red-500 rounded-full">{{ $favoritesCount }}</span>
                                @endif
                            </a>
                            <a href="{{ route('history.index') }}" class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition inline-flex items-center">
                                History
                                @if(isset($historyCount) && $historyCount > 0)
                                    <span class="ml-1.5 px-2 py-0.5 text-xs font-semibold text-white bg-gray-500 rounded-full">{{ $historyCount }}</span>
                                @endif
                            </a>

                            <!-- User Dropdown -->
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="flex items-center text-sm font-medium text-gray-700 hover:text-gray-900 transition">
                                    <span>{{ auth()->user()->name }}</span>
                                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10">
                                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                                        Profile
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Desktop Guest Links -->
                        <div class="hidden md:flex items-center space-x-4">
                            <a href="{{ route('login') }}" class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition">
                                Login
                            </a>
                            <a href="{{ route('register') }}" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md text-sm font-medium transition">
                                Sign Up
                            </a>
                        </div>
                    @endauth

                    <!-- Mobile Menu Button -->
                    <div class="md:hidden" x-data="{ open: false }">
                        <button @click="open = !open" type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:text-gray-900 hover:bg-gray-100 transition">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <!-- Mobile Menu -->
                        <div x-show="open" @click.away="open = false" x-transition class="absolute top-16 left-0 right-0 bg-white shadow-lg border-b border-gray-200 z-20">
                            <div class="px-2 pt-2 pb-3 space-y-1">
                                <a href="{{ route('search') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 transition">
                                    Search
                                </a>

                                @auth
                                    <a href="{{ route('playlists.index') }}" class="flex items-center justify-between px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 transition">
                                        <span>My Playlists</span>
                                        @if(isset($playlistsCount) && $playlistsCount > 0)
                                            <span class="px-2 py-0.5 text-xs font-semibold text-white bg-primary-600 rounded-full">{{ $playlistsCount }}</span>
                                        @endif
                                    </a>
                                    <a href="{{ route('favorites.index') }}" class="flex items-center justify-between px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 transition">
                                        <span>Favorites</span>
                                        @if(isset($favoritesCount) && $favoritesCount > 0)
                                            <span class="px-2 py-0.5 text-xs font-semibold text-white bg-red-500 rounded-full">{{ $favoritesCount }}</span>
                                        @endif
                                    </a>
                                    <a href="{{ route('history.index') }}" class="flex items-center justify-between px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 transition">
                                        <span>History</span>
                                        @if(isset($historyCount) && $historyCount > 0)
                                            <span class="px-2 py-0.5 text-xs font-semibold text-white bg-gray-500 rounded-full">{{ $historyCount }}</span>
                                        @endif
                                    </a>
                                    <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 transition">
                                        Profile
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 transition">
                                            Logout
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 transition">
                                        Login
                                    </a>
                                    <a href="{{ route('register') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 transition">
                                        Sign Up
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @if (session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-md" x-data="{ show: true }" x-show="show">
                <div class="flex justify-between items-center">
                    <span>{{ session('success') }}</span>
                    <button @click="show = false" class="text-green-600 hover:text-green-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-md" x-data="{ show: true }" x-show="show">
                <div class="flex justify-between items-center">
                    <span>{{ session('error') }}</span>
                    <button @click="show = false" class="text-red-600 hover:text-red-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Page Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-600 text-sm">
                    &copy; {{ date('Y') }} Karaoke Tube. All rights reserved.
                </p>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="#" class="text-gray-600 hover:text-gray-900 text-sm transition">About</a>
                    <a href="#" class="text-gray-600 hover:text-gray-900 text-sm transition">Privacy</a>
                    <a href="#" class="text-gray-600 hover:text-gray-900 text-sm transition">Terms</a>
                    <a href="#" class="text-gray-600 hover:text-gray-900 text-sm transition">Contact</a>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
