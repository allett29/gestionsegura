# Sistema de Cotización y Venta de Seguro de Viaje

Aplicación web completa (Laravel + Vue.js + MySQL) para cotizar, descargar PDF, contratar y consultar seguros de viaje.

> El enunciado original de la prueba técnica está en [`docs/ENUNCIADO.md`](docs/ENUNCIADO.md).  
> La propuesta de arquitectura está en [`README_PROPUESTA.md`](README_PROPUESTA.md).  
> Pasos manuales (GitHub, Docker Hub, Render): [`docs/PASOS_MANUALES.md`](docs/PASOS_MANUALES.md).

---

## Requisitos

- PHP 8.4+
- Composer
- Node.js 20+
- MySQL 8+
- Docker (recomendado para entorno local y despliegue)

---

## Instalación local (sin Docker)

```bash
git clone <URL_DEL_REPOSITORIO>
cd Gestion_Segura

composer install
cp .env.example .env
php artisan key:generate

# Configura DB_* en .env apuntando a tu MySQL
php artisan migrate --seed

npm install
npm run build

php artisan serve
```

En otra terminal (desarrollo frontend con hot reload):

```bash
npm run dev
```

Abre `http://127.0.0.1:8000`.

---

## Instalación con Docker Compose (recomendada)

1. Copia el entorno y genera la clave:

```bash
cp .env.example .env
```

2. Genera `APP_KEY` (elige una opción):

```bash
# Opción A (si tienes PHP local)
php artisan key:generate

# Opción B (con Docker)
docker run --rm php:8.4-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)), PHP_EOL;"
```

Pega el valor en `APP_KEY` dentro de `.env`.

3. Levanta los servicios:

```bash
docker compose up -d --build
```

4. (Opcional) Sembrar datos demo:

```bash
docker compose exec app php artisan db:seed --force
```

5. Abre `http://localhost:8080`.

---

## Scripts útiles

```bash
# Pruebas automatizadas (PEST)
php vendor/bin/pest

# Build frontend
npm run build

# Migraciones
php artisan migrate --seed
```

---

## Arquitectura

Se implementó un **monólito modular**:

- Controllers delgados (`app/Http/Controladores`)
- Lógica de negocio en servicios (`app/Servicios`)
- Validaciones en Form Requests (`app/Http/Peticiones`)
- Modelos Eloquent (`app/Modelos`)
- Enumeraciones y excepciones de dominio
- Frontend Vue 3 SPA (`resources/js`)

### Flujo

```text
Petición HTTP
  → Petición (validación)
  → Controlador (orquestación)
  → Servicio (negocio)
  → Modelo / API externa / PDF
  → Recurso JSON o vista
```

**No se usaron microservicios**: para este dominio aportan complejidad sin valor. La separación interna por servicios es la decisión senior adecuada.

---

## Organización de la lógica de negocio

| Pieza | Responsabilidad |
|---|---|
| `ServicioCalculoCotizacion` | Días, tarifa base, recargo y total |
| `RecargoPorRegion` | Mapeo región → porcentaje |
| `ClienteRestCountries` | Integración externa con timeout, caché y fallback |
| `ServicioCotizacion` | Crear / listar / consultar |
| `ServicioContratarCotizacion` | Transición Cotizado → Contratado |
| `ServicioPdfCotizacion` | Generación del PDF |

Regla de precio:

- USD 3 por día
- Recargo por región (South America 0%, North America 15%, Europe 20%, Asia 25%, Africa 20%, Oceania 25%)
- Ejemplo España 10 días = USD 36

---

## Integración REST Countries

- Endpoint: `GET /api/paises`
- Fuente: `https://restcountries.com/`
- Se usa `subregion` (cuando existe) para distinguir South/North America
- Timeout configurable
- Caché (24h por defecto)
- Fallback local si el servicio cae y no hay caché
- Errores registrados en log sin datos sensibles

---

## API

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/paises` | Lista de países |
| POST | `/api/cotizaciones` | Crear cotización |
| GET | `/api/cotizaciones` | Listado paginado (`buscar`, `estado`, `por_pagina`) |
| GET | `/api/cotizaciones/{uuid}` | Detalle |
| POST | `/api/cotizaciones/{uuid}/contratar` | Contratar |
| GET | `/api/cotizaciones/{uuid}/pdf` | Descargar PDF |

---

## Frontend

- Vue 3 + Vue Router + Axios
- Pantallas:
  - `/` cotizar
  - `/cotizaciones` listado con búsqueda/filtro/paginación
  - `/cotizaciones/:uuid` detalle, PDF y contratar
- Responsive (escritorio y móvil)
- Validaciones en cliente + servidor

---

## Pruebas (PEST)

Cobertura incluida:

- Cálculo unitario (incluye caso España = 36)
- Crear cotización
- Validaciones 422
- Contratar + conflicto 409
- Listado paginado
- PDF
- Países (éxito y fallback)

```bash
php vendor/bin/pest
```

---

## Docker Hub

Workflow listo en `.github/workflows/docker-publicar.yml`.

### Pasos manuales (tú debes hacerlos)

1. Crea cuenta en [Docker Hub](https://hub.docker.com/).
2. En GitHub → Settings → Secrets and variables → Actions, crea:
   - `DOCKERHUB_USERNAME`
   - `DOCKERHUB_TOKEN` (Access Token de Docker Hub)
3. Publica un tag:

```bash
git tag v1.0.0
git push origin v1.0.0
```

4. O ejecuta el workflow manualmente (**workflow_dispatch**).

Pull de ejemplo:

```bash
docker pull TU_USUARIO/seguro-viaje:latest
```

---

## Despliegue en Render

Guía detallada: [`docs/DEPLOY_RENDER.md`](docs/DEPLOY_RENDER.md).

Resumen:

1. Crear MySQL en Render.
2. Crear Web Service con Dockerfile.
3. Configurar variables (`APP_KEY`, `DB_*`, `APP_URL`, etc.).
4. Desplegar y verificar.

---

## Decisiones técnicas relevantes

- Código de dominio, migraciones, carpetas propias, funciones y comentarios en **español**
- UUID público en rutas (no exponer IDs internos)
- Estados tipados con Enum (`cotizado`, `contratado`)
- Configuración de negocio en `config/seguro.php`
- JSON uniforme (`mensaje`, `data`, `meta`)
- CI con GitHub Actions para PEST

---

## Mejoras futuras (producción)

- Autenticación y roles
- Pasarela de pagos real
- Colas para PDF/email
- Rate limiting
- Observabilidad avanzada
- Disco persistente / object storage para archivos
- Extracción a servicios solo si el dominio lo exige de verdad

---

## Estructura principal

```text
app/
  Enumeraciones/
  Excepciones/
  Http/Controladores|Peticiones|Recursos/
  Modelos/
  Servicios/
  Soporte/
resources/js/
  componentes/
  composables/
  paginas/
  enrutador/
  servicios/
database/migrations|factories|seeders/
tests/Feature|Unit/
docker/
docs/
```
