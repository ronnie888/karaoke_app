@echo off
echo ==========================================
echo Fixing PHP cURL SSL Certificate Issue
echo ==========================================
echo.

echo Step 1: Downloading latest CA certificate bundle...
curl -o C:\php8.3\cacert.pem https://curl.se/ca/cacert.pem
echo.

if exist "C:\php8.3\cacert.pem" (
    echo ✓ Downloaded successfully to C:\php8.3\cacert.pem
    echo.

    echo Step 2: Updating php.ini...
    echo.
    echo Please open C:\php8.3\php.ini and add/update this line:
    echo curl.cainfo = "C:\php8.3\cacert.pem"
    echo.
    echo After saving php.ini, restart your terminal and run:
    echo php test-spaces-detailed.php
    echo.
) else (
    echo ✗ Download failed. Please:
    echo 1. Download manually from: https://curl.se/ca/cacert.pem
    echo 2. Save to: C:\php8.3\cacert.pem
    echo 3. Update php.ini: curl.cainfo = "C:\php8.3\cacert.pem"
)

pause
