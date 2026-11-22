# ══════════════════════════════════════════════════════════════════
# ZYRA E-Commerce Production Dockerfile (PHP 8.3 FPM)
# Optimized for High Load, OpCache, WebP image processing & Concurrency
# ══════════════════════════════════════════════════════════════════
FROM php:8.3-fpm-bookworm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# Configure & Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        opcache

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy custom PHP and OPcache configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Copy project files
COPY . /var/www/html

# Create system user & group to run Composer and Artisan Commands safely
RUN useradd -G www-data,root -u 1000 -d /home/zyra zyra \
    && mkdir -p /home/zyra/.composer \
    && chown -R zyra:zyra /home/zyra \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Switch to non-root user
USER zyra

# Install project dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Switch back to root to execute php-fpm
USER root

EXPOSE 9000

CMD ["php-fpm"]
