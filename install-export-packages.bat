@echo off
echo ============================================
echo  TirtaBantu - Install Export Packages
echo ============================================
echo.

cd /d "%~dp0"

echo [1/3] Installing maatwebsite/excel and barryvdh/laravel-dompdf...
composer require maatwebsite/excel barryvdh/laravel-dompdf --no-interaction
if errorlevel 1 (
    echo ERROR: composer require failed!
    pause
    exit /b 1
)

echo.
echo [2/3] Publishing DomPDF config...
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider" --force

echo.
echo [3/3] Clearing config cache...
php artisan config:clear
php artisan cache:clear

echo.
echo ============================================
echo  DONE! Packages installed successfully.
echo  You can now use Export PDF and Export Excel.
echo ============================================
pause
