# Base image
FROM php:8.3-fpm

# Install system dependencies and PHP extensions
# These change infrequently and take long to install
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    cron \
    supervisor \
    default-mysql-client \
    libfreetype-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd intl zip pdo pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer (rarely changes)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy configuration files (change occasionally)
COPY nginx.conf /etc/nginx/sites-available/default

# Copy Supervisor configuration
COPY supervisord.conf /etc/supervisor/supervisord.conf

# Copy cron job file
COPY ./laravel-cron /etc/cron.d/laravel-cron

# Set up cron (depends on cron file)
RUN chmod 0644 /etc/cron.d/laravel-cron \
    && crontab /etc/cron.d/laravel-cron

# Set working directory
WORKDIR /var/www/html

# Copy dependency files first
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-scripts --no-autoloader

# Copy application code (changes frequently)
COPY . .

# Run post-install scripts and generate autoloader
RUN composer dump-autoload --optimize

# Set permissions (after all files are copied)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose ports
EXPOSE 80

# Start services
ENTRYPOINT ["./entrypoint.sh"]
