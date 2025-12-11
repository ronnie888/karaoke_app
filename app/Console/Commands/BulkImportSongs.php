<?php

namespace App\Console\Commands;

use App\Models\Song;
use App\Services\FilenameParser;
use Exception;
use Illuminate\Console\Command;

class BulkImportSongs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'karaoke:bulk-import
                            {--file= : Text file containing file paths, one per line}
                            {--directory=songs/2025/12/ : Directory path in CDN}
                            {--cdn-base=https://karaoke-songs.sfo3.cdn.digitaloceanspaces.com : CDN base URL}
                            {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bulk import songs from a list of filenames (no API calls needed)';

    /**
     * Execute the console command.
     */
    public function handle(FilenameParser $parser): int
    {
        $cdnBase = rtrim($this->option('cdn-base'), '/');
        $directory = trim($this->option('directory'), '/');
        $filePath = $this->option('file');

        $filenames = [];

        if ($filePath) {
            // Read from file
            if (!file_exists($filePath)) {
                $this->error("File not found: {$filePath}");
                return 1;
            }
            $content = file_get_contents($filePath);
            $lines = explode("\n", $content);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line && str_ends_with(strtolower($line), '.mp4')) {
                    $filenames[] = $line;
                }
            }
        } else {
            // Interactive mode - paste filenames
            $this->info("Paste filenames (one per line). Enter empty line when done:");
            $this->info("Example: AKIN KA NA LANG - Morissette Amon (HD Karaoke).mp4");
            $this->newLine();

            while (true) {
                $line = $this->ask('Filename (or press Enter to finish)');
                if (empty($line)) {
                    break;
                }
                if (str_ends_with(strtolower($line), '.mp4')) {
                    $filenames[] = trim($line);
                } else {
                    $this->warn("Skipping non-MP4 file: {$line}");
                }
            }
        }

        if (empty($filenames)) {
            $this->warn("No MP4 files provided.");
            return 0;
        }

        $totalFiles = count($filenames);
        $this->info("Found {$totalFiles} MP4 files to import");
        $this->newLine();

        // Confirm before proceeding
        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to proceed with importing?', true)) {
                $this->info('Import cancelled.');
                return 0;
            }
        }

        // Initialize counters
        $imported = 0;
        $skipped = 0;
        $errors = 0;

        // Create progress bar
        $progressBar = $this->output->createProgressBar($totalFiles);
        $progressBar->setFormat('verbose');
        $progressBar->start();

        foreach ($filenames as $filename) {
            try {
                $filename = trim($filename);
                $filePath = "{$directory}/{$filename}";
                $cdnUrl = "{$cdnBase}/{$directory}/" . rawurlencode($filename);

                // Generate hash from path
                $fileHash = hash('sha256', $filePath);

                // Check if already exists
                if (!$this->option('force')) {
                    $existing = Song::where('file_hash', $fileHash)->first();
                    if ($existing) {
                        $skipped++;
                        $progressBar->advance();
                        continue;
                    }
                }

                // Parse filename for metadata
                $parsed = $parser->parse($filename);

                // Estimate file size (average karaoke video ~50MB)
                $estimatedSize = 50 * 1024 * 1024;

                // Estimate duration (average 4 minutes)
                $estimatedDuration = 240;

                // Create or update database record
                Song::updateOrCreate(
                    ['file_hash' => $fileHash],
                    [
                        'file_path' => $filePath,
                        'file_name' => $filename,
                        'file_size' => $estimatedSize,
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
                    ]
                );

                $imported++;

            } catch (Exception $e) {
                $this->newLine();
                $this->error("Error processing {$filename}: {$e->getMessage()}");
                $errors++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display summary
        $this->info('Import complete!');
        $this->newLine();

        $this->table(
            ['Status', 'Count'],
            [
                ['Imported', $imported],
                ['Skipped (already exists)', $skipped],
                ['Errors', $errors],
                ['Total Files', $totalFiles],
            ]
        );

        // Display sample songs
        if ($imported > 0) {
            $this->newLine();
            $this->info('Sample imported songs:');
            $songs = Song::latest()->limit(5)->get(['title', 'artist', 'genre', 'cdn_url']);

            $this->table(
                ['Title', 'Artist', 'Genre'],
                $songs->map(fn($song) => [
                    $song->title,
                    $song->artist ?? 'Unknown',
                    $song->genre ?? 'Unknown',
                ])
            );
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
