# Despliegue en cPanel — Glauren (chaquetasdeprom.com)

Servidor: `/home/oocbwivl/` · Dominio (document root): `/home/oocbwivl/chaquetasdeprom.com/`
Repo: https://github.com/Gabo1123/glauren

> Laravel debe servirse desde su carpeta **`public/`**. Como el document root del dominio ya es
> `/home/oocbwivl/chaquetasdeprom.com/`, dejamos la app en una carpeta privada aparte
> (`/home/oocbwivl/glauren/`) y conectamos el document root a su `public/`.

---

## 0) Requisitos (cPanel → "Select PHP Version")
PHP **8.3** con extensiones: `mbstring, openssl, pdo_mysql, tokenizer, xml, ctype, json, bcmath, fileinfo, gd, curl`.

---

## 1) Subir el código

### Opción A — con Terminal/SSH (recomendada)
cPanel → **Terminal** (o SSH) y ejecuta:
```bash
cd ~
git clone https://github.com/Gabo1123/glauren.git glauren
cd glauren
```
> Al ser repo privado, usa un **token** de GitHub como contraseña, o configura una llave SSH.

Instala dependencias (si tu hosting tiene Composer):
```bash
composer install --no-dev --optimize-autoloader
```
Si no hay Composer en el servidor, sube tú la carpeta `vendor` por FTP (comprímela desde tu PC).

### Opción B — sin Terminal (Administrador de archivos)
1. En tu PC, comprime el proyecto **incluyendo `vendor`** (sin `.git`, sin `node_modules`, sin `deploy`).
2. Súbelo a `/home/oocbwivl/glauren/` y descomprime.

---

## 2) Base de datos
1. cPanel → **MySQL Databases** → crea la base (ej. `oocbwivl_glauren`).
2. Crea un usuario con contraseña fuerte y **asígnalo con TODOS los privilegios**.
3. cPanel → **phpMyAdmin** → selecciona la base → **Importar** → sube `deploy/db-chaquetas.sql`.
   (Ese archivo lo subes por FTP; no está en GitHub por contener datos.)

---

## 3) Archivo `.env` (en `/home/oocbwivl/glauren/.env`)
Cópialo de `.env.example` y ajusta:
```env
APP_NAME="Glauren"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://chaquetasdeprom.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=oocbwivl_glauren
DB_USERNAME=oocbwivl_xxx
DB_PASSWORD=tu_clave_db

# Correo del hosting
MAIL_MAILER=smtp
MAIL_HOST=mail.chaquetasdeprom.com
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
MAIL_USERNAME=glauren@chaquetasdeprom.com
MAIL_PASSWORD=la_clave_del_correo
MAIL_FROM_ADDRESS=glauren@chaquetasdeprom.com
MAIL_FROM_NAME="Glauren"

# IA WhatsApp
GEMINI_API_KEY=tu_llave_gemini
GEMINI_MODEL=gemini-3.6-flash

# WhatsApp Business (Meta)
WHATSAPP_TOKEN=
WHATSAPP_PHONE_ID=
WHATSAPP_VERIFY_TOKEN=glauren_webhook_2026
```

Luego (con Terminal, dentro de `~/glauren`):
```bash
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
> Sin Terminal: pide a soporte del hosting correr estos comandos, o usa un "cron único".
> `key:generate` es obligatorio (crea `APP_KEY`). `storage:link` es necesario para ver las imágenes.

---

## 4) Conectar el dominio a `public/`

### Si puedes cambiar el Document Root
cPanel → **Domains** → chaquetasdeprom.com → cambia su carpeta a
`/home/oocbwivl/glauren/public`. ¡Listo!

### Si NO puedes cambiarlo (el root queda en chaquetasdeprom.com/)
1. Copia el **contenido** de `~/glauren/public/` dentro de `/home/oocbwivl/chaquetasdeprom.com/`.
2. Edita `/home/oocbwivl/chaquetasdeprom.com/index.php` y cambia las dos rutas para que apunten a la app:
   ```php
   require __DIR__.'/../glauren/vendor/autoload.php';
   $app = require_once __DIR__.'/../glauren/bootstrap/app.php';
   ```
3. Verifica que exista `.htaccess` en esa carpeta (viene en `public/`).
4. El enlace de storage: si `storage:link` no funcionó, crea a mano una carpeta/symlink
   `chaquetasdeprom.com/storage` → `~/glauren/storage/app/public`.

---

## 5) Permisos
Las carpetas `storage/` y `bootstrap/cache/` deben ser escribibles (755/775).

---

## 6) Después de publicar
- Abre https://chaquetasdeprom.com — debe cargar el home.
- Panel: **https://chaquetasdeprom.com/gtorres** (usuario admin de tu base).
- En **/gtorres → Configuración (llaves API)** pega/confirma las llaves (WhatsApp, correo).
- Activa **HTTPS** (cPanel → SSL/TLS Status → AutoSSL).
- Webhook de WhatsApp en Meta: `https://chaquetasdeprom.com/whatsapp/webhook` (verify token: el de tu .env).

## 7) Actualizaciones futuras
Con Terminal:
```bash
cd ~/glauren && git pull && composer install --no-dev -o && php artisan migrate --force && php artisan config:cache && php artisan view:cache
```
