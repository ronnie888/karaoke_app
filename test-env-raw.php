<?php

// Read .env file directly without Laravel
$envFile = __DIR__ . '/.env';
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

echo "Reading .env file directly:\n";
echo "===========================\n\n";

foreach ($lines as $line) {
    if (strpos($line, 'DO_SPACES') !== false) {
        echo $line . "\n";
    }
}
