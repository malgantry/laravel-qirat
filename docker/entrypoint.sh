#!/usr/bin/env sh
set -e

cd /var/www/html

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

if [ ! -f .env ]; then
    cp .env.example .env
fi

# Generate key if missing or empty
APP_KEY_VALUE=$(grep '^APP_KEY=' .env | cut -d '=' -f2-)
if [ -z "$APP_KEY_VALUE" ]; then
    php artisan key:generate --force
fi

# Run migrations; ignore failure if DB not yet reachable to keep container alive
php artisan migrate --force --seed || true

php artisan serve --host=0.0.0.0 --port=8000
