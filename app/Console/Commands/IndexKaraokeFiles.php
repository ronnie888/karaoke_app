<?php

namespace App\Console\Commands;

use App\Models\Song;
use App\Services\FilenameParser;
use App\Services\VideoMetadataExtractor;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class IndexKaraokeFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'karaoke:index {source?} {--force} {--skip-upload} {--limit=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index karaoke files from local directory to database and upload to storage';

    /**
     * Execute the console command.
     */
    public function handle(
        FilenameParser $parser,
        VideoMetadataExtractor $extractor
    ): int {
        $sourcePath = $this->argument('source') ?? 'D:\HD KARAOKE SONGS';

        // Validate source directory
        if (!is_dir($sourcePath)) {
            $this->error("Source directory not found: {$sourcePath}");
            return 1;
        }

        // Get all MP4 files
        $files = glob($sourcePath . '/*.mp4');

        if (empty($files)) {
            $this->error("No MP4 files found in: {$sourcePath}");
            return 1;
        }

        // Apply limit if specified
        if ($limit = $this->option('limit')) {
            $files = array_slice($files, 0, (int) $limit);
        }

        $totalFiles = count($files);
        $this->info("Found {$totalFiles} MP4 files in: {$sourcePath}");
        $this->newLine();

        // Check if FFProbe is available
        if (!$extractor->isAvailable()) {
            $this->warn('⚠️  FFProbe not available - using fallback metadata extraction');
            $this->warn('   Install FFmpeg for accurate video metadata: sudo apt install ffmpeg');
            $this->newLine();
        }

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
        $uploaded = 0;

        // Create progress bar
        $progressBar = $this->output->createProgressBar($totalFiles);
        $progressBar->setFormat('verbose');
        $progressBar->start();

        foreach ($files as $filePath) {
            try {
                $fileName = basename($filePath);
                $fileHash = hash_file('sha256', $filePath);

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

                // Extract video metadata
                $metadata = $extractor->extract($filePath);

                // Prepare storage path
                $remotePath = 'songs/' . date('Y/m/') . $fileName;
                $cdnUrl = null;

                // Upload to storage (unless --skip-upload)
                if (!$this->option('skip-upload')) {
                    $diskName = config('filesystems.default');
                    $disk = Storage::disk($diskName);

                    // Check if using cloud storage
                    if (in_array($diskName, ['spaces', 's3'])) {
                        $uploadResult = $disk->putFileAs(
                            dirname($remotePath),
                            $filePath,
                            basename($remotePath),
                            'public'
                        );

                        if (!$uploadResult) {
                            throw new Exception("Failed to upload to storage");
                        }

                        $cdnUrl = $disk->url($remotePath);
                        $uploaded++;
                    } else {
                        // For local storage, just use the path
                        $remotePath = $filePath;
                        $cdnUrl = null;
                    }
                }

                // Create or update database record
                $song = Song::updateOrCreate(
                    ['file_hash' => $fileHash],
                    [
                        'file_path' => $remotePath,
                        'file_name' => $fileName,
                        'file_size' => filesize($filePath),
                        'title' => $parser->cleanTitle($parsed['title']),
                        'artist' => $parser->cleanArtist($parsed['artist']),
                        'genre' => $parser->detectGenre($parsed['artist']),
                        'language' => $parsed['language'],
                        'duration' => $metadata['duration'],
                        'width' => $metadata['width'],
                        'height' => $metadata['height'],
                        'video_codec' => $metadata['video_codec'],
                        'audio_codec' => $metadata['audio_codec'],
                        'bitrate' => $metadata['bitrate'],
                        'storage_driver' => $this->option('skip-upload') ? 'local' : config('filesystems.default'),
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
                        ['file_hash' => hash_file('sha256', $filePath)],
                        [
                            'file_name' => basename($filePath),
                            'file_path' => $filePath,
                            'file_size' => filesize($filePath),
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
                ['☁ Uploaded to storage', $uploaded],
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

        return 0;
    }
}
