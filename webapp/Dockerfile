# ── Stage 1: Build frontend assets ─────────────────────────────────────────
FROM node:22-alpine AS frontend
WORKDIR /app

COPY package*.json ./
RUN npm ci --no-audit

COPY vite.config.ts tsconfig.json postcss.config.js tailwind.config.js ./
COPY resources/ resources/
COPY public/ public/
RUN npm run build

# ── Stage 2: Production runtime ─────────────────────────────────────────────
FROM php:8.4-apache

# System libs + PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        zip \
        gd \
        intl \
        opcache \
        exif \
        bcmath \
    && apt-get purge -y --auto-remove && rm -rf /var/lib/apt/lists/*

# PHP runtime config
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

# Apache: enable mod_rewrite + headers, set document root
RUN a2enmod rewrite headers
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Composer binary (from official image, no full install needed)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP deps (no dev, no scripts — scripts need APP_KEY at runtime)
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader \
    && rm -rf /root/.composer

# Copy application code
COPY --chown=www-data:www-data . .

# Overlay build artifacts from Stage 1
COPY --from=frontend --chown=www-data:www-data /app/public/build ./public/build

# Storage & cache directories
RUN mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

EXPOSE 80
ENTRYPOINT ["docker-entrypoint"]
CMD ["apache2-foreground"]
