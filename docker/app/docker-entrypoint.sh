#!/bin/sh
set -e

echo "🚀 Starting container in environment: ${APP_ENV:-production}"

if [ ! -f "./artisan" ]; then
    echo "❌ Invalid Laravel project directory: $PWD"
    exit 1
fi

# If local environment, prepare
if [ "$APP_ENV" = "local" ]; then
    if [ ! -f "./vendor/autoload.php" ]; then
        echo "📦 Running composer install (local development)..."
        composer install

        echo "🔑 Running artisan key:generate (local development)..."
        php artisan key:generate

        echo "🧹 Clearing config cache..."
        php artisan config:clear

        echo "🎯 Running database migrations and seeds (local development)..."
        php artisan migrate --seed
    fi
fi

exec "$@"