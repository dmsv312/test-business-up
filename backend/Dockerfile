# Laravel API (PHP-FPM). nginx из соседнего образа проксирует сюда /api.
FROM php:8.4-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
        libpq-dev libzip-dev libonig-dev unzip git \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql pgsql bcmath mbstring zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Полный код копируем до composer install, чтобы post-install скрипты
# (package:discover) отработали. .env берём из примера; ключ и миграции — в entrypoint.
COPY . .
RUN cp .env.example .env \
    && composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader \
    && chmod +x docker/entrypoint.sh \
    && chown -R www-data:www-data storage bootstrap/cache

ENTRYPOINT ["docker/entrypoint.sh"]
