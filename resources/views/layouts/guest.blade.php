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
        @vite(['resources/css/app.css', 'resources/js/app.ts'])
    </head>
    <body class="font-sans antialiased bg-dark-900">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4">
            <!-- Logo -->
            <div class="mb-8">
                <a href="/" class="flex items-center space-x-3 transform hover:scale-105 transition duration-300">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-primary-600 rounded-lg flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 sm:w-10 sm:h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 3v9.28c-.47-.17-.97-.28-1.5-.28C8.01 12 6 14.01 6 16.5S8.01 21 10.5 21c2.31 0 4.2-1.75 4.45-4H15V6h4V3h-7z"/>
                        </svg>
                    </div>
                    <span class="text-2xl sm:text-3xl font-bold text-white">KARAOKE TUBE</span>
                </a>
            </div>

            <!-- Auth Card -->
            <div class="w-full sm:max-w-md px-6 py-8 bg-dark-850 border border-dark-700 shadow-2xl overflow-hidden sm:rounded-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
