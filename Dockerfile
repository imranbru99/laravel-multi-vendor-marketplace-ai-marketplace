FROM node:22-bookworm-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

FROM php:8.5-fpm-bookworm AS app
RUN apt-get update && apt-get install -y --no-install-recommends git unzip libicu-dev libpq-dev libzip-dev libonig-dev libxml2-dev \
 && docker-php-ext-install bcmath intl pdo_pgsql pdo_mysql zip pcntl mbstring exif \
 && pecl install redis-6.2.0 && docker-php-ext-enable redis opcache \
 && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist
COPY . .
COPY --from=assets /app/public/build ./public/build
RUN composer dump-autoload --no-dev --optimize \
 && php artisan filament:assets \
 && rm -f public/storage && ln -s /var/www/html/storage/app/public public/storage \
 && chown -R www-data:www-data storage bootstrap/cache
USER www-data
EXPOSE 9000
CMD ["php-fpm"]

FROM nginx:1.28-alpine AS web
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=app /var/www/html/public /var/www/html/public
COPY --from=assets /app/public/build /var/www/html/public/build
COPY --from=app /var/www/html/public/css/filament /var/www/html/public/css/filament
COPY --from=app /var/www/html/public/js/filament /var/www/html/public/js/filament
COPY --from=app /var/www/html/public/fonts/filament /var/www/html/public/fonts/filament
EXPOSE 80
