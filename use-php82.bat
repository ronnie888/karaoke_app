@echo off
echo ==========================================
echo Switching to PHP 8.2 for this project
echo ==========================================
echo.

set PATH=C:\php8.2;%PATH%

echo ✓ PHP 8.2 is now first in PATH for this terminal session
echo.

php -v
echo.

echo ==========================================
echo You can now run Laravel commands with PHP 8.2:
echo   - php artisan serve
echo   - php artisan karaoke:index "D:\HD KARAOKE SONGS" --limit=5
echo   - php test-spaces-detailed.php
echo.
echo NOTE: This only affects the current terminal.
echo To make it permanent, update your Windows Environment Variables.
echo ==========================================

cmd /k
