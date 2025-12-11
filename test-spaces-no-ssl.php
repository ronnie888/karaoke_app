<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing DigitalOcean Spaces Connection (SSL Verification Disabled)...\n";
echo "========================================\n\n";

try {
    // Temporarily disable SSL verification for testing
    $config = config('filesystems.disks.spaces');
    $config['options'] = [
        'http' => [
            'verify' => false, // Disable SSL verification
        ]
    ];

    Config::set('filesystems.disks.spaces', $config);

    $disk = Storage::disk('spaces');

    echo "✓ Storage disk 'spaces' loaded successfully\n";
    echo "⚠ WARNING: SSL verification is DISABLED for this test\n\n";

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

    echo "\nTest 2: Checking for 'songs' directory...\n";
    try {
        $allDirectories = $disk->directories();
        echo "✓ All directories in root: " . implode(', ', $allDirectories ?: ['(none)']) . "\n";

        $songsExists = in_array('songs', $allDirectories);

        if ($songsExists) {
            echo "✓ SUCCESS: 'songs' directory exists!\n";

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
    echo "\n⚠ NOTE: This test disabled SSL verification.\n";
    echo "   You should FIX the SSL certificate issue before production use.\n";

} catch (\Exception $e) {
    echo "\n✗ FATAL ERROR: " . $e->getMessage() . "\n";
}
