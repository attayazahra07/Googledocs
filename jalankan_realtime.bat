@echo off
title Menjalankan Aplikasi Real-time Habit Tracker
echo ==================================================
echo MEMULAI APLIKASI COLLABORATIVE HABIT TRACKER...
echo ==================================================
echo.

:: 1. Jalankan Reverb WebSockets di window baru
echo [1/2] Menjalankan Server WebSocket Reverb di port 8080...
start cmd /k "title Laravel Reverb WebSocket Server && php artisan reverb:start"

:: 2. Jalankan Laravel Web Server
echo [2/2] Menjalankan Laravel Web Server di http://127.0.0.1:8000...
echo.
echo Aplikasi siap digunakan! Jangan tutup window ini selama menggunakan aplikasi.
echo.
php artisan serve
