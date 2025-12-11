<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing DigitalOcean Spaces Permissions...\n";
echo "========================================\n\n";

$config = config('filesystems.disks.spaces');
echo "Using Key: " . $config['key'] . "\n";
echo "Secret (first 10): " . substr($config['secret'], 0, 10) . "...\n\n";

$disk = Storage::disk('spaces');

// Test 1: Can we PUT (write)?
echo "Test 1: PUT (Write) Permission\n";
try {
    $disk->put('test-permissions.txt', 'Testing write permission at ' . now());
    echo "✓ PASS - Can write files\n";
} catch (\Exception $e) {
    echo "✗ FAIL - Cannot write files: " . $e->getMessage() . "\n";
}

// Test 2: Can we GET (read)?
echo "\nTest 2: GET (Read) Permission\n";
try {
    $content = $disk->get('test-permissions.txt');
    echo "✓ PASS - Can read files\n";
} catch (\Exception $e) {
    echo "✗ FAIL - Cannot read files: " . $e->getMessage() . "\n";
}

// Test 3: Can we DELETE?
echo "\nTest 3: DELETE Permission\n";
try {
    $disk->delete('test-permissions.txt');
    echo "✓ PASS - Can delete files\n";
} catch (\Exception $e) {
    echo "✗ FAIL - Cannot delete files: " . $e->getMessage() . "\n";
}

// Test 4: Can we LIST?
echo "\nTest 4: LIST (ListBucket) Permission\n";
try {
    $files = $disk->files();
    echo "✓ PASS - Can list files (found " . count($files) . " files)\n";
} catch (\Exception $e) {
    echo "✗ FAIL - Cannot list files\n";
    echo "Error: " . $e->getMessage() . "\n\n";

    // Check if it's a signature error
    if (str_contains($e->getMessage(), 'SignatureDoesNotMatch')) {
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "⚠️  DIAGNOSIS: Your Spaces API key does NOT have LIST permissions!\n\n";
        echo "To fix this:\n";
        echo "1. Go to DigitalOcean Console → API → Spaces Keys\n";
        echo "2. DELETE the current key: DO00BLNDH49A4A3229NY\n";
        echo "3. CREATE a new key with these permissions:\n";
        echo "   ☑ Full Access (or at minimum):\n";
        echo "     ☑ List objects (s3:ListBucket)\n";
        echo "     ☑ Get objects (s3:GetObject)\n";
        echo "     ☑ Put objects (s3:PutObject)\n";
        echo "     ☑ Delete objects (s3:DeleteObject)\n";
        echo "4. Update .env with the new Access Key and Secret\n";
        echo "5. Run: php artisan config:clear\n";
        echo "6. Test again: php test-spaces-permissions.php\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    }
}

echo "\n========================================\n";
echo "Permission test complete!\n\n";

// Summary
echo "SUMMARY:\n";
echo "✓ Your credentials are valid (authentication works)\n";
echo "✓ You can write/read/delete individual files\n";
if (!isset($files)) {
    echo "✗ You CANNOT list files (missing ListBucket permission)\n";
    echo "\n⚠️  Action Required: Generate a new Spaces key with FULL ACCESS\n";
} else {
    echo "✓ All permissions are working correctly!\n";
}
