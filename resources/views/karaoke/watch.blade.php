@extends('layouts.app')

@section('content')
@php
$isFavorited = false;
if (auth()->check()) {
    $isFavorited = \App\Models\Favorite::where('user_id', auth()->id())
        ->where('video_id', $video->id)
        ->exists();
}
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Player Column -->
        <div class="lg:col-span-2">
            <!-- Video Player -->
            <x-player :video-id="$video->id" :autoplay="true" />

            <!-- Video Info -->
            <div class="mt-6 bg-white p-6 rounded-lg shadow-sm">
                <!-- Title -->
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4">
                    {{ $video->title }}
                </h1>

                <!-- Channel & Stats -->
                <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-gray-200">
                    <div class="flex items-center space-x-4">
                        <!-- Channel -->
                        <div>
                            <a
                                href="https://www.youtube.com/channel/{{ $video->channelId }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-lg font-semibold text-gray-900 hover:text-primary-600 transition"
                            >
                                {{ $video->channelTitle }}
                            </a>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="flex items-center space-x-6 text-sm text-gray-600">
                        @if($video->viewCount)
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            {{ number_format($video->viewCount) }} views
                        </span>
                        @endif

                        @if($video->likeCount)
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                            </svg>
                            {{ number_format($video->likeCount) }} likes
                        </span>
                        @endif

                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $video->publishedAt->diffForHumans() }}
                        </span>
                    </div>
                </div>

                <!-- Description -->
                @if($video->description)
                <div class="mt-4" x-data="{ expanded: false }">
                    <div class="prose max-w-none">
                        <p class="text-gray-700 whitespace-pre-wrap" :class="{ 'line-clamp-3': !expanded }">
                            {{ $video->description }}
                        </p>
                    </div>

                    @if(strlen($video->description) > 200)
                    <button
                        @click="expanded = !expanded"
                        class="mt-2 text-sm font-medium text-primary-600 hover:text-primary-700"
                    >
                        <span x-show="!expanded">Show more</span>
                        <span x-show="expanded">Show less</span>
                    </button>
                    @endif
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Quick Actions -->
            <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
                <div class="space-y-3">
                    @auth
                        <!-- Favorite Button -->
                        <x-favorite-button
                            :video-id="$video->id"
                            :title="$video->title"
                            :thumbnail="$video->thumbnailUrl"
                            :is-favorited="$isFavorited"
                            size="md"
                            class="w-full justify-center"
                        />

                        <!-- Add to Playlist -->
                        <x-add-to-playlist-dropdown
                            :video-id="$video->id"
                            :title="$video->title"
                            :thumbnail="$video->thumbnailUrl"
                            :duration="$video->duration"
                            :playlists="$playlists"
                            class="w-full justify-center"
                        />
                    @else
                        <a href="{{ route('login') }}" class="block w-full px-4 py-2 text-center border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                            Login to Favorite
                        </a>
                    @endauth

                    <a
                        href="{{ route('search') }}?q={{ urlencode($video->channelTitle . ' karaoke') }}"
                        class="block w-full px-4 py-2 text-center border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition"
                    >
                        More from {{ Str::limit($video->channelTitle, 20) }}
                    </a>

                    <a
                        href="{{ route('home') }}"
                        class="block w-full px-4 py-2 text-center border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition"
                    >
                        Search Another Song
                    </a>
                </div>
            </div>

            <!-- Video Details -->
            <div class="bg-white p-4 rounded-lg shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Video Details</h2>
                <dl class="space-y-3 text-sm">
                    @if($video->duration)
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Duration:</dt>
                        <dd class="font-medium text-gray-900">{{ $video->getFormattedDuration() }}</dd>
                    </div>
                    @endif

                    @if($video->viewCount)
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Views:</dt>
                        <dd class="font-medium text-gray-900">{{ number_format($video->viewCount) }}</dd>
                    </div>
                    @endif

                    @if($video->likeCount)
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Likes:</dt>
                        <dd class="font-medium text-gray-900">{{ number_format($video->likeCount) }}</dd>
                    </div>
                    @endif

                    <div class="flex justify-between">
                        <dt class="text-gray-600">Published:</dt>
                        <dd class="font-medium text-gray-900">{{ $video->publishedAt->format('M d, Y') }}</dd>
                    </div>

                    <div class="flex justify-between">
                        <dt class="text-gray-600">Video ID:</dt>
                        <dd class="font-mono text-xs text-gray-900">{{ $video->id }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
