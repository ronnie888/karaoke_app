<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing DigitalOcean Spaces Connection...\n";
echo "========================================\n\n";

try {
    $disk = Storage::disk('spaces');

    echo "✓ Storage disk 'spaces' loaded successfully\n";
    echo "✓ Configuration:\n";
    echo "  - Key: " . substr(config('filesystems.disks.spaces.key'), 0, 10) . "...\n";
    echo "  - Bucket: " . config('filesystems.disks.spaces.bucket') . "\n";
    echo "  - Region: " . config('filesystems.disks.spaces.region') . "\n";
    echo "  - Endpoint: " . config('filesystems.disks.spaces.endpoint') . "\n";
    echo "  - CDN URL: " . config('filesystems.disks.spaces.url') . "\n\n";

    echo "Testing connection by checking for 'songs' directory...\n";

    $exists = $disk->exists('songs');

    if ($exists) {
        echo "✓ SUCCESS: Connected to DigitalOcean Spaces!\n";
        echo "✓ The 'songs' directory exists in your Space.\n\n";

        // Try to list files
        echo "Listing files in 'songs' directory...\n";
        $files = $disk->files('songs');
        echo "Found " . count($files) . " files.\n";

        if (count($files) > 0) {
            echo "\nFirst 5 files:\n";
            foreach (array_slice($files, 0, 5) as $file) {
                echo "  - " . basename($file) . "\n";
            }
        } else {
            echo "  (Directory is empty - upload files using rclone)\n";
        }
    } else {
        echo "⚠ WARNING: Connected but 'songs' directory doesn't exist yet.\n";
        echo "  You'll need to upload files to this directory.\n";
    }

    echo "\n========================================\n";
    echo "✓ Connection test completed successfully!\n";

} catch (\Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "\nFull error:\n";
    echo $e->getTraceAsString() . "\n";

    echo "\n========================================\n";
    echo "Troubleshooting:\n";
    echo "1. Check your .env file has correct credentials\n";
    echo "2. Verify the Space name and region are correct\n";
    echo "3. Ensure the API token has the right permissions\n";
    echo "4. Check your internet connection\n";
}
