<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Acceso · Fotografía de Grados</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body{ background:#12100f; color:#f4f1ea; font-family:system-ui,sans-serif; min-height:100vh; display:grid; place-items:center }
    .box{ background:#1b1917; border:1px solid #2e2a27; border-radius:16px; padding:30px; width:100%; max-width:380px }
    .brand{ color:#c9a54b; font-weight:800; text-transform:uppercase; letter-spacing:.05em; text-align:center; margin-bottom:4px }
    .form-control{ background:#12100f; border:1px solid #2e2a27; color:#f4f1ea }
    .form-control:focus{ background:#12100f; color:#f4f1ea; border-color:#c9a54b; box-shadow:0 0 0 .2rem rgba(201,165,75,.2) }
    .btn-gold{ background:#c9a54b; color:#1a1405; font-weight:700; border:0; width:100% }
</style>
</head>
<body>
<form class="box" method="POST" action="{{ route('foto.login.post') }}">
    @csrf
    <div class="brand">📸 Fotografía de Grados</div>
    <p class="text-center text-secondary" style="font-size:14px">Panel del fotógrafo</p>
    @if($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif
    <div class="mb-3"><label class="form-label small">Correo</label><input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus></div>
    <div class="mb-3"><label class="form-label small">Contraseña</label><input type="password" name="password" class="form-control" required></div>
    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="remember" id="r"><label class="form-check-label small" for="r">Recordarme</label></div>
    <button class="btn btn-gold">Ingresar</button>
    <div class="text-center mt-3"><a href="{{ url('/') }}" class="small text-secondary text-decoration-none">← Volver al sitio</a></div>
</form>
</body>
</html>
