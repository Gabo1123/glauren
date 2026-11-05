#!/usr/bin/env bash
# Publica los últimos cambios en el hosting con UN solo comando:
#   bash ~/glauren/deploy.sh
# Opcional: DOCROOT=~/otra_carpeta bash ~/glauren/deploy.sh
set -e

APP="$(cd "$(dirname "$0")" && pwd)"
DOCROOT="${DOCROOT:-$HOME/chaquetasdeprom.com}"
PHP="${PHP:-php}"

cd "$APP"
echo "▶ Bajando cambios de GitHub…"
# Sin --ff-only: si en el servidor quedó un "merge" de un pull anterior, igual baja los cambios
git pull --no-edit origin main || { echo "❌ git pull falló. Copia este error y envíalo."; exit 1; }

if command -v composer >/dev/null 2>&1 && [ composer.lock -nt vendor/autoload.php ]; then
  echo "▶ Actualizando dependencias…"
  composer install --no-dev --optimize-autoloader --no-interaction
fi

echo "▶ Limpiando y regenerando cachés…"
$PHP artisan optimize:clear
$PHP artisan migrate --force || echo "⚠️  La migración falló (copia el error de arriba). Sigo con las cachés para que la web no se caiga."
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache
$PHP artisan storage:link 2>/dev/null || true

# Si la web pública vive en otra carpeta, copiamos imágenes y fuentes
if [ -d "$DOCROOT" ] && [ "$(cd "$DOCROOT" && pwd)" != "$APP/public" ]; then
  echo "▶ Copiando imágenes y fuentes a $DOCROOT…"
  for d in img fonts; do
    [ -d "public/$d" ] && cp -r "public/$d" "$DOCROOT/"
  done
fi

echo "✅ Listo. Cambios publicados."
