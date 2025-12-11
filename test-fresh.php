<?php

// Completely fresh start
putenv('DO_SPACES_KEY');
putenv('DO_SPACES_SECRET');

require __DIR__.'/vendor/autoload.php';

// Clear phpdotenv cache
if (file_exists(__DIR__ . '/vendor/vlucas/phpdotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Fresh test:\n";
echo "===========\n";
echo "DO_SPACES_KEY from config: " . config('filesystems.disks.spaces.key') . "\n";
echo "DO_SPACES_KEY from env(): " . env('DO_SPACES_KEY') . "\n";
echo "DO_SPACES_KEY from getenv(): " . getenv('DO_SPACES_KEY') . "\n";
