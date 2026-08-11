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

# Render inyecta PORT; Apache debe escuchar en ese puerto.
PORT="${PORT:-80}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

php artisan config:clear || true
php artisan storage:link || true

if [ "${EJECUTAR_MIGRACIONES:-true}" = "true" ]; then
  php artisan migrate --force
fi

exec "$@"
