# Despliegue en Render — pasos explícitos (PostgreSQL)

Render Free suele ofrecer **PostgreSQL**, no MySQL.  
Esta app Laravel funciona con ambos; en Render usamos **pgsql**.

---

## A) Crear PostgreSQL

1. Entra a https://dashboard.render.com
2. **New +** → **PostgreSQL**
3. Completa:
   - **Name:** `gestionsegura-db`
   - **Database:** `seguro_viaje`
   - **User:** `seguro`
   - **Region:** la más cercana
   - **Plan:** Free
4. **Create Database**
5. Espera a que diga **Available**
6. Abre la DB y copia (sección **Connections**):
   - **Internal Database URL** (la más importante), o por separado:
     - Host
     - Port (`5432`)
     - Database
     - Username
     - Password

---

## B) Crear Web Service (Docker)

1. **New +** → **Web Service**
2. Conecta GitHub y elige **`allett29/gestionsegura`**
3. Configura:

| Campo | Valor |
|---|---|
| **Name** | `gestionsegura` |
| **Region** | la misma de la DB |
| **Branch** | `main` |
| **Runtime** | **Docker** |
| **Dockerfile Path** | `Dockerfile` |

4. En **Environment Variables** agrega:

| Key | Value |
|---|---|
| `APP_NAME` | `Seguro de Viaje` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://gestionsegura.onrender.com` |
| `APP_KEY` | `base64:LFlbN/WKD7o0HvksuRMsgUT+z+b8frLyUT2m7gzko1M=` |
| `APP_LOCALE` | `es` |
| `APP_FALLBACK_LOCALE` | `es` |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | *(Host Internal de Postgres)* |
| `DB_PORT` | `5432` |
| `DB_DATABASE` | *(Database de Render)* |
| `DB_USERNAME` | *(User de Render)* |
| `DB_PASSWORD` | *(Password de Render)* |
| `SESSION_DRIVER` | `file` |
| `CACHE_STORE` | `file` |
| `QUEUE_CONNECTION` | `sync` |
| `LOG_CHANNEL` | `stderr` |
| `EJECUTAR_MIGRACIONES` | `true` |
| `REST_COUNTRIES_API_KEY` | *(opcional; si no hay key se usa respaldo local ISO)* |

> **REST Countries:** la API pública v3 ya no devuelve datos. Sin `REST_COUNTRIES_API_KEY` la app usa un archivo local con ~249 países. Para datos en vivo, regístrate en https://restcountries.com/sign-up y pega tu key.

> Alternativa más simple: si Render te da **Internal Database URL**, puedes poner solo:
>
> - `DB_CONNECTION=pgsql`
> - `DATABASE_URL=postgresql://user:pass@host:5432/db`
>
> (Laravel también lee `DATABASE_URL`).

5. **Create Web Service**
6. Espera a que quede **Live**

---

## C) Verificar

Abre la URL de Render:

- `/`
- `/cotizaciones`
- `/api/paises`

Opcional (datos demo) en **Shell** del servicio:

```bash
php artisan db:seed --force
```

Si la URL real no es `gestionsegura.onrender.com`, actualiza `APP_URL` y redeploy.

---

## Nota sobre MySQL local

En local/Docker Compose seguimos usando **MySQL** (`docker compose`).  
En Render usamos **PostgreSQL**. No hay conflicto: Laravel abstrae el driver.
