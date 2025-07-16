@echo off
echo ========================================
echo Subscription Auto-Expiration Script
echo ========================================
echo.

REM Change to the project directory
cd /d "C:\xampp\htdocs\amit-kumar"

REM Check if PHP is available
php --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: PHP is not found in PATH
    echo Please ensure XAMPP is installed and PHP is in your PATH
    pause
    exit /b 1
)

echo Running auto-expiration script...
echo.

REM Run the auto-expiration script
php admin\inc\auto_subscription_end.php

echo.
echo ========================================
echo Script completed!
echo ========================================
echo.
echo Press any key to exit...
pause >nul 