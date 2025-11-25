<?php

return [

    /*
    |--------------------------------------------------------------------------
    | YouTube Data API v3 Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains all configuration for the YouTube Data API v3
    | integration. The API key should be stored in your .env file for security.
    |
    */

    'api_key' => env('YOUTUBE_API_KEY'),

    'api_base' => env('YOUTUBE_API_BASE', 'https://www.googleapis.com/youtube/v3'),

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how long different types of YouTube API responses should be
    | cached. Longer cache times reduce API quota usage but may show stale data.
    |
    */

    'cache' => [
        'enabled' => env('YOUTUBE_CACHE_ENABLED', true),

        // Time-to-live for search results (in seconds)
        'search_ttl' => env('YOUTUBE_CACHE_SEARCH_TTL', 3600), // 1 hour

        // Time-to-live for video details (in seconds)
        'video_ttl' => env('YOUTUBE_CACHE_VIDEO_TTL', 86400), // 24 hours

        // Time-to-live for popular/trending videos (in seconds)
        'popular_ttl' => env('YOUTUBE_CACHE_POPULAR_TTL', 7200), // 2 hours

        // Time-to-live for channel information (in seconds)
        'channel_ttl' => env('YOUTUBE_CACHE_CHANNEL_TTL', 43200), // 12 hours

        // Cache driver to use (defaults to app cache driver)
        'driver' => env('YOUTUBE_CACHE_DRIVER', config('cache.default')),

        // Cache key prefix
        'prefix' => 'youtube',

        // Cache tags (if using Redis/Memcached)
        'tags' => ['youtube', 'api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Quota Management
    |--------------------------------------------------------------------------
    |
    | YouTube API has daily quota limits. Configure monitoring and thresholds
    | to track usage and prevent quota exhaustion.
    |
    */

    'quota' => [
        // Daily quota limit (default free tier)
        'daily_limit' => env('YOUTUBE_QUOTA_LIMIT', 10000),

        // Cost per API operation type (in quota units)
        'costs' => [
            'search' => 100,        // search.list
            'videos' => 1,          // videos.list
            'channels' => 1,        // channels.list
            'playlists' => 1,       // playlists.list
            'playlistItems' => 1,   // playlistItems.list
        ],

        // Enable quota monitoring
        'monitor' => env('YOUTUBE_QUOTA_MONITOR', true),

        // Quota warning threshold (percentage)
        'warning_threshold' => 80, // Alert at 80% usage

        // Quota critical threshold (percentage)
        'critical_threshold' => 95, // Block requests at 95% usage
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Configuration
    |--------------------------------------------------------------------------
    |
    | Default parameters for YouTube search functionality.
    |
    */

    'search' => [
        // Default maximum results per search
        'max_results' => env('YOUTUBE_SEARCH_MAX_RESULTS', 25),

        // Maximum allowed results (hard limit)
        'max_results_limit' => 50,

        // Default search order
        'default_order' => 'relevance', // relevance, date, rating, title, viewCount

        // Default region code (ISO 3166-1 alpha-2)
        'region_code' => env('YOUTUBE_SEARCH_REGION', 'US'),

        // Safe search mode
        'safe_search' => env('YOUTUBE_SAFE_SEARCH', 'moderate'), // none, moderate, strict

        // Video category ID for karaoke (Music = 10)
        'karaoke_category' => '10',

        // Video definition (any, high, standard)
        'video_definition' => 'any',

        // Video type (any, episode, movie)
        'video_type' => 'any',

        // Filter embeddable videos only (any, true)
        // Set to 'true' to only return videos that can be embedded on external websites
        'video_embeddable' => env('YOUTUBE_VIDEO_EMBEDDABLE', 'true'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for YouTube API requests to prevent abuse
    | and quota exhaustion.
    |
    */

    'rate_limit' => [
        // Enable rate limiting
        'enabled' => env('YOUTUBE_RATE_LIMIT_ENABLED', true),

        // Maximum requests per minute
        'requests_per_minute' => env('YOUTUBE_RATE_LIMIT_RPM', 60),

        // Maximum concurrent requests
        'max_concurrent' => 5,

        // Retry failed requests
        'retry' => [
            'enabled' => true,
            'max_attempts' => 3,
            'delay' => 1000, // milliseconds
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Configure logging for YouTube API requests and responses.
    |
    */

    'logging' => [
        // Enable request/response logging
        'enabled' => env('YOUTUBE_LOG_ENABLED', env('APP_DEBUG', false)),

        // Log channel to use
        'channel' => env('YOUTUBE_LOG_CHANNEL', 'stack'),

        // Log level (debug, info, warning, error)
        'level' => 'info',

        // Log slow queries (threshold in milliseconds)
        'slow_query_threshold' => 1000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Handling
    |--------------------------------------------------------------------------
    |
    | Configure how YouTube API errors should be handled.
    |
    */

    'errors' => [
        // Throw exceptions on API errors
        'throw_exceptions' => env('APP_DEBUG', false),

        // Fallback behavior on errors
        'fallback_to_cache' => true,

        // Return empty results on error
        'return_empty_on_error' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Video Embed Configuration
    |--------------------------------------------------------------------------
    |
    | Configure YouTube IFrame Player parameters.
    |
    */

    'embed' => [
        // Default player width
        'width' => '100%',

        // Default player height
        'height' => '480',

        // Player parameters
        'params' => [
            'autoplay' => 0,
            'controls' => 1,
            'modestbranding' => 1,
            'rel' => 0,
            'showinfo' => 0,
            'fs' => 1,
            'cc_load_policy' => 0,
            'iv_load_policy' => 3,
            'enablejsapi' => 1,
        ],
    ],

];
