"""Ajusta android/ e ios/ generados por flutter create (lo llama preparar_plataformas.sh)."""
import re, sys, pathlib
app_id, app_name = sys.argv[1], sys.argv[2].replace("'", '’')   # el apóstrofo recto rompe Android

# ---------- Android ----------
g = pathlib.Path('android/app/build.gradle.kts')
groovy = False
if not g.exists():
    g = pathlib.Path('android/app/build.gradle'); groovy = True
s = g.read_text()
if groovy:
    s = re.sub(r'applicationId\s+"[^"]*"', f'applicationId "{app_id}"', s)
    s = re.sub(r'namespace\s+"[^"]*"', f'namespace "{app_id}"', s)
    s = re.sub(r'minSdkVersion\s+[^\n]+', 'minSdkVersion Math.max(23, flutter.minSdkVersion)', s)
    if 'CM_KEYSTORE_PATH' not in s:
        s = s.replace('buildTypes {', '''signingConfigs {
        release {
            if (System.getenv("CM_KEYSTORE_PATH") != null) {
                storeFile file(System.getenv("CM_KEYSTORE_PATH"))
                storePassword System.getenv("CM_KEYSTORE_PASSWORD")
                keyAlias System.getenv("CM_KEY_ALIAS")
                keyPassword System.getenv("CM_KEY_PASSWORD")
            }
        }
    }
    buildTypes {''', 1)
        s = s.replace('signingConfig signingConfigs.debug', 'signingConfig System.getenv("CM_KEYSTORE_PATH") != null ? signingConfigs.release : signingConfigs.debug')
else:
    s = re.sub(r'applicationId\s*=\s*"[^"]*"', f'applicationId = "{app_id}"', s)
    s = re.sub(r'namespace\s*=\s*"[^"]*"', f'namespace = "{app_id}"', s)
    s = re.sub(r'minSdk\s*=\s*[^\n]+', 'minSdk = maxOf(23, flutter.minSdkVersion)', s)
    if 'CM_KEYSTORE_PATH' not in s:
        s = s.replace('buildTypes {', '''signingConfigs {
        create("release") {
            if (System.getenv("CM_KEYSTORE_PATH") != null) {
                storeFile = file(System.getenv("CM_KEYSTORE_PATH"))
                storePassword = System.getenv("CM_KEYSTORE_PASSWORD")
                keyAlias = System.getenv("CM_KEY_ALIAS")
                keyPassword = System.getenv("CM_KEY_PASSWORD")
            }
        }
    }
    buildTypes {''', 1)
        s = s.replace('signingConfig = signingConfigs.getByName("debug")',
                      'signingConfig = if (System.getenv("CM_KEYSTORE_PATH") != null) signingConfigs.getByName("release") else signingConfigs.getByName("debug")')
g.write_text(s)

# Mueve MainActivity al paquete nuevo (si el identificador cambió)
kt_root = pathlib.Path('android/app/src/main/kotlin')
acts = list(kt_root.rglob('MainActivity.kt'))
if acts:
    act = acts[0]
    target = kt_root.joinpath(*app_id.split('.')) / 'MainActivity.kt'
    code = re.sub(r'^package\s+[\w.]+', f'package {app_id}', act.read_text(), flags=re.M)
    target.parent.mkdir(parents=True, exist_ok=True)
    if act != target:
        act.unlink()
    target.write_text(code)

m = pathlib.Path('android/app/src/main/AndroidManifest.xml')
x = m.read_text()
if 'android.permission.INTERNET' not in x:
    x = x.replace('<application', '<uses-permission android:name="android.permission.INTERNET"/>\n    <application', 1)
x = re.sub(r'android:label="[^"]*"', f'android:label="{app_name}"', x, count=1)
m.write_text(x)

# ---------- iOS ----------
p = pathlib.Path('ios/Runner/Info.plist')
x = p.read_text()
def put(key, value):
    global x
    if f'<key>{key}</key>' in x:
        x = re.sub(rf'(<key>{key}</key>\s*<string>)[^<]*(</string>)', rf'\g<1>{value}\g<2>', x)
    else:
        x = x.replace('<dict>', f'<dict>\n\t<key>{key}</key>\n\t<string>{value}</string>', 1)
put('CFBundleDisplayName', app_name)
put('NSCameraUsageDescription', 'Para tomar fotos del avance de producción y de los comprobantes de pago.')
put('NSPhotoLibraryUsageDescription', 'Para elegir fotos del avance y comprobantes de pago desde tu galería.')
put('ITSAppUsesNonExemptEncryption', '')
x = x.replace('<key>ITSAppUsesNonExemptEncryption</key>\n\t<string></string>', '<key>ITSAppUsesNonExemptEncryption</key>\n\t<false/>')
p.write_text(x)

pbx = pathlib.Path('ios/Runner.xcodeproj/project.pbxproj')
y = pbx.read_text()
y = re.sub(r'PRODUCT_BUNDLE_IDENTIFIER = [^;]*\.RunnerTests;', f'PRODUCT_BUNDLE_IDENTIFIER = {app_id}.RunnerTests;', y)
y = re.sub(r'PRODUCT_BUNDLE_IDENTIFIER = (?![^;]*RunnerTests)[^;]*;', f'PRODUCT_BUNDLE_IDENTIFIER = {app_id};', y)
y = re.sub(r'IPHONEOS_DEPLOYMENT_TARGET = [0-9.]+;', 'IPHONEOS_DEPLOYMENT_TARGET = 13.0;', y)
pbx.write_text(y)
print('✔ Plataformas listas:', app_id)
