<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing DigitalOcean Spaces Connection (Detailed)...\n";
echo "========================================\n\n";

try {
    $disk = Storage::disk('spaces');

    echo "✓ Storage disk 'spaces' loaded successfully\n";
    echo "✓ Configuration:\n";
    echo "  - Key: " . substr(config('filesystems.disks.spaces.key'), 0, 10) . "...\n";
    echo "  - Bucket: " . config('filesystems.disks.spaces.bucket') . "\n";
    echo "  - Region: " . config('filesystems.disks.spaces.region') . "\n";
    echo "  - Endpoint: " . config('filesystems.disks.spaces.endpoint') . "\n";
    echo "  - CDN URL: " . config('filesystems.disks.spaces.url') . "\n";
    echo "  - Path Style: " . (config('filesystems.disks.spaces.use_path_style_endpoint') ? 'true' : 'false') . "\n\n";

    echo "Test 1: Listing all files in root directory...\n";
    try {
        $allFiles = $disk->allFiles();
        echo "✓ SUCCESS: Listed " . count($allFiles) . " files in root\n";

        if (count($allFiles) > 0) {
            echo "  First few files:\n";
            foreach (array_slice($allFiles, 0, 5) as $file) {
                echo "  - " . $file . "\n";
            }
        } else {
            echo "  (Space is empty)\n";
        }
    } catch (\Exception $e) {
        echo "✗ FAILED: " . $e->getMessage() . "\n";
    }

    echo "\nTest 2: Creating a test file...\n";
    try {
        $testContent = "Test from Laravel at " . now()->toDateTimeString();
        $disk->put('test.txt', $testContent);
        echo "✓ SUCCESS: Created test.txt\n";

        // Try to read it back
        $content = $disk->get('test.txt');
        echo "✓ SUCCESS: Read test.txt back (content matches: " . ($content === $testContent ? 'YES' : 'NO') . ")\n";

        // Clean up
        $disk->delete('test.txt');
        echo "✓ SUCCESS: Deleted test.txt\n";

    } catch (\Exception $e) {
        echo "✗ FAILED: " . $e->getMessage() . "\n";
        echo "  This might be a permissions issue with your API key.\n";
    }

    echo "\nTest 3: Checking for 'songs' directory...\n";
    try {
        // Try different methods
        $allDirectories = $disk->directories();
        echo "✓ All directories in root: " . implode(', ', $allDirectories ?: ['(none)']) . "\n";

        $songsExists = in_array('songs', $allDirectories);

        if ($songsExists) {
            echo "✓ SUCCESS: 'songs' directory exists!\n";

            // List files in songs directory
            $songFiles = $disk->files('songs');
            echo "✓ Found " . count($songFiles) . " files in 'songs' directory\n";

            if (count($songFiles) > 0) {
                echo "\nFirst 5 files:\n";
                foreach (array_slice($songFiles, 0, 5) as $file) {
                    echo "  - " . basename($file) . "\n";
                }
            }
        } else {
            echo "⚠ WARNING: 'songs' directory doesn't exist yet.\n";
            echo "  You'll need to create it when uploading files.\n";
        }

    } catch (\Exception $e) {
        echo "✗ FAILED: " . $e->getMessage() . "\n";
    }

    echo "\n========================================\n";
    echo "✓ Connection test completed!\n";
    echo "\nNext steps:\n";
    echo "1. If all tests passed, you're ready to upload files\n";
    echo "2. Use: php artisan karaoke:index \"D:\\HD KARAOKE SONGS\" --limit=5\n";
    echo "3. Or use rclone for bulk upload to production\n";

} catch (\Exception $e) {
    echo "\n✗ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "\nFull error:\n";
    echo $e->getTraceAsString() . "\n";

    echo "\n========================================\n";
    echo "Troubleshooting:\n";
    echo "1. Verify your Access Key and Secret Key are correct\n";
    echo "2. Check that you selected 'Full Access' when creating the key\n";
    echo "3. Ensure the Space name 'karaoke-songs' exists in sfo3 region\n";
    echo "4. Check your internet connection\n";
    echo "5. Try generating a new Spaces key with Full Access\n";
}
