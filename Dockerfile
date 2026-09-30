FROM node:22-bookworm-slim AS frontend

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM litestream/litestream:0.5.15 AS litestream

FROM php:8.5-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends fonts-noto-cjk libfreetype6-dev libonig-dev libsqlite3-dev libxml2-dev libzip-dev unzip \
    && docker-php-ext-configure gd --with-freetype \
    && docker-php-ext-install gd mbstring pdo_sqlite xml zip \
    && a2enmod rewrite proxy proxy_http proxy_wstunnel \
    && sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=litestream /usr/local/bin/litestream /usr/local/bin/litestream

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts --optimize-autoloader

COPY . .
COPY --from=frontend /app/public/build ./public/build
COPY --from=frontend /app/node_modules ./node_modules
COPY --from=frontend /usr/local/bin/node /usr/local/bin/node
COPY apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY litestream.yml /etc/litestream.yml
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint

RUN rm -f bootstrap/cache/packages.php bootstrap/cache/services.php \
    && chmod +x /usr/local/bin/docker-entrypoint \
    && php artisan package:discover --ansi \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

ENV APP_ENV=production \
    APP_DEBUG=false \
    PORT=8080

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/docker-entrypoint"]
