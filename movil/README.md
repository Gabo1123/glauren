# App G'Lauren (Flutter)

App para iPhone y Android del equipo, los colegios y los estudiantes. Usa la API `https://chaquetasdeprom.com/api/v1` (ver `routes/api.php`).

- `lib/api.dart`: conexión con el servidor y sesión (token guardado en el llavero del teléfono).
- `lib/offline.dart`: medidas tomadas sin internet que se envían después.
- `lib/screens/admin`: equipo (Hoy, Clientes, Contratos, Pagos).
- `lib/screens/school`: colegio.
- `lib/screens/student`: estudiante.
- `scripts/preparar_plataformas.sh`: genera `android/` e `ios/` listos para publicar (lo usa Codemagic).

**Compilar y publicar:** ver [PUBLICAR.md](PUBLICAR.md).
