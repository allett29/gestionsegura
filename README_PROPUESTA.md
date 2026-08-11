# PROPUESTA TÉCNICA — Sistema de Cotización y Venta de Seguro de Viaje

Documento de diseño para revisión previa a la implementación.  
Enfoque: **sencillo, completo, funcional y senior** — monólito modular bien cortado, sin overengineering artificial.

**Criterio de decisión:** calidad, claridad, encaje con el enunciado, valor real vs complejidad innecesaria, y facilidad de despliegue/mantenimiento.  
El tiempo **no** es un factor de recorte: se entrega todo lo obligatorio y los bonus que aportan valor real.

---

## 1. Visión de la solución

Aplicación web full-stack que permite:

1. Cotizar un seguro de viaje.
2. Descargar la cotización en PDF.
3. Confirmar la contratación.
4. Consultar cotizaciones/contrataciones registradas.

Stack obligatorio respetado:

| Tecnología | Uso |
|---|---|
| Laravel (API + dominio) | Backend, validaciones, lógica de negocio, PDF, persistencia |
| Vue.js (SPA con Vue Router) | Frontend responsive |
| MySQL | Persistencia |
| Git | Historial limpio y commits semánticos |
| PEST | Pruebas automatizadas |

**Decisión de integración frontend–backend:**  
**Laravel API REST + Vue 3 (Composition API) + Vue Router + Axios**, en el mismo proyecto Laravel con Vite.

Motivo: desacopla capas, facilita pruebas del backend, mantiene un solo deploy unitario y evita mezclar lógica de negocio en componentes Vue.

---

## 2. Arquitectura elegida: monólito modular (no microservicios)

### 2.1 Decisión

Se adopta un **monólito modular** con límites claros de dominio dentro del mismo codebase.

**No se usarán microservicios.**

### 2.2 Por qué no microservicios (criterio de calidad, no de tiempo)

Para este caso de negocio (cotizar → calcular → contratar → listar), los microservicios añadirían:

- Contratos HTTP internos innecesarios
- Complejidad de red, CORS y fallos parciales
- Múltiples deploys y bases de datos sin beneficio de negocio
- Ruido arquitectónico que oscurece lo que sí se evalúa: organización del dominio, validaciones, pricing e integración externa

Un monólito modular demuestra madurez senior porque:

- Separa responsabilidades **donde importa** (Services, Clients, Enums, Requests)
- Es fácil de entender, probar y desplegar
- Escala a módulos internos si el producto crece (paso previo natural antes de partir servicios)

### 2.3 Principios de arquitectura

1. **Controllers delgados** — solo orquestan HTTP (request → service → response).
2. **Lógica de negocio en Services / Actions** — pricing, países, cotizaciones, contratación, PDF.
3. **Form Requests** — validación de entrada en backend.
4. **Modelos Eloquent limpios** — relaciones, casts, scopes; sin reglas de tarifa dentro del modelo.
5. **Enums** — estados tipados.
6. **Excepciones de dominio** — errores controlados (API externa, cotización no contratable, etc.).
7. **Responses consistentes** — JSON uniforme (`data`, `message`, `meta`).
8. **Validación dual** — frontend (UX) + backend (fuente de verdad).
9. **Testabilidad** — servicios inyectables; HTTP client mockeable.
10. **Despliegue simple y reproducible** — Docker + Render + imagen en Docker Hub.

---

## 3. Cobertura punto por punto del enunciado

### 3.1 Cotización

**Pantalla `/` (Cotizar)**

Formulario con:

**Asegurado**

- Nombres
- Apellidos
- Número de identificación
- Correo electrónico
- Fecha de nacimiento

**Viaje**

- País de destino (select alimentado por API)
- Fecha de salida
- Fecha de regreso

**Flujo**

1. Usuario completa el formulario.
2. Frontend valida campos.
3. `POST /api/quotes` crea la cotización (estado `quoted`).
4. Se muestra resumen: días, tarifa base, recargo %, total USD.
5. Acciones: **Descargar PDF** y **Contratar seguro**.

**PDF**

- Endpoint: `GET /api/quotes/{uuid}/pdf`
- Generación con **Barryvdh DomPDF** (o equivalente estable).
- Contenido: datos del asegurado, destino, fechas, desglose de tarifa, total, estado, fecha de emisión.

---

### 3.2 Integración REST Countries

**Servicio:** `App\Services\Country\RestCountriesClient`

- Endpoint usado: `GET https://restcountries.com/v3.1/all?fields=name,cca2,region,flags`
- Mapeo interno a DTO: `name`, `iso_code`, `region`, `flag_url`.
- Timeout configurado (ej. 5s).
- Retry controlado (1 reintento ante fallo transitorio).
- Manejo de:
  - timeout / connection error → excepción controlada + mensaje amigable
  - HTTP no 2xx → log + respuesta 502/503 de API
  - JSON inválido / estructura inesperada → validación defensiva
- **Caché:** `Cache::remember('countries.all', 24h)`
- **Fallback:** si falla y no hay caché, lista mínima seedada o error claro sin tumbar la app
- Endpoint app: `GET /api/countries`

La región del país alimenta el recargo. Se normaliza el valor de REST Countries a las claves del enunciado.

---

### 3.3 Cálculo de la cotización

**Servicio dedicado:** `App\Services\Quote\QuotePricingService`

Regla:

```text
días = (fecha_regreso - fecha_salida) + 1   // ambos días inclusive
tarifa_base = días × 3 USD
recargo_% = según región
recargo_valor = tarifa_base × (recargo_% / 100)
total = tarifa_base + recargo_valor
```

Tabla de recargos (en `config/insurance.php`):

| Región | Recargo |
|---|---|
| South America | 0% |
| North America | 15% |
| Europe | 20% |
| Asia | 25% |
| Africa | 20% |
| Oceania | 25% |
| Otras / desconocida | política definida y documentada (rechazo o recargo por defecto) |

**Por qué Service + config:**  
Testeable, independiente de HTTP, ajustable sin tocar controllers. Punto especialmente evaluado del enunciado.

---

### 3.4 Confirmación de contratación

**Acción:** `POST /api/quotes/{uuid}/contract`

Reglas:

- Solo se puede contratar si el estado es `quoted`.
- Al contratar se garantiza persistencia de:
  - asegurado
  - destino
  - fechas del viaje
  - cantidad de días
  - tarifa base
  - porcentaje de recargo
  - valor total
  - fecha de contratación (`contracted_at`)
  - estado → `contracted`
- Sin pasarela de pagos real.

Estados (Enum):

- `quoted` → Cotizado
- `contracted` → Contratado

---

### 3.5 Consulta de contrataciones

**Pantalla `/quotes`**

Tabla responsive con:

- Cliente
- Identificación
- Destino
- Fecha de salida
- Fecha de regreso
- Valor
- Estado
- Fecha de creación

Extras incluidos (aportan usabilidad real):

- Paginación
- Búsqueda por nombre / identificación / destino
- Filtro por estado

Endpoint: `GET /api/quotes?search=&status=&page=`

---

### 3.6 Base de datos

Solo migraciones Laravel + Eloquent. Sin SQL como mecanismo principal de entrega.

**Tabla principal: `quotes`**

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| uuid | uuid unique | URLs públicas |
| first_name | string | |
| last_name | string | |
| document_number | string | index |
| email | string | |
| birth_date | date | |
| destination_country | string | nombre |
| destination_iso | string(2) | |
| destination_region | string | |
| departure_date | date | |
| return_date | date | |
| travel_days | unsigned int | |
| base_rate | decimal(10,2) | |
| surcharge_percent | decimal(5,2) | |
| total_amount | decimal(10,2) | |
| currency | string(3) default USD | |
| status | string/enum | quoted \| contracted |
| contracted_at | timestamp nullable | |
| timestamps | created_at / updated_at | |

**Modelado**

- Decisión: **modelo único `Quote` autosuficiente**
- Scopes: `quoted()`, `contracted()`
- Accessors de presentación (`full_name`, labels de estado)
- No se parte en tablas artificiales sin valor de negocio

**Seeders / Factories**

- `QuoteSeeder` con datos de demo (varios estados/regiones)
- `QuoteFactory` para tests y seeders

---

## 4. Validaciones propuestas (criterio senior)

### Backend (Form Requests)

**Crear cotización**

- `first_name`, `last_name`: required, string, max:100
- `document_number`: required, string, max:30, formato alfanumérico razonable
- `email`: required, email, max:150
- `birth_date`: required, date, before:today, edad mínima 18
- `destination_iso`: required, size:2, validado contra catálogo de países
- `departure_date`: required, date, after_or_equal:today
- `return_date`: required, date, after_or_equal:departure_date
- Duración máxima razonable (ej. 180 días)

**Contratar**

- Quote existente
- Estado `quoted`
- Comportamiento ante ya contratada: `409 Conflict` (documentado)

### Frontend

- Validación inmediata en español
- Inputs date con min/max
- Select de países con búsqueda
- Bloqueo de submit durante request
- Errores de API / red visibles

---

## 5. Docker, Docker Hub y despliegue en Render

### 5.1 Docker (incluido)

Objetivo: entorno local reproducible y base de deploy.

Archivos:

- `Dockerfile` (multi-stage: build frontend + runtime PHP/Laravel)
- `docker-compose.yml` (app + MySQL + opcional Redis para caché)
- `.dockerignore`
- `docker/entrypoint.sh` (migrate / optimizaciones controladas por env)

Servicios Compose:

| Servicio | Rol |
|---|---|
| `app` | Laravel + assets compilados |
| `mysql` | Base de datos |
| `redis` (opcional) | Caché de países |

Comandos documentados en README:

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --seed
```

### 5.2 Docker Hub (incluido como plus de entrega)

- Imagen publicada (ej. `usuario/gestion-segura-viaje:latest`)
- Tags semánticos (`latest`, `v1.0.0`)
- README con pull + run
- Credenciales **nunca** en el repo; solo secrets de CI

Valor: portabilidad, demo rápida y señal de madurez en entrega.

### 5.3 Render (incluido)

Despliegue del **monólito** (no de varios servicios de negocio):

| Recurso Render | Uso |
|---|---|
| Web Service | App Laravel+Vue (Docker o native build) |
| MySQL | Base de datos gestionada |
| Env vars | `APP_KEY`, `DB_*`, `CACHE_*`, `APP_URL`, etc. |

Documentación en README:

1. Crear DB MySQL en Render
2. Crear Web Service desde Dockerfile o repo
3. Configurar variables de entorno
4. Build/start commands
5. Migraciones en release (`php artisan migrate --force`)
6. URL pública de demo

Estrategia preferida: **deploy por Dockerfile** → mismo artefacto local/CI/Render/Docker Hub.

---

## 6. Estructura de carpetas propuesta

```text
Gestion_Segura/
├── app/
│   ├── Enums/
│   │   └── QuoteStatus.php
│   ├── Exceptions/
│   │   ├── CountryServiceException.php
│   │   └── QuoteNotContractableException.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── CountryController.php
│   │   │       └── QuoteController.php
│   ├── Requests/
│   │   │   ├── StoreQuoteRequest.php
│   │   │   └── ListQuotesRequest.php
│   │   └── Resources/
│   │       └── QuoteResource.php
│   ├── Models/
│   │   └── Quote.php
│   ├── Services/
│   │   ├── Country/
│   │   │   ├── CountryData.php
│   │   │   └── RestCountriesClient.php
│   │   ├── Quote/
│   │   │   ├── QuotePricingService.php
│   │   │   ├── QuoteService.php
│   │   │   └── ContractQuoteService.php
│   │   └── Pdf/
│   │       └── QuotePdfService.php
│   └── Support/
│       └── RegionSurcharge.php
├── config/
│   └── insurance.php
├── database/
│   ├── factories/
│   │   └── QuoteFactory.php
│   ├── migrations/
│   │   └── xxxx_create_quotes_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── QuoteSeeder.php
├── docker/
│   └── entrypoint.sh
├── docs/
│   ├── ENUNCIADO.md                 # enunciado original (movido desde README raíz)
│   └── DEPLOY_RENDER.md             # guía detallada de despliegue
├── resources/
│   ├── js/
│   │   ├── app.js
│   │   ├── bootstrap.js
│   │   ├── components/
│   │   │   ├── QuoteForm.vue
│   │   │   ├── QuoteSummary.vue
│   │   │   ├── QuotesTable.vue
│   │   │   └── ui/
│   │   │       ├── AppButton.vue
│   │   │       ├── AppInput.vue
│   │   │       ├── AppSelect.vue
│   │   │       └── AppAlert.vue
│   │   ├── composables/
│   │   │   ├── useCountries.js
│   │   │   └── useQuotes.js
│   │   ├── pages/
│   │   │   ├── QuoteCreatePage.vue
│   │   │   ├── QuoteDetailPage.vue
│   │   │   └── QuotesIndexPage.vue
│   │   ├── router/
│   │   │   └── index.js
│   │   └── services/
│   │       └── api.js
│   ├── css/
│   │   └── app.css
│   └── views/
│       ├── app.blade.php
│       └── pdf/
│           └── quote.blade.php
├── routes/
│   ├── api.php
│   └── web.php
├── tests/
│   ├── Feature/
│   │   ├── CountryApiTest.php
│   │   ├── CreateQuoteTest.php
│   │   ├── ContractQuoteTest.php
│   │   ├── QuotePdfTest.php
│   │   └── ListQuotesTest.php
│   └── Unit/
│       ├── QuotePricingServiceTest.php
│       └── RegionSurchargeTest.php
├── .github/
│   └── workflows/
│       ├── tests.yml                # CI: PEST
│       └── docker-publish.yml       # build + push Docker Hub
├── Dockerfile
├── docker-compose.yml
├── .dockerignore
├── .env.example
├── README.md                        # instalación + arquitectura + deploy
├── README_PROPUESTA.md              # este documento
└── ...
```

---

## 7. API REST propuesta

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/countries` | Lista países (caché + fallback) |
| POST | `/api/quotes` | Crear cotización |
| GET | `/api/quotes` | Listar (paginado + filtros) |
| GET | `/api/quotes/{uuid}` | Detalle |
| POST | `/api/quotes/{uuid}/contract` | Contratar |
| GET | `/api/quotes/{uuid}/pdf` | Descargar PDF |

Rutas web:

| Ruta | Vista Vue |
|---|---|
| `/` | Crear cotización |
| `/quotes` | Listado |
| `/quotes/{uuid}` | Detalle / resumen |

---

## 8. Organización de la lógica de negocio

```text
HTTP Request
   ↓
Form Request (validación)
   ↓
Controller (orquestación)
   ↓
Service / Action (reglas de negocio)
   ↓
Model / Eloquent (persistencia)
   ↓
API Resource / PDF View (salida)
```

| Pieza | Responsabilidad |
|---|---|
| `QuotePricingService` | Días, base, recargo, total |
| `RestCountriesClient` | Integración externa resiliente |
| `QuoteService` | Crear y consultar cotizaciones |
| `ContractQuoteService` | Transición a contratado |
| `QuotePdfService` | Render + descarga PDF |
| Controllers | HTTP únicamente |
| Vue pages/components | UI + validación UX |

Separación interna fuerte **dentro del monólito** = buen diseño.  
Separación en microservicios para este dominio = complejidad sin retorno.

---

## 9. Frontend (Vue)

- Vue 3 + Composition API
- Vue Router
- Axios
- CSS propio responsive (mobile-first)
- Componentes pequeños y reutilizables
- Composables para países y quotes
- Loading / empty / error states claros
- UI usable en escritorio y móvil

No se busca diseño comercial elaborado; sí claridad y responsividad.

---

## 10. Pruebas automatizadas (PEST) — cobertura sólida

### Unitarias

- Cálculo de días (mismo día, varios días, bordes)
- Recargos por región (tabla completa del enunciado)
- Total final (España 10 días = USD 36)
- Región desconocida / política definida

### Feature

- Crear cotización válida → 201 + `quoted`
- Validaciones fallidas → 422
- Contratar → `contracted` + `contracted_at`
- Recontratar → 409
- Listado con paginación / búsqueda / filtro
- PDF descargable
- Países: éxito con `Http::fake()`
- Países: fallo externo controlado + fallback/caché

### Estrategia

- `Http::fake()` para REST Countries
- Factories
- DB de testing (SQLite in-memory o MySQL según `.env.testing`)
- CI ejecutando PEST en cada push/PR

---

## 11. CI/CD y calidad

### GitHub Actions (incluido)

1. **`tests.yml`**
   - Install deps
   - Ejecutar PEST
2. **`docker-publish.yml`**
   - Build imagen
   - Push a Docker Hub (secrets: `DOCKERHUB_USERNAME`, `DOCKERHUB_TOKEN`)

### Calidad de código

- Excepciones de dominio → HTTP claros (404, 409, 422, 503)
- Logging de fallos externos (sin datos sensibles)
- `.env.example` completo
- `.gitignore` correcto
- PHP tipado (enums, return types)
- Código en inglés; UI en español
- Sin secretos en el repo

---

## 12. Git y entregables

### Commits semánticos (historial limpio)

1. `chore: scaffold Laravel + Vue project`
2. `feat: add quotes migration, model and factory`
3. `feat: implement quote pricing service and config`
4. `feat: integrate REST Countries with cache and fallback`
5. `feat: create quote API endpoints and form requests`
6. `feat: add contract quote flow`
7. `feat: generate quote PDF`
8. `feat: build Vue quote form and quotes list`
9. `test: add pest unit and feature coverage`
10. `chore: add Docker Compose and production Dockerfile`
11. `ci: add GitHub Actions for tests and Docker Hub publish`
12. `docs: complete README, Render deploy and architecture`

### Entregables

1. Repo GitHub compartido con `vrubio@gestionsegura.com.ec`
2. README con instalación, arquitectura, Docker, Render y Docker Hub
3. Migraciones
4. Backend Laravel
5. Frontend Vue
6. Pruebas PEST
7. Explicación de arquitectura
8. (Plus) imagen en Docker Hub + guía de deploy en Render

---

## 13. Bonus — decisión actualizada

| Bonus | ¿Incluir? | Motivo |
|---|---|---|
| Caché API países | **Sí** | Resiliencia real |
| Factories | **Sí** | Tests y seeders limpios |
| Mayor cobertura PEST | **Sí** | Demuestra criterio |
| Docker Compose + Dockerfile | **Sí** | Local reproducible + base de deploy |
| Docker Hub | **Sí** | Portabilidad y entrega profesional |
| CI GitHub Actions | **Sí** | Calidad continua |
| Deploy documentado en Render | **Sí** | Demo pública fácil |
| Logs claros de integración externa | **Sí** | Operabilidad |
| Pinia | **No** | Composables alcanzan; evita estado global innecesario |
| TypeScript | **No** | No aporta suficiente vs complejidad añadida en este alcance |
| Autenticación | **No** | Fuera del caso de negocio pedido |
| Microservicios | **No** | Overengineering para este dominio |
| UI muy elaborada | **No** | Claridad > ornamentación |

---

## 14. README final del proyecto (contenido previsto)

El `README.md` operativo incluirá:

1. **Instalación local** (composer/npm y también Docker Compose)
2. **Arquitectura** (monólito modular y por qué)
3. **Organización de la lógica de negocio**
4. **Integración REST Countries**
5. **Decisiones técnicas relevantes**
6. **Cómo correr tests**
7. **Docker Hub** (pull/run)
8. **Despliegue en Render**
9. **Mejoras futuras** hacia producción

El enunciado original irá a `docs/ENUNCIADO.md` para no mezclar requisitos de la prueba con la documentación del producto.

---

## 15. Plan de implementación por fases

### Fase 0 — Preparación
- Scaffold Laravel + Vue/Vite
- Config MySQL, `.env.example`
- Estructura Services / Enums / Exceptions
- Mover enunciado a `docs/ENUNCIADO.md`

### Fase 1 — Dominio y persistencia
- Migración `quotes`
- Model + Enum + Factory + Seeder
- `config/insurance.php`

### Fase 2 — Lógica de negocio
- `QuotePricingService`
- `RegionSurcharge`
- Tests unitarios de pricing

### Fase 3 — Integración externa
- `RestCountriesClient` + caché + fallback + logs
- Endpoint `/api/countries`
- Tests con `Http::fake()`

### Fase 4 — API de cotización/contratación
- Form Requests + Controllers + Resources
- Create / Show / List / Contract
- Tests feature

### Fase 5 — PDF
- Vista Blade + `QuotePdfService`
- Endpoint de descarga + test

### Fase 6 — Frontend Vue
- Router, API client, composables
- Cotización + resumen + PDF + contratar
- Listado con búsqueda/filtro/paginación
- Detalle
- Responsive

### Fase 7 — Contenerización
- Dockerfile multi-stage
- `docker-compose.yml`
- Entrypoint y documentación de uso local

### Fase 8 — CI/CD y publicación
- GitHub Actions (PEST)
- Build/push Docker Hub
- Documentar secrets necesarios

### Fase 9 — Deploy Render + docs finales
- Guía `docs/DEPLOY_RENDER.md`
- README completo (instalación, arquitectura, Docker, Render, mejoras futuras)
- Verificación end-to-end en entorno desplegado
- Limpieza, commits, push, compartir repo

---

## 16. Criterios de aceptación (Definition of Done)

La solución se considera completa cuando:

- [ ] Cotización con todos los campos mínimos
- [ ] Validaciones frontend y backend activas
- [ ] Países desde REST Countries con resiliencia (caché/fallback)
- [ ] Cálculo correcto (ej. España 10 días = USD 36)
- [ ] PDF descargable
- [ ] Contratación persiste campos y cambia estado
- [ ] Listado con columnas mínimas + paginación/filtro/búsqueda
- [ ] Migraciones / seeders / factories listos
- [ ] PEST unit + feature pasando
- [ ] Controllers sin lógica de negocio pesada
- [ ] Docker Compose funcional
- [ ] Imagen publicable/publicada en Docker Hub
- [ ] CI de tests en GitHub Actions
- [ ] Guía de deploy en Render
- [ ] README con instalación, arquitectura y mejoras futuras
- [ ] Sin `.env` ni secretos en el repo
- [ ] Alcance funcional **100% completo** (sin secciones “pendiente de implementar”)

---

## 17. Mejoras futuras (producción — documentadas, no parte del núcleo actual)

- Autenticación/autorización (roles agente/admin)
- Pasarela de pagos real + webhooks
- Auditoría e historial de cambios de estado
- Colas para PDF y email
- Rate limiting anti-abuso
- Observabilidad avanzada (métricas, tracing)
- Multi-moneda / FX
- Catálogo de productos y coberturas
- Si el dominio crece de verdad: extracción gradual de módulos a servicios (no al inicio)

---

## 18. Resumen ejecutivo

Propuesta **completa, sencilla y senior**:

- **Monólito modular** (no microservicios)
- Services para pricing, países, contratación y PDF
- API REST + Vue SPA
- Validaciones duales justificadas
- Integración externa resiliente con caché
- PEST con cobertura de valor
- **Docker Compose + Dockerfile**
- **Docker Hub** como plus de entrega
- **CI** + **deploy documentado en Render**
- Cero complejidad ornamental

Listo para revisión. Tras tu OK, se implementa fase por fase según este plan.
