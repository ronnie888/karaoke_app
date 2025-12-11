<?php

/**
 * Bulk Import Karaoke Songs from Local Directory to Database
 *
 * This script scans a local directory for MP4 files and imports them
 * to the database with CDN URLs pointing to DigitalOcean Spaces.
 *
 * Usage: php import-local-songs.php
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Song;
use App\Services\FilenameParser;

// Configuration
$localPath = 'D:\HD KARAOKE SONGS';
$cdnBase = 'https://karaoke-songs.sfo3.cdn.digitaloceanspaces.com';
$cdnDirectory = 'songs/2025/12';

echo "==============================================\n";
echo "  Karaoke Songs Bulk Import Tool\n";
echo "==============================================\n\n";

// Check if directory exists
if (!is_dir($localPath)) {
    echo "ERROR: Directory not found: {$localPath}\n";
    exit(1);
}

// Get parser
$parser = new FilenameParser();

// Scan for MP4 files
echo "Scanning {$localPath} for MP4 files...\n";
$files = glob($localPath . '/*.mp4');
$totalFiles = count($files);

if ($totalFiles === 0) {
    echo "No MP4 files found.\n";
    exit(0);
}

echo "Found {$totalFiles} MP4 files\n\n";

// Initialize counters
$imported = 0;
$skipped = 0;
$errors = 0;

// Process each file
foreach ($files as $index => $filePath) {
    $filename = basename($filePath);
    $progress = $index + 1;

    // Show progress every 10 files
    if ($progress % 10 === 0 || $progress === $totalFiles) {
        echo "\rProcessing: {$progress}/{$totalFiles} ({$imported} imported, {$skipped} skipped, {$errors} errors)";
    }

    try {
        // Generate paths
        $cdnPath = "{$cdnDirectory}/{$filename}";
        $cdnUrl = "{$cdnBase}/{$cdnDirectory}/" . rawurlencode($filename);
        $fileHash = hash('sha256', $cdnPath);

        // Check if already exists
        $existing = Song::where('file_hash', $fileHash)->first();
        if ($existing) {
            $skipped++;
            continue;
        }

        // Get file info
        $fileSize = filesize($filePath);

        // Parse filename for metadata
        $parsed = $parser->parse($filename);

        // Estimate duration (will be updated when played)
        $estimatedDuration = 240; // 4 minutes default

        // Create database record
        Song::create([
            'file_path' => $cdnPath,
            'file_name' => $filename,
            'file_size' => $fileSize,
            'file_hash' => $fileHash,
            'title' => $parser->cleanTitle($parsed['title']),
            'artist' => $parser->cleanArtist($parsed['artist']),
            'genre' => $parser->detectGenre($parsed['artist']),
            'language' => $parsed['language'],
            'duration' => $estimatedDuration,
            'storage_driver' => 'spaces',
            'cdn_url' => $cdnUrl,
            'search_text' => implode(' ', array_filter([
                $parsed['title'],
                $parsed['artist'],
                $parsed['raw_name'],
            ])),
            'indexed_at' => now(),
            'index_status' => 'completed',
            'index_error' => null,
        ]);

        $imported++;

    } catch (Exception $e) {
        echo "\nError processing {$filename}: {$e->getMessage()}\n";
        $errors++;
    }
}

echo "\n\n";
echo "==============================================\n";
echo "  Import Complete!\n";
echo "==============================================\n\n";

echo "Results:\n";
echo "  - Imported: {$imported}\n";
echo "  - Skipped (already exists): {$skipped}\n";
echo "  - Errors: {$errors}\n";
echo "  - Total: {$totalFiles}\n\n";

// Show database stats
$totalSongs = Song::count();
$completedSongs = Song::where('index_status', 'completed')->count();
$opmSongs = Song::where('genre', 'OPM')->count();
$rockSongs = Song::where('genre', 'Rock')->count();
$popSongs = Song::where('genre', 'Pop')->count();

echo "Database Statistics:\n";
echo "  - Total Songs: {$totalSongs}\n";
echo "  - Completed: {$completedSongs}\n";
echo "  - OPM Songs: {$opmSongs}\n";
echo "  - Rock Songs: {$rockSongs}\n";
echo "  - Pop Songs: {$popSongs}\n\n";

// Show sample songs
echo "Sample Imported Songs:\n";
$samples = Song::latest()->limit(5)->get(['title', 'artist', 'genre']);
foreach ($samples as $song) {
    echo "  - {$song->title} by {$song->artist} ({$song->genre})\n";
}

echo "\nDone!\n";
