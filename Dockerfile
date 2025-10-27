# Base image
FROM php:8.3-fpm-alpine AS builder

# Install system dependencies and PHP extensions
# These change infrequently and take long to install
RUN apk add --no-cache \
    icu-dev \
    postgresql-dev\
    libzip-dev \
    supervisor \
    mysql-client \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev

RUN  docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd intl zip pdo pdo_mysql

# Install Composer (rarely changes)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


WORKDIR /app

COPY composer.json composer.lock ./

COPY . .


RUN composer install --no-dev --optimize-autoloader --no-interaction --verbose



# ---------- 2️⃣  Runtime stage ----------
FROM php:8.3-fpm-alpine

# Install system dependencies and PHP extensions
# These change infrequently and take long to install
RUN apk add --no-cache \
    git \
    dcron \
    supervisor \
    mysql-client \
    icu-libs \
    libzip \
    libpng \
    libjpeg-turbo \
    freetype



WORKDIR /var/www/html

# Copy built app from builder stage
COPY --from=builder /app /var/www/html


# Copy configuration files (change occasionally)
COPY nginx.conf /etc/nginx/sites-available/default

# Copy Supervisor configuration
COPY supervisord.conf /etc/supervisor/supervisord.conf

# Copy cron job file
COPY ./laravel-cron /etc/cron.d/laravel-cron

# Set up cron (depends on cron file)
RUN chmod 0644 /etc/cron.d/laravel-cron \
    && crontab /etc/cron.d/laravel-cron



# Set permissions (after all files are copied)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose ports
EXPOSE 80

# Start services
ENTRYPOINT ["./entrypoint.sh"]
