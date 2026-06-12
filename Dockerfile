FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (PENTING: pdo_mysql untuk MySQL)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . /var/www

# Install composer dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Jalankan migrasi otomatis, lalu hidupkan PHP-FPM & Nginx
CMD php artisan migrate --force && service nginx start && php-fpm

# Setup Nginx configuration
COPY ./nginx.conf /etc/nginx/sites-available/default

EXPOSE 80

CMD service nginx start && php-fpm