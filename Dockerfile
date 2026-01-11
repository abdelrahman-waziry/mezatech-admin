# Stage 1: Build frontend assets
FROM node:20 as frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 2: Final Application Image
FROM php:8.2-apache

# Environment variables
ENV COMPOSER_MEMORY_LIMIT=-1 \
    COMPOSER_ALLOW_SUPERUSER=1 \
    APACHE_DOCUMENT_ROOT=/var/www/html/public

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    zip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libjpeg-dev \
    libfreetype6-dev \
    mariadb-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    zip \
    mbstring \
    exif \
    pcntl \
    bcmath \
    intl \
    gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules and configure document root
RUN a2enmod rewrite headers \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy application files common to all
COPY . .

# Copy built frontend assets
COPY --from=frontend /app/public/build public/build
# Copy manifest if it exists (Vite 5+)


# Configure git to allow operations in the directory
RUN git config --global --add safe.directory /var/www/html

# Install PHP dependencies
RUN composer update \
    && composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Update entrypoint permission
RUN chmod +x docker-entrypoint.sh

# Expose port (default Apache port)
EXPOSE 80

# Use entrypoint script
ENTRYPOINT ["./docker-entrypoint.sh"]
