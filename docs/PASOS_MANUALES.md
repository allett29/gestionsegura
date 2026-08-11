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

## 2) GitHub (estado actual)

Repo ya publicado: https://github.com/allett29/gestionsegura

Pendiente manual:

1. Comparte el repo con: **vrubio@gestionsegura.com.ec**
   - Settings → Collaborators → Add people → invita ese correo
2. Para subir los workflows de GitHub Actions, crea un Personal Access Token (classic) con scopes:
   - `repo`
   - `workflow`
3. Luego:

```powershell
cd C:\Users\DETPC\Desktop\Gestion_Segura
git add .github/workflows
git commit -m "ci: restaurar workflows de pruebas y Docker Hub"
git push
```

---

## 3) Docker Hub (estado actual)

Imagen ya publicada:

- `allett29/gestionsegura:latest`
- `allett29/gestionsegura:v1.0.0`

Pendiente de seguridad (importante):

1. **Revoca** el token que se compartió en el chat (Docker Hub → Account Settings → Security).
2. Crea un token nuevo solo para CI.
3. En GitHub → Settings → Secrets and variables → Actions:
   - `DOCKERHUB_USERNAME` = `allett29`
   - `DOCKERHUB_TOKEN` = el token nuevo (no el expuesto)

Pull de prueba:

```powershell
docker pull allett29/gestionsegura:latest
```

---

## 4) Despliegue en Render

Sigue `docs/DEPLOY_RENDER.md`. Resumen:

1. Crear **PostgreSQL** en Render (plan free)
2. New Web Service → conectar repo → Runtime Docker
3. Variables mínimas:
   - `APP_KEY`
   - `APP_URL`
   - `DB_CONNECTION=pgsql`
   - `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
   - `SESSION_DRIVER=file`
   - `CACHE_STORE=file`
   - `EJECUTAR_MIGRACIONES=true`
   - `REST_COUNTRIES_URL=https://api.restcountries.com/countries/v5`
   - `REST_COUNTRIES_API_KEY` = tu key v5 de REST Countries
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
