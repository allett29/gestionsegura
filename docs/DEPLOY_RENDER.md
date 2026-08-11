# Despliegue en Render — pasos explícitos

## A) Crear la base de datos MySQL

1. Entra a https://dashboard.render.com y inicia sesión.
2. Click en **New +** (arriba a la derecha).
3. Elige **MySQL**.
4. Completa:
   - **Name:** `gestionsegura-db`
   - **Database:** `seguro_viaje`
   - **User:** `seguro`
   - **Region:** la más cercana (ej. Oregon)
   - **Plan:** el que tengas disponible (Free/Starter según tu cuenta)
5. Click **Create Database**.
6. Espera a que quede **Available**.
7. Abre la base y copia estos valores (Internal Database URL / Connections):
   - **Host**
   - **Port** (normalmente `3306`)
   - **Database**
   - **User**
   - **Password**

> Usa la conexión **Internal** si el Web Service también está en Render (más estable y sin costo de salida).

---

## B) Crear el Web Service (la app)

1. En Render → **New +** → **Web Service**.
2. Conecta GitHub si te lo pide y autoriza el acceso.
3. Selecciona el repo: **`allett29/gestionsegura`**.
4. Configura exactamente así:

| Campo | Valor |
|---|---|
| **Name** | `gestionsegura` |
| **Region** | la misma de la DB |
| **Branch** | `main` |
| **Runtime** | **Docker** |
| **Dockerfile Path** | `Dockerfile` |
| **Docker Context** | `.` |
| **Instance type** | Free / Starter |

5. **NO** pongas Build Command ni Start Command custom (Docker usa el Dockerfile).
6. Baja a **Environment Variables** y agrega una por una:

| Key | Value |
|---|---|
| `APP_NAME` | `Seguro de Viaje` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://gestionsegura.onrender.com` *(cámbialo si Render te da otro nombre)* |
| `APP_KEY` | `base64:LFlbN/WKD7o0HvksuRMsgUT+z+b8frLyUT2m7gzko1M=` |
| `APP_LOCALE` | `es` |
| `APP_FALLBACK_LOCALE` | `es` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | *(Host de la DB del paso A)* |
| `DB_PORT` | `3306` *(o el que te muestre Render)* |
| `DB_DATABASE` | `seguro_viaje` *(o el nombre exacto de Render)* |
| `DB_USERNAME` | `seguro` *(o el user exacto de Render)* |
| `DB_PASSWORD` | *(Password de la DB del paso A)* |
| `SESSION_DRIVER` | `file` |
| `CACHE_STORE` | `file` |
| `QUEUE_CONNECTION` | `sync` |
| `LOG_CHANNEL` | `stderr` |
| `EJECUTAR_MIGRACIONES` | `true` |

7. Click **Create Web Service**.
8. Espera el deploy (puede tardar varios minutos la primera vez porque construye Docker).

---

## C) Después del primer deploy

1. Cuando el servicio diga **Live**, abre la URL que Render te dé  
   (ejemplo: `https://gestionsegura.onrender.com`).
2. Prueba:
   - `/` → formulario de cotización
   - `/cotizaciones` → listado
   - `/api/paises` → JSON de países
3. (Opcional) Cargar datos demo:
   - En el Web Service → **Shell**
   - Ejecuta:

```bash
php artisan db:seed --force
```

4. Actualiza `APP_URL` si la URL real de Render es distinta al nombre que pusiste, y haz **Manual Deploy → Deploy latest commit**.

---

## D) Si falla el deploy

Revisa **Logs** del Web Service:

- Error de DB → verifica `DB_HOST`, user, password (usa Internal Host).
- Error de puerto → asegúrate de tener el último commit con el `entrypoint` que usa `$PORT`.
- Build lento/timeout → reintenta; la primera build Docker en Free puede demorar.

---

## Checklist rápido

1. MySQL creado y Available  
2. Web Service Docker apuntando a `allett29/gestionsegura`  
3. Variables de entorno cargadas  
4. Deploy Live  
5. Probar `/`, `/cotizaciones`, `/api/paises`  
6. (Opcional) `db:seed`
