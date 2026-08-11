#!/bin/sh
set -e

echo "Preparando aplicación Laravel..."

# El volumen de storage puede llegar vacío: aseguramos estructura y permisos.
mkdir -p \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  storage/app/public \
  bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rwx storage bootstrap/cache || true

php artisan config:clear || true
php artisan storage:link || true

if [ "${EJECUTAR_MIGRACIONES:-true}" = "true" ]; then
  php artisan migrate --force || true
fi

exec "$@"
