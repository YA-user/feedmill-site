#!/usr/bin/env sh
set -e
mkdir -p /var/www/html/storage /var/www/html/public/uploads
chown -R www-data:www-data /var/www/html/storage /var/www/html/public/uploads
php /var/www/html/scripts/init.php
chown -R www-data:www-data /var/www/html/storage /var/www/html/public/uploads
exec "$@"
