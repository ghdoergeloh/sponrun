# Legacy SponRun — Laravel 5.5, PHP 7.4, Apache
FROM php:7.4-apache

# System libs + PHP extensions required by Laravel 5.5
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        unzip \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        zip \
        gd \
        intl \
        bcmath \
        exif \
    && apt-get purge -y --auto-remove && rm -rf /var/lib/apt/lists/*

# Apache: enable mod_rewrite
RUN a2enmod rewrite

COPY docker/apache-legacy.conf /etc/apache2/sites-available/000-default.conf

# Composer 2 is backward-compatible with L5.5 lock files
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader \
    && rm -rf /root/.composer

# Copy application (after vendor install to leverage layer cache)
COPY --chown=www-data:www-data . .

# Storage & cache permissions
RUN mkdir -p storage/app storage/framework/cache storage/framework/sessions \
             storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/entrypoint-legacy.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

EXPOSE 80
ENTRYPOINT ["docker-entrypoint"]
CMD ["apache2-foreground"]
