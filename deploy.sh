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
git pull --ff-only origin main

if command -v composer >/dev/null 2>&1 && [ composer.lock -nt vendor/autoload.php ]; then
  echo "▶ Actualizando dependencias…"
  composer install --no-dev --optimize-autoloader --no-interaction
fi

echo "▶ Limpiando y regenerando cachés…"
$PHP artisan optimize:clear
$PHP artisan migrate --force
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
