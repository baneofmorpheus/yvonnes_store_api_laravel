# Base image
FROM php:8.3-fpm-alpine AS builder

# Install system dependencies and PHP extensions
# These change infrequently and take long to install
RUN apk add --no-cache  --virtual .build-deps   \
    postgresql-dev\
    libzip-dev \
    libpng-dev  \
    supervisor \
    mysql-client \
    libwebp-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    mariadb-connector-c-dev

RUN  docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd  zip pdo pdo_mysql \
    && apk del .build-deps

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
RUN apk add --no-cache  \
    git \
    bash \
    dcron \
    supervisor \
    mysql-client \
    libpng \
    libwebp  \
    libjpeg-turbo \
    freetype\
    libzip \
    && mkdir -p /var/log/supervisor



WORKDIR /var/www/html

# Copy built app from builder stage
COPY --from=builder /app /var/www/html
COPY --from=builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=builder /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d


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
