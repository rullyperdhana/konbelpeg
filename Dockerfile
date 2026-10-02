# ==========================================
# Multi-stage Dockerfile for Laravel Application
# PHP 8.4-FPM + Composer + Node.js (Vite)
# ==========================================

# --- Stage 1: Build Frontend Assets (Optional) ---
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm install --ignore-scripts || true
COPY . .
RUN if [ -f package.json ] && grep -q '"build"' package.json; then npm run build || true; fi

# --- Stage 2: Main Application (PHP 8.4 FPM) ---
FROM php:8.4-fpm-bookworm

# Set working directory
WORKDIR /var/www

# Install system dependencies required by PHP extensions, DomPDF, and DBF/Excel handlers
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    zip \
    unzip \
    netcat-openbsd \
    procps \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy custom PHP configuration
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Copy application source code
COPY . /var/www

# Copy built frontend assets from frontend stage (if any)
COPY --from=frontend /app/public /var/www/public

# Install composer production dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create necessary directories and set permissions
RUN mkdir -p /var/www/storage/framework/cache/data \
             /var/www/storage/framework/sessions \
             /var/www/storage/framework/views \
             /var/www/storage/logs \
             /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Expose PHP-FPM port
EXPOSE 9000

# Set entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Default command
CMD ["php-fpm"]
