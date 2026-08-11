# Pasos manuales que debes realizar tú

La aplicación ya está implementada y corriendo localmente con Docker en `http://localhost:8080`.

## 1) Verificar local (ya debería estar arriba)

```bash
docker compose ps
```

Abre en el navegador:

- http://localhost:8080/ → Cotizar
- http://localhost:8080/cotizaciones → Listado

Si no está arriba:

```bash
cp .env.example .env
# Genera APP_KEY y pégala en .env
docker run --rm php:8.4-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)), PHP_EOL;"

docker compose up -d --build
docker compose exec app php artisan db:seed --force
```

---

## 2) Crear repositorio en GitHub y subir el código

1. Entra a GitHub → **New repository**
2. Nombre sugerido: `seguro-viaje` (o el que prefieras)
3. **NO** marques README (ya existe en el proyecto)
4. Crea el repo y luego en PowerShell (desde la carpeta del proyecto):

```powershell
cd C:\Users\DETPC\Desktop\Gestion_Segura
git init
git add .
git commit -m "feat: sistema completo de cotizacion y venta de seguro de viaje"
git branch -M main
git remote add origin https://github.com/TU_USUARIO/TU_REPO.git
git push -u origin main
```

5. Comparte el repo con: **vrubio@gestionsegura.com.ec**
   - Settings → Collaborators → Add people → invita ese correo

---

## 3) Docker Hub (plus)

1. Crea cuenta en https://hub.docker.com
2. Crea un Access Token (Account Settings → Security)
3. En GitHub del repo → Settings → Secrets and variables → Actions:
   - `DOCKERHUB_USERNAME` = tu usuario
   - `DOCKERHUB_TOKEN` = el token
4. Publica imagen:

```powershell
git tag v1.0.0
git push origin v1.0.0
```

O corre el workflow **Publicar imagen Docker Hub** manualmente.

---

## 4) Despliegue en Render

Sigue `docs/DEPLOY_RENDER.md`. Resumen:

1. Crear MySQL en Render
2. New Web Service → conectar repo → Runtime Docker
3. Variables mínimas:
   - `APP_KEY`
   - `APP_URL`
   - `DB_CONNECTION=mysql`
   - `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
   - `SESSION_DRIVER=file`
   - `CACHE_STORE=file`
   - `EJECUTAR_MIGRACIONES=true`
4. Deploy
5. (Opcional) Shell → `php artisan db:seed --force`

---

## 5) Checklist final antes de entregar

- [ ] Repo en GitHub compartido con `vrubio@gestionsegura.com.ec`
- [ ] README legible (instalación + arquitectura)
- [ ] App demo local o en Render funcionando
- [ ] Sin `.env` ni secretos en el repo
- [ ] (Opcional) Imagen en Docker Hub
- [ ] (Opcional) URL de Render en el README o en el correo de entrega
