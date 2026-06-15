@echo off
chcp 65001 >nul
cd /d "%~dp0"

where php >nul 2>nul
if errorlevel 1 (
    echo PHP не найден.
    echo Установите PHP 8 или добавьте php.exe в переменную PATH.
    echo После установки снова запустите START_SITE.bat.
    pause
    exit /b 1
)

start "" http://127.0.0.1:8080/index.php
php -S 127.0.0.1:8080
