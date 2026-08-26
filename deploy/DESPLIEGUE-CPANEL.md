# Guía de despliegue en cPanel — Glauren

Esta guía deja la plataforma funcionando en tu hosting con cPanel. Tiempo estimado: 30–45 min.

## 0) Requisitos del hosting
- **PHP 8.3** (en cPanel → *Select PHP Version*). Activa extensiones: `mbstring, openssl, pdo_mysql, tokenizer, xml, ctype, json, bcmath, fileinfo, gd, curl`.
- **MySQL/MariaDB**.
- Acceso a **phpMyAdmin** y, si es posible, a **Terminal** (cPanel → Terminal) o SSH.

---

## 1) Subir los archivos
1. En tu PC, comprime la carpeta del proyecto **`C:\Chaquetas prom`** en un `.zip`.
   - **NO incluyas**: `node_modules` (no hay), `.git`, ni la carpeta `deploy`.
   - **SÍ incluye**: `app, bootstrap, config, database, public, resources, routes, storage, vendor, artisan, composer.json`, etc.
   - Si `vendor` no está o pesa mucho, puedes subir sin él y ejecutar `composer install --no-dev` en el servidor (paso 6).
2. Sube el `.zip` a una carpeta **privada** (fuera de `public_html`), por ejemplo `/home/USUARIO/glauren`, y descomprímelo ahí.

### Document Root (importante)
Laravel debe servirse desde la carpeta **`public/`**, nunca desde la raíz.
- **Opción A (recomendada):** crea el dominio/subdominio en cPanel y pon su *Document Root* en `/home/USUARIO/glauren/public`.
- **Opción B (si tu plan solo usa `public_html`):** mueve el **contenido** de `glauren/public/` a `public_html/`, deja el resto del proyecto en `glauren/`, y edita `public_html/index.php`:
  ```php
  require __DIR__.'/../glauren/vendor/autoload.php';
  $app = require_once __DIR__.'/../glauren/bootstrap/app.php';
  ```

---

## 2) Crear la base de datos
1. cPanel → **MySQL Databases** → crea una base (ej. `usuario_glauren`).
2. Crea un usuario MySQL con contraseña fuerte y **asígnalo a la base con TODOS los privilegios**.
3. Anota: nombre de base, usuario y contraseña (van en el `.env`).

## 3) Importar los datos
1. cPanel → **phpMyAdmin** → selecciona tu base → pestaña **Importar**.
2. Sube el archivo **`db-chaquetas.sql`** (incluido en esta carpeta `deploy`) y ejecuta.
   - Esto crea las 26 tablas con tus contenidos (colecciones, tipos, textos, etc.).

## 4) Configurar el `.env`
1. En la raíz del proyecto (`glauren/`) crea un archivo **`.env`** copiando **`.env.produccion.example`** (incluido aquí).
2. Rellena: `APP_URL`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, y tus **llaves de Rapyd**.
3. Genera la clave de la app:
   ```bash
   php artisan key:generate
   ```
   (Si no tienes Terminal/SSH: genera una en https://laravel-encryption-key-generator.vercel.app y pégala en `APP_KEY=base64:...`)

## 5) Enlazar el almacenamiento y permisos
```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```
- Si el hosting **no permite symlinks**, crea a mano la carpeta `public/storage` y copia dentro el contenido de `storage/app/public` (ahí están el banner, logos y fotos subidas).

## 6) Instalar dependencias y optimizar (si subiste sin `vendor`)
```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force        # solo si NO importaste el .sql; si ya importaste, omite
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
> Si importaste el `.sql` (paso 3), **no** corras `migrate` para no duplicar. Si prefieres, empieza con base vacía y usa `php artisan migrate --seed` en vez de importar el `.sql`.

---

## 7) Primeros ajustes ya en producción
1. Entra a `https://TU-DOMINIO.com/admin` con `admin@chaquetas.test` / `password` y **cambia la contraseña** (crea tu usuario real y borra el de prueba).
2. Cambia también la clave del portal de colegios de prueba.
3. En **Admin → Textos/Home**: revisa WhatsApp, teléfono, dirección, logo, banner.
4. En **Admin → Rapyd**: verifica que las llaves sean las de **producción** (`api.rapyd.net`) y registra el webhook `https://TU-DOMINIO.com/rapyd/webhook` en tu panel Rapyd.

## 8) Seguridad
- `APP_DEBUG=false` (ya viene así en el template).
- Fuerza HTTPS (cPanel → SSL/TLS → AutoSSL / Let's Encrypt).
- Haz una **copia de seguridad** periódica de la base (cPanel → Backup) o exporta el `.sql` cada semana.

---

### Checklist rápido
- [ ] Archivos subidos, docroot → `public/`
- [ ] Base creada + `db-chaquetas.sql` importado
- [ ] `.env` configurado + `APP_KEY` generada
- [ ] `storage:link` y permisos 775
- [ ] Caché optimizada
- [ ] Contraseñas de prueba cambiadas
- [ ] HTTPS activo + webhook Rapyd

¿Dudas en algún paso? Dime el nombre de tu hosting y te doy los clics exactos.
