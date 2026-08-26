<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.seo', [
        'seoKey' => $seoKey ?? 'home',
        'seoTitleOverride' => ($__t = trim($__env->yieldContent('title'))) !== '' ? $__t.' · Glauren' : null,
    ])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/glauren-ui.css') }}" rel="stylesheet">
    <style>
        :root{ --bg:#0e1014; --panel:#14171e; --panel2:#1e222b; --bd:#2a2f3a; }
        body{ background:var(--bg); color:#e8eaed; }
        .text-secondary{ color:#98a0ac !important; }
        .card{ background:var(--panel); border:1px solid var(--bd); color:#e8eaed; }
        .type-card{ transition:.15s; cursor:pointer; }
        .type-card:hover{ border-color:#4f8cff; transform:translateY(-4px); }
        .form-control,.form-select{ background:var(--panel2); border-color:var(--bd); color:#e8eaed; }
        .form-control:focus,.form-select:focus{ background:var(--panel2); color:#e8eaed; border-color:#4f8cff; box-shadow:none; }
        .form-control::placeholder{ color:#6b7280; }
        a{ color:#8ab4ff; }
    </style>
    @stack('head')
</head>
<body>
    @yield('content')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
