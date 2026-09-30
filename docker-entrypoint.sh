#!/bin/sh
set -eu

DB_PATH=/var/www/html/database/database.sqlite

mkdir -p "$(dirname "$DB_PATH")"

litestream restore -if-db-not-exists -if-replica-exists "$DB_PATH"
chown -R www-data:www-data "$(dirname "$DB_PATH")"

if [ ! -f "$DB_PATH" ]; then
    su -s /bin/sh www-data -c 'touch database/database.sqlite'
fi

su -s /bin/sh www-data -c 'php artisan migrate --force'

exec litestream replicate -config /etc/litestream.yml -exec "apache2-foreground"
