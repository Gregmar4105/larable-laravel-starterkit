@echo off
SET command=%1

IF "%command%"=="up" (
    docker-compose up -d
) ELSE IF "%command%"=="down" (
    docker-compose down
) ELSE IF "%command%"=="restart" (
    docker-compose down && docker-compose up -d
) ELSE IF "%command%"=="test" (
    docker-compose exec app php artisan test
) ELSE IF "%command%"=="fresh" (
    docker-compose exec app php artisan migrate:fresh --seed
) ELSE IF "%command%"=="logs" (
    docker-compose logs -f
) ELSE IF "%command%"=="shell" (
    docker-compose exec app bash
) ELSE IF "%command%"=="lint" (
    docker-compose exec app ./vendor/bin/pint --test
) ELSE IF "%command%"=="build" (
    docker-compose build
) ELSE IF "%command%"=="setup" (
    powershell -ExecutionPolicy Bypass -File "%~dp0scripts\setup.ps1"
) ELSE (
    echo Usage: larable {up^|down^|restart^|test^|fresh^|logs^|shell^|lint^|build^|setup}
    exit /b 1
)
