# ─── Stage: Base PHP 8.3 FPM with extensions ───────────────────────────
FROM php:8.3-fpm AS base

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        bcmath \
        gd \
        zip \
        mbstring \
        xml \
        pcntl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ─── Stage: Development (uses PHP-FPM served via Nginx sidecar) ───────
FROM base AS development

# Copy application code
COPY . .

# Install dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

EXPOSE 9000

CMD ["php-fpm"]

# ─── Stage: Production (Nginx + PHP-FPM) ──────────────────────────────
FROM base AS production-php

RUN docker-php-ext-install opcache

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

COPY . .

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

FROM nginx:alpine AS production

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=production-php /var/www/html /var/www/html

EXPOSE 80
