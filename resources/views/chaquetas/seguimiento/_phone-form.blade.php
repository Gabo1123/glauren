<form method="POST" action="{{ route('seguimiento.phone') }}" class="phone-form">
    @csrf
    <input type="hidden" name="student_id" value="{{ $student->id }}">
    <input type="hidden" name="document" value="{{ $student->document }}">
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-top:8px">
        <input type="tel" name="phone" value="{{ $student->phone }}" placeholder="Ej: 300 123 4567"
               style="flex:1;min-width:180px;background:var(--panel2);border:2px solid #000;border-radius:8px;padding:11px;color:var(--paper);font-size:14px">
        <button class="btn2" style="width:auto;padding:11px 20px">Guardar WhatsApp</button>
    </div>
    @error('phone')<div class="note err-note" style="margin-top:8px">{{ $message }}</div>@enderror
    <div style="font-size:12px;color:var(--muted);margin-top:8px">Solo lo usamos para avisarte del avance de tu pedido. Nada de spam.</div>
</form>
