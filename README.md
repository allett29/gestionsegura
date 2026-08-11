# Sistema de Cotización y Venta de Seguro de Viaje

Aplicación web para cotizar un seguro de viaje, descargar la cotización en PDF, confirmar la contratación y consultar el historial de cotizaciones.

**Demostración:** https://gestionsegura.onrender.com

**Tecnologías:** Laravel · Vue.js · MySQL · Git · PEST

---

## Funcionalidades implementadas

- Formulario de cotización con validaciones en frontend y backend.
- Listado de países desde REST Countries API (v5).
- Cálculo del valor del seguro según días de viaje y región de destino.
- Descarga de cotización en PDF.
- Contratación con persistencia de datos y cambio de estado (`cotizado` → `contratado`).
- Consulta de cotizaciones con búsqueda, filtro por estado y paginación.

---

## Instalación

### Requisitos

- PHP 8.4+, Composer, Node.js 20+, MySQL 8+
- Docker y Docker Compose (opcional)

### Con Docker Compose

```bash
git clone https://github.com/allett29/gestionsegura.git
cd gestionsegura

cp .env.example .env
php artisan key:generate

docker compose up -d --build
docker compose exec app php artisan db:seed --force
```

La aplicación queda disponible en http://localhost:8080

### Sin Docker

```bash
composer install
cp .env.example .env
php artisan key:generate

# Configurar DB_* y REST_COUNTRIES_API_KEY en .env
php artisan migrate --seed

npm install
npm run build
php artisan serve
```

Variables de entorno: ver `.env.example`.

---

## Arquitectura

Se adoptó un **monólito modular**: backend Laravel expone una API REST y el frontend Vue 3 funciona como SPA. La lógica de negocio no reside en controladores ni en componentes Vue.

### Por qué esta arquitectura

- **Monólito modular:** el alcance funcional es acotado (cotizar, contratar y consultar). Dividir en microservicios añadiría complejidad de despliegue y comunicación sin beneficio claro para este dominio.
- **API REST + SPA Vue:** el backend concentra validaciones y reglas de negocio; el frontend se limita a captura de datos y presentación, lo que facilita pruebas y mantenimiento.
- **Servicios de dominio:** el cálculo del seguro y la integración con REST Countries son reglas independientes de HTTP; aislarlas permite probarlas con PEST sin levantar toda la interfaz.

```text
Petición HTTP
  → Form Request (validación)
  → Controlador (orquestación)
  → Servicio (negocio)
  → Modelo / API externa / PDF
  → Respuesta JSON o vista
```

### Organización de la lógica de negocio

| Servicio | Responsabilidad |
|---|---|
| `ServicioCalculoCotizacion` | Días de viaje, tarifa base, recargo regional y total |
| `RecargoPorRegion` | Normalización de región y porcentaje de recargo |
| `ClienteRestCountries` | Consumo de REST Countries v5 |
| `ServicioCotizacion` | Creación, consulta y listado de cotizaciones |
| `ServicioContratarCotizacion` | Contratación y transición de estado |
| `ServicioPdfCotizacion` | Generación del PDF |

### Integración con REST Countries

- Endpoint de la aplicación: `GET /api/paises`
- Fuente externa: REST Countries v5 (`https://api.restcountries.com/countries/v5`)
- Autenticación mediante `REST_COUNTRIES_API_KEY`
- Paginación, caché, timeout y tratamiento de errores de red o respuestas inválidas
- Uso de `subregion` cuando existe, para distinguir regiones dentro de América
- Bandera del país en el selector de destino (URL desde la API)

### Regla de cálculo

- Tarifa base: USD 3 por día de viaje
- Recargos por región: South America 0%, North America 15%, Europe 20%, Asia 25%, Africa 20%, Oceania 25%
- Ejemplo: viaje de 10 días a España → USD 36

### Decisiones técnicas

- Servicios de dominio separados de la capa HTTP.
- UUID como identificador público en rutas y API.
- Estados modelados con Enum de PHP.
- Parámetros de negocio en `config/seguro.php`.
- Migraciones Laravel para el esquema de base de datos.
- Respuestas JSON con estructura uniforme (`mensaje`, `data`, `meta`).

---

## API

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/paises` | Países disponibles |
| POST | `/api/cotizaciones` | Crear cotización |
| GET | `/api/cotizaciones` | Listado paginado |
| GET | `/api/cotizaciones/{uuid}` | Detalle |
| POST | `/api/cotizaciones/{uuid}/contratar` | Contratar |
| GET | `/api/cotizaciones/{uuid}/pdf` | PDF de la cotización |

---

## Frontend

| Ruta | Pantalla |
|---|---|
| `/` | Cotización |
| `/cotizaciones` | Listado de cotizaciones |
| `/cotizaciones/:uuid` | Detalle, PDF y contratación |

Vue 3, Vue Router y Axios. Interfaz responsive.

---

## Pruebas automatizadas

```bash
php vendor/bin/pest
```

Cobertura principal:

- Cálculo de cotización (incluye caso España = USD 36)
- Creación y validaciones de cotizaciones
- Contratación y conflictos de estado
- Listado paginado y PDF
- Integración de países

---

## Mejoras futuras

De evolucionar a un sistema productivo, se consideraría:

- Autenticación de usuarios y panel administrativo
- Pasarela de pagos
- Procesamiento asíncrono de PDF y correos
- Rate limiting, auditoría y monitoreo
- Almacenamiento de documentos en servicio dedicado

---

## Estructura del repositorio

```text
app/Servicios/          Lógica de negocio
app/Http/               Controladores, peticiones y recursos
app/Modelos/            Modelos Eloquent
resources/js/           Frontend Vue 3
database/migrations/    Esquema de base de datos
tests/                  Pruebas PEST
```
