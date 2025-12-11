<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Storage;

echo "Testing DigitalOcean Spaces connection...\n\n";

try {
    $disk = Storage::disk('spaces');

    // Try listing files in root
    echo "Listing root directory...\n";
    $rootFiles = $disk->files('');
    echo "Root files: " . count($rootFiles) . "\n";

    // Try listing files in songs directory
    echo "\nListing songs directory...\n";
    $songFiles = $disk->files('songs');
    echo "Song files: " . count($songFiles) . "\n";

    // Show first 5 files
    if (count($songFiles) > 0) {
        echo "\nFirst 5 files:\n";
        foreach (array_slice($songFiles, 0, 5) as $file) {
            echo "  - $file\n";
            echo "    URL: " . $disk->url($file) . "\n";
        }
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "\nTrace:\n";
    echo $e->getTraceAsString();
}
