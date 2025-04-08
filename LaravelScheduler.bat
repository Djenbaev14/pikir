@echo off
cd /d C:\laragon\www\pikir
php artisan schedule:run >> nul 2>&1
