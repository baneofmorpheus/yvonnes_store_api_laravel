#!/bin/bash

env >/var/cron.env

composer install

chown -R www-data:www-data /var/www/html
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

crond

php artisan migrate

supervisord -c /etc/supervisor/supervisord.conf

php artisan queue:restart
php artisan config:clear

#scout setup
php artisan scout:sync-index-settings
php artisan scout:import "App\Models\Invoice"
php artisan scout:import "App\Models\Product"
php artisan scout:import "App\Models\Customer"
php artisan scout:import "App\Models\Supplier"

php-fpm &
nginx -g 'daemon off;'
