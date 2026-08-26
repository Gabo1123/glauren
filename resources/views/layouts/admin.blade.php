<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>Admin · @yield('title', 'Glauren')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/glauren-ui.css') }}" rel="stylesheet">
    <style>
        body{ background:var(--g-ink); color:var(--g-bone); font-family:var(--g-font-body); }
        .g-shell{ display:flex; min-height:100vh; }
        .g-side{ width:250px; flex-shrink:0; background:var(--g-surface); border-right:1px solid var(--g-line); padding:22px 16px; position:sticky; top:0; height:100vh; overflow-y:auto; }
        .g-side::-webkit-scrollbar{ width:8px; } .g-side::-webkit-scrollbar-thumb{ background:rgba(255,255,255,.12); border-radius:4px; }
        .g-brand{ font-family:var(--g-font-display); text-transform:uppercase; font-size:22px; letter-spacing:.05em; display:flex; align-items:center; gap:10px; margin-bottom:4px; }
        .g-brand .mk{ width:26px;height:26px;border:2px solid var(--g-gold);border-radius:50%;display:grid;place-items:center;color:var(--g-gold);font-size:12px; }
        .g-side .sub{ font-family:var(--g-font-label); font-size:10px; letter-spacing:.2em; color:var(--g-muted); text-transform:uppercase; margin-bottom:22px; }
        .g-nav .nav-sec{ display:flex; align-items:center; justify-content:space-between; width:100%; margin:10px 0 4px; padding:10px 13px 8px; border:0; border-top:1px solid var(--g-line, rgba(255,255,255,.08)); background:transparent; cursor:pointer; font-size:10px; letter-spacing:.18em; color:var(--g-muted); text-transform:uppercase; font-family:var(--g-font-label); border-radius:var(--g-r-sm); }
        .g-nav .nav-sec:hover{ color:#fff; }
        .g-nav .nav-sec .chev{ transition:transform .2s; font-size:12px; }
        .g-nav .nav-sec.collapsed .chev{ transform:rotate(-90deg); }
        .g-nav .nav-group{ overflow:hidden; transition:max-height .25s ease; }
        .g-nav .nav-group.collapsed{ max-height:0 !important; }
        .g-nav a{ display:flex; align-items:center; gap:11px; color:#c7ccd4; text-decoration:none; padding:11px 13px; border-radius:var(--g-r-sm); margin-bottom:4px; font-size:14px; transition:var(--g-t); }
        .g-nav a:hover{ background:var(--g-surface-2); color:#fff; }
        .g-nav a.active{ background:linear-gradient(90deg, rgba(201,165,75,.18), transparent); color:var(--g-gold); box-shadow:inset 2px 0 0 var(--g-gold); }
        .g-main{ flex:1; min-width:0; padding:32px 40px; }
        .g-topbar{ display:flex; align-items:center; justify-content:space-between; margin-bottom:26px; }
        /* Puentes Bootstrap -> design system */
        .card{ background:var(--g-surface); border:1px solid var(--g-line); color:var(--g-bone); border-radius:var(--g-r); }
        .table{ --bs-table-bg:transparent; --bs-table-color:var(--g-bone); --bs-table-border-color:var(--g-line); }
        .table thead th{ font-family:var(--g-font-label); text-transform:uppercase; letter-spacing:.08em; font-size:11px; color:var(--g-muted); border-color:var(--g-line); }
        .table td{ border-color:var(--g-line); }
        .table tbody tr:hover{ background:var(--g-surface-2); }
        .form-control,.form-select,.form-control:focus,.form-select:focus{ background:var(--g-surface-2); border-color:var(--g-line); color:var(--g-bone); box-shadow:none; }
        .form-control:focus,.form-select:focus{ border-color:var(--g-gold); box-shadow:0 0 0 3px rgba(201,165,75,.12); }
        .btn-primary{ background:var(--g-gold); border-color:var(--g-gold); color:#1a1405; font-weight:600; }
        .btn-primary:hover{ background:var(--g-gold-2); border-color:var(--g-gold-2); color:#1a1405; }
        .btn-outline-light{ border-color:var(--g-line); color:var(--g-bone); }
        .btn-outline-light:hover{ border-color:var(--g-gold); color:var(--g-gold); background:transparent; }
        h3{ font-family:var(--g-font-display); text-transform:uppercase; letter-spacing:.02em; }
        .badge.bg-primary{ background:rgba(79,140,255,.15)!important; color:var(--g-info); }
        .badge.bg-success{ background:rgba(37,211,102,.15)!important; color:var(--g-ok); }
        .badge.bg-warning{ background:rgba(224,164,34,.15)!important; color:var(--g-warn); }
        .badge.bg-secondary{ background:var(--g-surface-3)!important; color:var(--g-muted); }
        @media(max-width:820px){ .g-shell{ flex-direction:column; } .g-side{ width:100%; height:auto; position:static; } .g-main{ padding:22px; } }
    </style>
</head>
<body>
<div class="g-shell">
    <aside class="g-side">
        <div class="g-brand"><span class="mk">G</span> Glauren</div>
        <div class="sub">Panel administrativo</div>
        @php
            $needHumanNav = \App\Modules\Chaquetas\Models\Lead::where('ai_paused', true)->count();
            $apptCount    = \App\Modules\Chaquetas\Models\Appointment::where('date','>=',now()->toDateString())->where('status','agendada')->count();
            $pendApprovals = \App\Modules\Chaquetas\Models\Student::where('payment_status','en_revision')->count();
        @endphp
        <nav class="g-nav" id="gNav">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> Panel</a>

            <button type="button" class="nav-sec" data-group="ventas">Ventas y clientes <i class="bi bi-chevron-down chev"></i></button>
            <div class="nav-group" data-group="ventas">
                <a href="{{ route('admin.quotes.index') }}" class="{{ request()->routeIs('admin.quotes.*') ? 'active' : '' }}"><i class="bi bi-receipt"></i> Cotizaciones</a>
                <a href="{{ route('admin.leads.index') }}" class="{{ request()->routeIs('admin.leads.*') ? 'active' : '' }}"><i class="bi bi-person-lines-fill"></i> Leads / Contactos @if($needHumanNav)<span class="badge bg-warning ms-1">{{ $needHumanNav }}</span>@endif</a>
                <a href="{{ route('admin.appointments.index') }}" class="{{ request()->routeIs('admin.appointments.*') ? 'active' : '' }}"><i class="bi bi-calendar-check"></i> Citas / Contacto @if($apptCount)<span class="badge bg-info text-dark ms-1">{{ $apptCount }}</span>@endif</a>
                <a href="{{ route('admin.ia.edit') }}" class="{{ request()->routeIs('admin.ia.*') ? 'active' : '' }}"><i class="bi bi-robot"></i> Entrenar IA (WhatsApp)</a>
                <a href="{{ route('admin.broadcast.index') }}" class="{{ request()->routeIs('admin.broadcast.*') ? 'active' : '' }}"><i class="bi bi-megaphone"></i> Difusión (WhatsApp)</a>
            </div>

            <button type="button" class="nav-sec" data-group="produccion">Producción <i class="bi bi-chevron-down chev"></i></button>
            <div class="nav-group" data-group="produccion">
                <a href="{{ route('admin.contracts.index') }}" class="{{ request()->routeIs('admin.contracts.*') ? 'active' : '' }}"><i class="bi bi-file-earmark-check"></i> Contratos</a>
                <a href="{{ route('admin.schools.index') }}" class="{{ request()->routeIs('admin.schools.*') ? 'active' : '' }}"><i class="bi bi-building"></i> Colegios</a>
                <a href="{{ route('admin.types.index') }}" class="{{ request()->routeIs('admin.types.*') ? 'active' : '' }}"><i class="bi bi-tags"></i> Tipos de chaqueta</a>
                <a href="{{ route('admin.approvals.index') }}" class="{{ request()->routeIs('admin.approvals.*') ? 'active' : '' }}"><i class="bi bi-patch-check"></i> Pagos por aprobar @if($pendApprovals)<span class="badge bg-warning ms-1">{{ $pendApprovals }}</span>@endif</a>
                <a href="{{ route('admin.reminders.index') }}" class="{{ request()->routeIs('admin.reminders.*') ? 'active' : '' }}"><i class="bi bi-bell"></i> Recordatorios de pago</a>
            </div>

            <button type="button" class="nav-sec" data-group="finanzas">Finanzas <i class="bi bi-chevron-down chev"></i></button>
            <div class="nav-group" data-group="finanzas">
                <a href="{{ route('admin.expenses.index') }}" class="{{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}"><i class="bi bi-receipt-cutoff"></i> Gastos / Facturas</a>
                <a href="{{ route('admin.finance.index') }}" class="{{ request()->routeIs('admin.finance.*') ? 'active' : '' }}"><i class="bi bi-graph-up-arrow"></i> Finanzas</a>
            </div>

            <button type="button" class="nav-sec" data-group="contenido">Contenido <i class="bi bi-chevron-down chev"></i></button>
            <div class="nav-group" data-group="contenido">
                <a href="{{ route('admin.homedesign.index') }}" class="{{ request()->routeIs('admin.homedesign.*') ? 'active' : '' }}"><i class="bi bi-columns-gap"></i> Diseño del Home</a>
                <a href="{{ route('admin.settings.edit') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"><i class="bi bi-fonts"></i> Textos / Home</a>
                <a href="{{ route('admin.blocks.index') }}" class="{{ request()->routeIs('admin.blocks.*') ? 'active' : '' }}"><i class="bi bi-grid-1x2"></i> Bloques del home</a>
                <a href="{{ route('admin.menu.index') }}" class="{{ request()->routeIs('admin.menu.*') ? 'active' : '' }}"><i class="bi bi-list-ul"></i> Menú</a>
                <a href="{{ route('admin.collections.index') }}" class="{{ request()->routeIs('admin.collections.*') ? 'active' : '' }}"><i class="bi bi-images"></i> Colecciones</a>
                <a href="{{ route('admin.showcase.index') }}" class="{{ request()->routeIs('admin.showcase.*') ? 'active' : '' }}"><i class="bi bi-camera"></i> Promociones</a>
                <a href="{{ route('admin.testimonials.index') }}" class="{{ request()->routeIs('admin.testimonials.*') ? 'active' : '' }}"><i class="bi bi-chat-quote"></i> Testimonios</a>
                <a href="{{ route('admin.pages.index') }}" class="{{ request()->routeIs('admin.pages.*') ? 'active' : '' }}"><i class="bi bi-file-earmark-text"></i> Páginas</a>
                <a href="{{ route('admin.posts.index') }}" class="{{ request()->routeIs('admin.posts.*') ? 'active' : '' }}"><i class="bi bi-newspaper"></i> Noticias</a>
                <a href="{{ route('admin.ranking.index') }}" class="{{ request()->routeIs('admin.ranking.*') ? 'active' : '' }}"><i class="bi bi-trophy"></i> Ranking colegios</a>
                <a href="{{ route('admin.seo.index') }}" class="{{ request()->routeIs('admin.seo.*') || request()->routeIs('admin.faqs.*') ? 'active' : '' }}"><i class="bi bi-search"></i> SEO y FAQ</a>
            </div>

            <button type="button" class="nav-sec" data-group="config">Configuración <i class="bi bi-chevron-down chev"></i></button>
            <div class="nav-group" data-group="config">
                <a href="{{ route('admin.apikeys.edit') }}" class="{{ request()->routeIs('admin.apikeys.*') ? 'active' : '' }}"><i class="bi bi-key"></i> Llaves API</a>
            </div>

            <div class="sub nav-sec" style="cursor:default;border-top:1px solid var(--g-line,rgba(255,255,255,.08))">Sitio</div>
            <a href="{{ route('home') }}" target="_blank"><i class="bi bi-box-arrow-up-right"></i> Ver sitio</a>
            <hr class="g-divider">
            <form method="POST" action="{{ route('admin.logout') }}">@csrf
                <button class="g-btn g-btn--ghost g-btn--sm g-btn--block"><i class="bi bi-box-arrow-left"></i> Salir</button>
            </form>
        </nav>
        <script>
        (function(){
            var nav = document.getElementById('gNav'); if(!nav) return;
            var KEY = 'glaurenNavCollapsed';
            var stored = {};
            try { stored = JSON.parse(localStorage.getItem(KEY) || '{}'); } catch(e){}

            nav.querySelectorAll('.nav-group[data-group]').forEach(function(group){
                var name = group.dataset.group;
                var header = nav.querySelector('.nav-sec[data-group="'+name+'"]');
                var hasActive = !!group.querySelector('a.active');
                // Por defecto TODAS cerradas (menú compacto). Se abre solo la de la página actual,
                // o las que el usuario haya abierto antes (recordado).
                var collapsed = hasActive ? false : (stored[name] !== false);

                function apply(withAnim){
                    if(collapsed){
                        group.classList.add('collapsed'); group.style.maxHeight='0px';
                        header.classList.add('collapsed');
                    } else {
                        header.classList.remove('collapsed'); group.classList.remove('collapsed');
                        group.style.maxHeight = withAnim ? group.scrollHeight+'px' : 'none';
                    }
                }
                apply(false);

                header.addEventListener('click', function(){
                    collapsed = !collapsed;
                    if(!collapsed){ group.classList.remove('collapsed'); group.style.maxHeight = group.scrollHeight+'px'; header.classList.remove('collapsed'); }
                    else { group.style.maxHeight = group.scrollHeight+'px'; requestAnimationFrame(function(){ group.style.maxHeight='0px'; group.classList.add('collapsed'); header.classList.add('collapsed'); }); }
                    stored[name] = collapsed;
                    try { localStorage.setItem(KEY, JSON.stringify(stored)); } catch(e){}
                });
            });
        })();
        </script>
    </aside>
    <main class="g-main">
        @if(session('ok'))<div class="g-badge g-badge--ok" style="margin-bottom:18px;padding:10px 16px;border-radius:var(--g-r-sm)"><i class="bi bi-check-circle"></i> {{ session('ok') }}</div>@endif
        @yield('content')
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
