@extends('layouts.admin')
@section('title', 'SEO y FAQ')

@section('content')
<h3 class="mb-4">SEO y FAQ</h3>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<div class="card p-4 mb-4">
    <div class="g-card__title" style="color:var(--g-gold)">SEO global (valores por defecto)</div>
    <form method="POST" action="{{ route('admin.seo.globals') }}">
        @csrf @method('PUT')
        <div class="mb-3"><label class="form-label small">Título por defecto</label><input type="text" name="seo_default_title" class="form-control" value="{{ $values['seo_default_title'] ?? '' }}"></div>
        <div class="mb-3"><label class="form-label small">Descripción por defecto</label><textarea name="seo_default_description" class="form-control" rows="2">{{ $values['seo_default_description'] ?? '' }}</textarea></div>
        <div class="mb-3"><label class="form-label small">Palabras clave</label><input type="text" name="seo_keywords" class="form-control" value="{{ $values['seo_keywords'] ?? '' }}"></div>
        <div class="mb-3"><label class="form-label small">Imagen para compartir (URL)</label><input type="text" name="seo_og_image" class="form-control" value="{{ $values['seo_og_image'] ?? '' }}"></div>
        <div class="mb-3"><label class="form-label small">Contenido SEO del home (HTML)</label><textarea name="home_seo_content" class="form-control" rows="6" style="font-family:monospace;font-size:13px">{{ $values['home_seo_content'] ?? '' }}</textarea></div>
        <button class="btn btn-primary"><i class="bi bi-save"></i> Guardar SEO global</button>
    </form>
</div>

<div class="card p-4 mb-4">
    <div class="g-card__title" style="color:var(--g-gold)">Meta por página (title y descripción)</div>
    @foreach($pages as $p)
        <form method="POST" action="{{ route('admin.seo.page', $p) }}" class="mb-4 pb-3" style="border-bottom:1px solid var(--g-line)">
            @csrf @method('PUT')
            <div class="d-flex justify-content-between align-items-center mb-2">
                <b>{{ $p->label ?: $p->page_key }}</b> <span class="text-secondary small"><code>{{ $p->page_key }}</code></span>
            </div>
            <div class="row g-2">
                <div class="col-md-6"><label class="form-label small">Título (SEO)</label><input type="text" name="title" class="form-control form-control-sm" value="{{ $p->title }}" maxlength="70"></div>
                <div class="col-md-6"><label class="form-label small">Palabras clave</label><input type="text" name="keywords" class="form-control form-control-sm" value="{{ $p->keywords }}"></div>
                <div class="col-12"><label class="form-label small">Descripción (máx 160)</label><textarea name="description" class="form-control form-control-sm" rows="2" maxlength="320">{{ $p->description }}</textarea></div>
            </div>
            <div class="d-flex align-items-center gap-3 mt-2">
                <button class="btn btn-primary btn-sm"><i class="bi bi-save"></i> Guardar</button>
                <div class="form-check form-switch mb-0"><input class="form-check-input" type="checkbox" name="noindex" value="1" id="ni{{ $p->id }}" {{ $p->noindex ? 'checked' : '' }}><label class="form-check-label small" for="ni{{ $p->id }}">No indexar</label></div>
            </div>
        </form>
    @endforeach
</div>

<div class="card p-4">
    <div class="g-card__title" style="color:var(--g-gold)">Preguntas frecuentes (FAQ del home)</div>
    <form method="POST" action="{{ route('admin.faqs.store') }}" class="row g-2 align-items-end mb-3">
        @csrf
        <div class="col-md-4"><label class="form-label small">Pregunta</label><input type="text" name="question" class="form-control form-control-sm" required></div>
        <div class="col-md-6"><label class="form-label small">Respuesta</label><input type="text" name="answer" class="form-control form-control-sm" required></div>
        <div class="col-md-2"><button class="btn btn-primary btn-sm w-100"><i class="bi bi-plus-lg"></i> Agregar</button></div>
    </form>
    @foreach($faqs as $f)
        <div class="d-flex gap-2 align-items-center mb-2">
            <form method="POST" action="{{ route('admin.faqs.update', $f) }}" class="row g-2 align-items-center flex-grow-1">
                @csrf @method('PUT')
                <div class="col-md-4"><input type="text" name="question" class="form-control form-control-sm" value="{{ $f->question }}"></div>
                <div class="col-md-6"><input type="text" name="answer" class="form-control form-control-sm" value="{{ $f->answer }}"></div>
                <div class="col-md-1"><input type="number" name="sort" class="form-control form-control-sm" value="{{ $f->sort }}" title="Orden"></div>
                <div class="col-md-1"><button class="btn btn-outline-light btn-sm w-100"><i class="bi bi-save"></i></button></div>
            </form>
            <form method="POST" action="{{ route('admin.faqs.destroy', $f) }}" onsubmit="return confirm('¿Eliminar pregunta?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
            </form>
        </div>
    @endforeach
</div>
@endsection
