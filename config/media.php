<?php

return [
    /*
    |--------------------------------------------------------------------------
    | FFmpeg Binary Paths
    |--------------------------------------------------------------------------
    |
    | Specify the paths to FFmpeg and FFProbe binaries.
    | On Windows: C:\ffmpeg\bin\ffmpeg.exe
    | On Linux: /usr/bin/ffmpeg
    |
    */

    'ffmpeg_binaries' => env('FFMPEG_BINARIES', 'C:\ffmpeg\bin\ffmpeg.exe'),
    'ffprobe_binaries' => env('FFPROBE_BINARIES', 'C:\ffmpeg\bin\ffprobe.exe'),

    /*
    |--------------------------------------------------------------------------
    | Thumbnail Generation
    |--------------------------------------------------------------------------
    |
    | Configuration for video thumbnail generation.
    |
    */

    'thumbnail' => [
        'enabled' => env('GENERATE_THUMBNAILS', false),
        'time' => 5, // seconds into video to capture thumbnail
        'width' => 480,
        'height' => 360,
        'format' => 'jpg',
        'quality' => 85,
    ],

    /*
    |--------------------------------------------------------------------------
    | Video Processing
    |--------------------------------------------------------------------------
    |
    | Timeout and threading configuration for video processing.
    |
    */

    'processing' => [
        'timeout' => 3600, // seconds
        'threads' => 12,
    ],
];
