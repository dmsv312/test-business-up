#!/bin/sh
set -e

cd /var/www/html

# Ключ приложения — при первом старте, если ещё не задан.
if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    php artisan key:generate --force
fi

# Ждём готовности PostgreSQL.
echo "Ожидание PostgreSQL на ${DB_HOST}:${DB_PORT}..."
until php -r 'new PDO("pgsql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT").";dbname=".getenv("DB_DATABASE"), getenv("DB_USERNAME"), getenv("DB_PASSWORD"));' 2>/dev/null; do
    sleep 2
done

# Миграции + сиды (сидер идемпотентен — повторный старт не дублирует данные).
php artisan migrate --force --seed
chown -R www-data:www-data storage bootstrap/cache

exec php-fpm
