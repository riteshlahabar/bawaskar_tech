# Step 1: Use an official PHP 8.3 runtime with FPM
FROM php:8.3-fpm-alpine

# Step 2: Install system dependencies required for Laravel & Extensions
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    linux-headers \
    libzip-dev \
    icu-dev

# Step 3: Install PHP extensions needed for Laravel core
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

# Step 4: Install Composer globally inside the container
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Step 5: Set the working directory inside the container
WORKDIR /var/www

# Step 6: Copy your existing Laravel application code into the container
COPY . .

# Step 7: Install Laravel dependencies using Composer
RUN composer install --no-interaction --optimize-autoloader --ignore-platform-reqs

# Step 8: Set permissions for Laravel storage and bootstrap cache folders
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Step 9: Expose port 80 for web traffic
EXPOSE 80

# Step 10: Run the PHP-FPM server
CMD ["php-fpm"]
