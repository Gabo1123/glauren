# App G'Lauren: cómo compilar y publicar (sin Mac)

La app está hecha en **Flutter (Dart)** y se compila en la nube con **Codemagic**. Una misma app sirve para:

| Quién entra | Cómo entra | Qué puede hacer |
|---|---|---|
| **Equipo G'Lauren** | Correo y contraseña del panel `/gtorres` (con código por WhatsApp si tienes la verificación en dos pasos) | Ver a quién llamar hoy, crear clientes, enviar cotizaciones por WhatsApp, editar la ficha, marcar ganadas o perdidas, cambiar la etapa de los contratos, subir fotos del taller, tomar medidas (también sin internet), aprobar pagos y anticipos, y ver la cartera |
| **Colegios** | Correo y contraseña de su portal | Ver el avance, los pagos del curso y las fotos; agregar alumnos; firmar; subir el comprobante del anticipo |
| **Estudiantes** | Documento y PIN de 4 números (el mismo de la web) | Ver en qué va su chaqueta, su talla y su pago; subir el comprobante; ver las fotos; calificar al entregar |

Identificador de la app (iPhone y Android): **`com.chaquetasdeprom.glauren`**

---

## 0. Antes de empezar

1. Despliega la web con `bash ~/glauren/deploy.sh`. Crea la tabla de sesiones de la app (`api_tokens`).
2. Comprueba que el servidor responde: abre `https://chaquetasdeprom.com/api/v1/ping` en el navegador. Debe mostrar `{"ok":true,...}`.

## 1. Conectar Codemagic con GitHub

1. Entra a **codemagic.io** y crea la cuenta **con tu GitHub**.
2. **Add application** → elige el repositorio `Gabo1123/glauren` → tipo **Flutter App** → **codemagic.yaml**.
3. Codemagic lee el archivo `codemagic.yaml` que está en la raíz del repositorio. Trae 3 flujos:
   - **Revisar app**: corre solo en cada cambio de `movil/`. Analiza el código y no publica nada.
   - **iPhone · App Store**: lo lanzas tú y sube la app a TestFlight.
   - **Android · Play Store**: lo lanzas tú y sube la app a Google Play (pista interna, como borrador).

## 2. iPhone (App Store)

### En App Store Connect (appstoreconnect.apple.com)
1. **Usuarios y acceso → Integraciones → App Store Connect API** → **Generar llave** con rol **App Manager**.
   - Descarga el archivo `.p8` (solo se puede bajar una vez).
   - Anota el **Issuer ID** y el **Key ID**.
2. **Mis apps → + → Nueva app**:
   - Plataforma: iOS.
   - Nombre: *G’Lauren*.
   - Idioma: español.
   - Bundle ID: `com.chaquetasdeprom.glauren`. Si no aparece, créalo antes en developer.apple.com → Identifiers.
   - SKU: `glauren-app`.

### En Codemagic
1. **Teams → Integrations → Developer Portal → Manage keys → Add key**.
   - Nombre: **`GLauren App Store`**. Debe ser exactamente igual al de `codemagic.yaml`.
   - Pega el Issuer ID y el Key ID, y sube el `.p8`.
2. **Code signing identities → iOS certificates → Generate certificate**: crea un certificado de **Apple Distribution**. Codemagic lo guarda.
3. **iOS provisioning profiles → Fetch profiles**: trae el perfil *App Store* de `com.chaquetasdeprom.glauren`. Si no existe, Codemagic lo crea al compilar.
4. Abre la app en Codemagic → **Start new build** → flujo **iPhone · App Store**.
   - Tarda unos 20 minutos.
   - Al terminar, la app aparece en **TestFlight**: instálala en tu iPhone con la app TestFlight y pruébala.
5. Cuando esté bien: en App Store Connect completa la ficha y elige el build → **Enviar a revisión**. La ficha lleva fotos, descripción, política de privacidad y una cuenta de prueba para Apple (ver el punto 4).

## 3. Android (Google Play)

### Llave de firma (una sola vez, ¡guárdala para siempre!)
En Codemagic: **Teams → Code signing identities → Android keystores → Generate keystore**.
- Nombre de referencia: **`glauren_keystore`**. Debe ser igual al de `codemagic.yaml`.
- Descarga una copia y guárdala en un lugar seguro. Sin ella no podrás actualizar la app nunca.

### Cuenta de servicio para publicar
1. En **Google Cloud Console** crea una *cuenta de servicio* y descarga su llave **JSON**.
2. En **Play Console → Usuarios y permisos**, invita el correo de esa cuenta de servicio con permiso de **Administrador de versiones**.
3. En Codemagic: **App settings → Environment variables**:
   - Nombre: `GCLOUD_SERVICE_ACCOUNT_CREDENTIALS`.
   - Valor: pega el contenido del JSON y márcalo como **Secret**.
   - Grupo: **`google_play`**.

### Primera publicación
1. En **Play Console → Crear app**: nombre *G’Lauren*, idioma español, tipo App, gratuita.
2. Lanza en Codemagic el flujo **Android · Play Store**.
   - Google exige que el **primer** archivo se suba a mano.
   - Si el paso de publicar falla esa primera vez, descarga el `.aab` de los *Artifacts* del build.
   - Súbelo en **Play Console → Pruebas → Prueba interna → Crear versión**.
3. Desde la segunda versión, Codemagic lo sube solo (queda como borrador en la pista interna).
4. Completa la ficha:
   - Fotos y descripción.
   - **Política de privacidad**: usa `https://chaquetasdeprom.com/p/politica-de-privacidad` (es la página que enlaza tu web; créala en Contenido → Páginas si aún no existe, y menciona la app, las fotos y los comprobantes).
   - Clasificación de contenido y *Seguridad de los datos*: la app guarda nombre, documento, celular, fotos y comprobantes de pago.
5. Envía a producción cuando la pruebes.

## 4. Lo que piden Apple y Google para aprobar

- **Cuenta de prueba**: crea en el panel un usuario de colegio de prueba (por ejemplo `revision@chaquetasdeprom.com`) con un contrato de ejemplo, y pon su correo y contraseña en la sección de revisión de cada tienda.
- **Política de privacidad**: debe ser un enlace público.
- **Borrar cuenta**: Apple exige poder pedir la eliminación de la cuenta. Por ahora indica en la ficha que se solicita por WhatsApp o correo. Si Apple lo pide dentro de la app, se agrega un botón.

## 5. Publicar una actualización

1. Haz los cambios en `movil/` y súbelos a GitHub (el flujo *Revisar app* te avisa si algo no compila).
2. En Codemagic lanza **iPhone · App Store** y/o **Android · Play Store**. El número de versión sube solo.

## 6. Probar en un computador (opcional)

Con Flutter instalado:
```bash
cd movil
bash scripts/preparar_plataformas.sh   # genera android/ e ios/
flutter run                             # en un emulador o un celular conectado
```
Para apuntar a otro servidor: `flutter run --dart-define=API_URL=https://otro.dominio/api/v1`

## 7. Ícono

El ícono está en `movil/assets/icon.png` (1024×1024) y su versión con fondo transparente en `assets/icon_foreground.png` (Android). Reemplázalos por tu logo con el mismo nombre y tamaño. En el siguiente build se generan todos los tamaños.
