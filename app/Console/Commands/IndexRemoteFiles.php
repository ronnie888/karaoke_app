<?php

namespace App\Console\Commands;

use App\Models\Song;
use App\Services\FilenameParser;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class IndexRemoteFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'karaoke:index-remote {--force} {--disk=spaces}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index karaoke files already uploaded to cloud storage (Spaces/S3)';

    /**
     * Execute the console command.
     */
    public function handle(FilenameParser $parser): int
    {
        $diskName = $this->option('disk');

        // Get storage disk
        try {
            $disk = Storage::disk($diskName);
        } catch (Exception $e) {
            $this->error("Storage disk '{$diskName}' not found or not configured.");
            return 1;
        }

        $this->info("Scanning files in {$diskName} storage...");
        $this->newLine();

        // Get all files in songs directory
        try {
            $files = $disk->files('songs');
        } catch (Exception $e) {
            $this->error("Failed to list files: {$e->getMessage()}");
            return 1;
        }

        // Filter for MP4 files only
        $mp4Files = array_filter($files, fn($file) => str_ends_with(strtolower($file), '.mp4'));
        $totalFiles = count($mp4Files);

        if ($totalFiles === 0) {
            $this->warn("No MP4 files found in songs directory.");
            return 0;
        }

        $this->info("Found {$totalFiles} MP4 files");
        $this->newLine();

        // Confirm before proceeding
        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to proceed with indexing?', true)) {
                $this->info('Indexing cancelled.');
                return 0;
            }
        }

        // Initialize counters
        $indexed = 0;
        $skipped = 0;
        $errors = 0;

        // Create progress bar
        $progressBar = $this->output->createProgressBar($totalFiles);
        $progressBar->setFormat('verbose');
        $progressBar->start();

        foreach ($mp4Files as $filePath) {
            try {
                $fileName = basename($filePath);

                // Generate hash from path (can't hash remote files directly)
                $fileHash = hash('sha256', $filePath);

                // Check if already indexed (unless --force)
                if (!$this->option('force')) {
                    $existing = Song::where('file_hash', $fileHash)->first();
                    if ($existing) {
                        $skipped++;
                        $progressBar->advance();
                        continue;
                    }
                }

                // Parse filename for metadata
                $parsed = $parser->parse($fileName);

                // Get file metadata from storage
                $size = $disk->size($filePath);
                $cdnUrl = $disk->url($filePath);

                // Estimate duration (will be updated when file is actually accessed)
                // Assuming average bitrate of 2000 kbps
                $estimatedDuration = (int) ceil(($size * 8) / (2000 * 1000));

                // Create or update database record
                Song::updateOrCreate(
                    ['file_hash' => $fileHash],
                    [
                        'file_path' => $filePath,
                        'file_name' => $fileName,
                        'file_size' => $size,
                        'title' => $parser->cleanTitle($parsed['title']),
                        'artist' => $parser->cleanArtist($parsed['artist']),
                        'genre' => $parser->detectGenre($parsed['artist']),
                        'language' => $parsed['language'],
                        'duration' => $estimatedDuration,
                        'storage_driver' => $diskName,
                        'cdn_url' => $cdnUrl,
                        'search_text' => implode(' ', array_filter([
                            $parsed['title'],
                            $parsed['artist'],
                            $parsed['raw_name'],
                        ])),
                        'indexed_at' => now(),
                        'index_status' => 'completed',
                        'index_error' => null,
                    ]
                );

                $indexed++;

            } catch (Exception $e) {
                $this->newLine();
                $this->error("✗ Error processing {$fileName}: {$e->getMessage()}");

                // Log error in database
                try {
                    Song::updateOrCreate(
                        ['file_hash' => hash('sha256', $filePath)],
                        [
                            'file_name' => basename($filePath),
                            'file_path' => $filePath,
                            'file_size' => 0,
                            'title' => basename($filePath),
                            'duration' => 0,
                            'index_status' => 'failed',
                            'index_error' => $e->getMessage(),
                        ]
                    );
                } catch (Exception $dbError) {
                    // Ignore database errors during error logging
                }

                $errors++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display summary
        $this->info('✓ Indexing complete!');
        $this->newLine();

        $this->table(
            ['Status', 'Count'],
            [
                ['✓ Indexed', $indexed],
                ['↻ Skipped (already exists)', $skipped],
                ['✗ Errors', $errors],
                ['━ Total Files', $totalFiles],
            ]
        );

        // Display sample songs
        if ($indexed > 0) {
            $this->newLine();
            $this->info('Sample indexed songs:');
            $songs = Song::latest()->limit(5)->get(['title', 'artist', 'genre', 'duration']);

            $this->table(
                ['Title', 'Artist', 'Genre', 'Duration'],
                $songs->map(fn($song) => [
                    $song->title,
                    $song->artist ?? 'Unknown',
                    $song->genre ?? 'Unknown',
                    $song->formatted_duration,
                ])
            );
        }

        // Display errors summary
        if ($errors > 0) {
            $this->newLine();
            $this->warn("⚠️  {$errors} files failed to index. Check failed songs:");
            $this->line("   php artisan tinker");
            $this->line("   >>> Song::where('index_status', 'failed')->get(['file_name', 'index_error'])");
        }

        // Display statistics
        $this->newLine();
        $this->info('Database Statistics:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Songs', Song::count()],
                ['Completed', Song::where('index_status', 'completed')->count()],
                ['OPM Songs', Song::where('genre', 'OPM')->count()],
                ['Rock Songs', Song::where('genre', 'Rock')->count()],
                ['Pop Songs', Song::where('genre', 'Pop')->count()],
            ]
        );

        return 0;
    }
}
