# Despliegue en Render (monólito Laravel + Vue)

## Opción recomendada: Web Service con Dockerfile

1. Crea una base **MySQL** en Render y copia host, puerto, usuario, contraseña y nombre de BD.
2. En Render → **New → Web Service** → conecta el repositorio GitHub.
3. Runtime: **Docker**.
4. Configura variables de entorno:

```env
APP_NAME=Seguro de Viaje
APP_ENV=production
APP_DEBUG=false
APP_URL=https://TU-SERVICIO.onrender.com
APP_KEY=base64:PEGA_AQUI_UNA_CLAVE_GENERADA
APP_LOCALE=es
APP_FALLBACK_LOCALE=es
DB_CONNECTION=mysql
DB_HOST=...
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
LOG_CHANNEL=stderr
EJECUTAR_MIGRACIONES=true
```

5. Genera `APP_KEY` localmente o con:

```bash
docker run --rm php:8.4-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)), PHP_EOL;"
```

6. Despliega. El `entrypoint` ejecutará migraciones automáticamente.
7. Verifica `/` (SPA), `/api/paises` y el listado de cotizaciones.

## Notas

- No subas el archivo `.env` al repositorio.
- Si Render usa disco efímero, considera un disco persistente para `storage` o usar S3 más adelante.
- Para seeders de demo (opcional): abre shell del servicio y ejecuta `php artisan db:seed --force`.
