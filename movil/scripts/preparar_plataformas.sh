#!/usr/bin/env bash
# Genera las carpetas android/ e ios/ (si no existen) y las deja listas para publicar:
# identificador de la app, nombre, permisos de cámara/fotos, internet, firma de Android e íconos.
# Lo ejecuta Codemagic antes de compilar. También sirve en un computador con Flutter.
set -euo pipefail
cd "$(dirname "$0")/.."

APP_ID="${APP_ID:-com.chaquetasdeprom.glauren}"
DEFAULT_NAME="G’Lauren"
APP_NAME="${APP_NAME:-$DEFAULT_NAME}"

if [ ! -d android ] || [ ! -d ios ]; then
  echo "▶ Creando proyectos nativos…"
  flutter create . --org com.chaquetasdeprom --project-name glauren --platforms=android,ios --no-pub >/dev/null
  rm -f test/widget_test.dart   # prueba de ejemplo que trae flutter create
fi

python3 scripts/preparar_plataformas.py "$APP_ID" "$APP_NAME"

flutter pub get
dart run flutter_launcher_icons
echo "✅ Listo para compilar"
