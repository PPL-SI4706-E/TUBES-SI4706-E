@echo off
echo ============================================
echo  TirtaBantu - Run Export Tests (PBI-15)
echo ============================================
echo.

cd /d "%~dp0"

php artisan test --filter=ExportLaporanTest --colors=always
pause
