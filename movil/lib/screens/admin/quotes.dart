import 'dart:async';

import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../api.dart';
import '../../format.dart';
import '../../theme.dart';
import '../../widgets/common.dart';

const quoteStatuses = {
  'nuevo': 'Nuevo',
  'contactado': 'Contactado',
  'enviada': 'Enviada',
  'vista': 'La vio',
  'negociando': 'Negociando',
  'cerrado': 'Ganada',
  'cancelado': 'Perdida',
};

Color statusColor(String? s) => switch (s) {
      'cerrado' => Brand.ok,
      'cancelado' => Brand.bad,
      'vista' => Brand.info,
      'negociando' => Brand.orange,
      _ => Brand.yellow,
    };

/// Lista de clientes / cotizaciones.
class QuotesTab extends StatefulWidget {
  const QuotesTab({super.key});

  @override
  State<QuotesTab> createState() => _QuotesTabState();
}

class _QuotesTabState extends State<QuotesTab> {
  final _search = TextEditingController();
  String? _status;
  Timer? _debounce;
  int _version = 0;

  @override
  void dispose() {
    _search.dispose();
    _debounce?.cancel();
    super.dispose();
  }

  void _refresh() => setState(() => _version++);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Column(children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 6),
          child: TextField(
            controller: _search,
            decoration: const InputDecoration(hintText: 'Buscar nombre, colegio, celular o N°', prefixIcon: Icon(Icons.search)),
            onChanged: (_) {
              _debounce?.cancel();
              _debounce = Timer(const Duration(milliseconds: 450), _refresh);
            },
          ),
        ),
        SizedBox(
          height: 44,
          child: ListView(scrollDirection: Axis.horizontal, padding: const EdgeInsets.symmetric(horizontal: 12), children: [
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 4),
              child: ChoiceChip(label: const Text('Todas'), selected: _status == null, onSelected: (_) => setState(() {
                    _status = null;
                    _version++;
                  })),
            ),
            for (final e in quoteStatuses.entries)
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 4),
                child: ChoiceChip(label: Text(e.value), selected: _status == e.key, onSelected: (_) => setState(() {
                      _status = e.key;
                      _version++;
                    })),
              ),
          ]),
        ),
        Expanded(
          child: Loader<Json>(
            key: ValueKey('q$_version'),
            load: () => Api.instance.get('/admin/cotizaciones', query: {
              if (_search.text.trim().isNotEmpty) 'q': _search.text.trim(),
              if (_status != null) 'status': _status!,
            }),
            builder: (context, d, reload) {
              final list = (d['data'] as List).cast<Map<String, dynamic>>();
              if (list.isEmpty) {
                return ListView(children: const [SizedBox(height: 80), Center(child: Text('No hay cotizaciones.', style: TextStyle(color: Brand.muted)))]);
              }
              return ListView.builder(
                padding: const EdgeInsets.fromLTRB(16, 6, 16, 90),
                itemCount: list.length,
                itemBuilder: (_, i) => QuoteTile(list[i], onBack: reload),
              );
            },
          ),
        ),
      ]),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: Brand.yellow,
        foregroundColor: Brand.ink,
        icon: const Icon(Icons.person_add_alt_1),
        label: const Text('Nuevo cliente', style: TextStyle(fontWeight: FontWeight.w800)),
        onPressed: () async {
          final id = await Navigator.push<int>(context, MaterialPageRoute(builder: (_) => const NewQuoteScreen()));
          if (id != null && context.mounted) {
            await Navigator.push(context, MaterialPageRoute(builder: (_) => QuoteScreen(id: id)));
          }
          _refresh();
        },
      ),
    );
  }
}

class QuoteTile extends StatelessWidget {
  const QuoteTile(this.q, {super.key, this.onBack});
  final Map<String, dynamic> q;
  final Future<void> Function()? onBack;

  @override
  Widget build(BuildContext context) {
    final follow = q['follow_up_at'];
    return ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 4),
      onTap: () async {
        await Navigator.push(context, MaterialPageRoute(builder: (_) => QuoteScreen(id: toInt(q['id']))));
        await onBack?.call();
      },
      leading: CircleAvatar(
        backgroundColor: statusColor(q['status'] as String?).withValues(alpha: 0.2),
        child: Text(str(q['customer_name'], '?').substring(0, 1).toUpperCase(), style: TextStyle(color: statusColor(q['status'] as String?), fontWeight: FontWeight.w900)),
      ),
      title: Text(str(q['customer_name']), style: const TextStyle(fontWeight: FontWeight.w700)),
      subtitle: Text([str(q['school']), str(q['code']), if (follow != null) '📞 ${shortDate(follow)}'].where((s) => s.isNotEmpty).join(' · '),
          maxLines: 1, overflow: TextOverflow.ellipsis),
      trailing: Pill(str(q['status_label']), color: statusColor(q['status'] as String?)),
    );
  }
}

/// Detalle de una cotización: llamar, WhatsApp, etapa, seguimiento y ficha.
class QuoteScreen extends StatefulWidget {
  const QuoteScreen({super.key, required this.id});
  final int id;

  @override
  State<QuoteScreen> createState() => _QuoteScreenState();
}

class _QuoteScreenState extends State<QuoteScreen> {
  int _v = 0;
  Json? _q;

  void _set(Json? q) {
    if (q != null) setState(() => _q = q);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(str(_q?['code'], 'Cotización'))),
      body: Loader<Json>(
        key: ValueKey(_v),
        load: () async {
          final q = await Api.instance.get('/admin/cotizaciones/${widget.id}');
          _q = q;
          return q;
        },
        builder: (context, loaded, reload) {
          final q = _q ?? loaded;
          final client = ((q['client'] as Map?) ?? {}).cast<String, dynamic>();
          final contacts = ((client['contacts'] as List?) ?? []).cast<Map>();
          final phone = str(q['customer_phone']);
          return ListView(padding: const EdgeInsets.all(16), children: [
            Text(str(q['customer_name']), style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w900)),
            if (str(q['school']).isNotEmpty) Text(str(q['school']), style: const TextStyle(color: Brand.muted, fontSize: 15)),
            const SizedBox(height: 10),
            Wrap(spacing: 8, runSpacing: 8, children: [
              Pill(str(q['status_label']), color: statusColor(q['status'] as String?)),
              Pill('${q['quantity']} est. · ${money(q['total'])}', color: Brand.paper),
              if (toInt(q['gift_jackets']) > 0) Pill('🎁 ${q['gift_jackets']} de obsequio', color: Brand.orange),
            ]),
            const SizedBox(height: 16),
            Row(children: [
              Expanded(
                child: FilledButton.icon(
                  style: FilledButton.styleFrom(backgroundColor: Brand.ok, foregroundColor: Brand.ink),
                  icon: const Icon(Icons.chat),
                  label: const Text('WhatsApp'),
                  onPressed: () async {
                    await launchUrl(Uri.parse(str(q['whatsapp_url'])), mode: LaunchMode.externalApplication);
                    if (['nuevo', 'contactado'].contains(q['status'])) {
                      _set(await run(context, () => Api.instance.post('/admin/cotizaciones/${widget.id}/enviada'), ok: 'Marcada como enviada.'));
                    }
                  },
                ),
              ),
              const SizedBox(width: 10),
              IconButton.filledTonal(
                icon: const Icon(Icons.call),
                onPressed: phone.isEmpty ? null : () => launchUrl(Uri.parse('tel:${phone.replaceAll(' ', '')}')),
              ),
              IconButton.filledTonal(
                icon: const Icon(Icons.open_in_new),
                tooltip: 'Ver cotización',
                onPressed: () => launchUrl(Uri.parse(str(q['public_url'])), mode: LaunchMode.externalApplication),
              ),
            ]),
            const SizedBox(height: 16),
            Box(
              title: 'Etapa',
              icon: Icons.flag_outlined,
              child: Wrap(spacing: 8, runSpacing: 8, children: [
                for (final e in {'contactado': 'Contactado', 'enviada': 'Enviada', 'negociando': 'Negociando'}.entries)
                  ChoiceChip(
                    label: Text(e.value),
                    selected: q['status'] == e.key,
                    onSelected: (_) async => _set(await run(context, () => Api.instance.post('/admin/cotizaciones/${widget.id}/etapa', {'stage': e.key}))),
                  ),
                ActionChip(
                  avatar: const Icon(Icons.emoji_events, size: 18, color: Brand.ok),
                  label: const Text('Ganada'),
                  onPressed: q['status'] == 'cerrado' ? null : () => _win(q),
                ),
                ActionChip(
                  avatar: Icon(q['status'] == 'cancelado' ? Icons.replay : Icons.close, size: 18, color: Brand.bad),
                  label: Text(q['status'] == 'cancelado' ? 'Reabrir' : 'Perdida'),
                  onPressed: () async {
                    final stage = q['status'] == 'cancelado' ? 'reabrir' : 'perdida';
                    if (stage == 'perdida' && !await confirm(context, '¿Marcar como perdida?')) return;
                    if (!context.mounted) return;
                    _set(await run(context, () => Api.instance.post('/admin/cotizaciones/${widget.id}/etapa', {'stage': stage})));
                  },
                ),
              ]),
            ),
            Box(
              title: 'Próxima llamada',
              icon: Icons.event_outlined,
              trailing: Text(q['follow_up_at'] == null ? 'Sin fecha' : longDate(q['follow_up_at']), style: const TextStyle(color: Brand.yellow)),
              child: Wrap(spacing: 8, runSpacing: 8, children: [
                for (final e in {'Hoy': 0, 'Mañana': 1, 'En 3 días': 3, 'En 1 semana': 7}.entries)
                  ActionChip(
                    label: Text(e.key),
                    onPressed: () async => _set(await run(context, () => Api.instance.post('/admin/cotizaciones/${widget.id}/seguimiento',
                        {'follow_up_at': isoDay(DateTime.now().add(Duration(days: e.value)))}), ok: 'Seguimiento agendado.')),
                  ),
                ActionChip(
                  avatar: const Icon(Icons.edit_note, size: 18),
                  label: const Text('Nota'),
                  onPressed: () => _note(),
                ),
              ]),
            ),
            Box(
              title: 'Ficha del cliente',
              icon: Icons.badge_outlined,
              trailing: TextButton(onPressed: () => _editClient(q), child: const Text('Editar')),
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                _kv('Celular', phone),
                _kv('Correo', str(q['customer_email'])),
                _kv('Curso', str(client['course'])),
                _kv('Ciudad', str(client['city'])),
                _kv('Quién decide', str(client['decides'])),
                _kv('Fecha de grado', str(client['event_date'])),
                _kv('Presupuesto', str(client['budget'])),
                for (final c in contacts)
                  ListTile(
                    dense: true,
                    contentPadding: EdgeInsets.zero,
                    leading: const Icon(Icons.person_outline),
                    title: Text('${c['name'] ?? ''}${c['role'] != null ? ' · ${c['role']}' : ''}'),
                    trailing: c['phone'] == null
                        ? null
                        : IconButton(
                            icon: const Icon(Icons.chat, color: Brand.ok),
                            onPressed: () => launchUrl(Uri.parse('https://wa.me/${waNumber('${c['phone']}')}'), mode: LaunchMode.externalApplication),
                          ),
                  ),
              ]),
            ),
            Box(
              title: 'Lo cotizado',
              icon: Icons.checkroom_outlined,
              child: Column(children: [
                for (final l in (q['lines'] as List).cast<Map>())
                  Padding(
                    padding: const EdgeInsets.only(bottom: 6),
                    child: Row(children: [
                      Expanded(child: Text('${l['label']}')),
                      Text(money(l['price']), style: const TextStyle(fontWeight: FontWeight.w700)),
                    ]),
                  ),
                const Divider(),
                Row(children: [
                  const Expanded(child: Text('Por estudiante', style: TextStyle(color: Brand.muted))),
                  Text(money(q['unit_total']), style: const TextStyle(fontWeight: FontWeight.w900, color: Brand.yellow)),
                ]),
              ]),
            ),
            Box(
              title: 'Historial',
              icon: Icons.history,
              child: Column(children: [
                for (final a in (q['activities'] as List).cast<Map>())
                  ListTile(
                    dense: true,
                    contentPadding: EdgeInsets.zero,
                    title: Text('${a['text']}'),
                    subtitle: Text(ago(a['at'])),
                  ),
              ]),
            ),
          ]);
        },
      ),
    );
  }

  Widget _kv(String k, String v) {
    if (v.isEmpty) return const SizedBox.shrink();
    return Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
        SizedBox(width: 120, child: Text(k, style: const TextStyle(color: Brand.muted))),
        Expanded(child: Text(v)),
      ]),
    );
  }

  Future<void> _win(Json q) async {
    final ctrl = TextEditingController(text: str(q['school']));
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: Brand.panel,
        title: const Text('¡Cotización ganada! 🎉'),
        content: Column(mainAxisSize: MainAxisSize.min, children: [
          const Text('Se crea el colegio y el contrato queda listo para completar en el panel.'),
          const SizedBox(height: 12),
          TextField(controller: ctrl, decoration: const InputDecoration(labelText: 'Nombre del colegio')),
        ]),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancelar')),
          FilledButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Confirmar')),
        ],
      ),
    );
    if (ok != true || !mounted) return;
    _set(await run(context, () => Api.instance.post('/admin/cotizaciones/${widget.id}/etapa', {'stage': 'ganada', 'school_name': ctrl.text.trim()}), ok: '¡Cliente ganado!'));
  }

  Future<void> _note() async {
    final ctrl = TextEditingController();
    final text = await showDialog<String>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: Brand.panel,
        title: const Text('Nota'),
        content: TextField(controller: ctrl, maxLines: 4, autofocus: true, decoration: const InputDecoration(hintText: 'Qué hablaron, qué quedó pendiente…')),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Cancelar')),
          FilledButton(onPressed: () => Navigator.pop(ctx, ctrl.text.trim()), child: const Text('Guardar')),
        ],
      ),
    );
    if (text == null || text.isEmpty || !mounted) return;
    _set(await run(context, () => Api.instance.post('/admin/cotizaciones/${widget.id}/seguimiento', {'note': text}), ok: 'Nota guardada.'));
  }

  Future<void> _editClient(Json q) async {
    final updated = await Navigator.push<Json>(context, MaterialPageRoute(builder: (_) => ClientFormScreen(quote: q)));
    if (updated != null) _set(updated);
  }
}

/// Formulario de la ficha del cliente.
class ClientFormScreen extends StatefulWidget {
  const ClientFormScreen({super.key, required this.quote});
  final Json quote;

  @override
  State<ClientFormScreen> createState() => _ClientFormScreenState();
}

class _ClientFormScreenState extends State<ClientFormScreen> {
  static const fields = {
    'course': 'Curso / grado',
    'city': 'Ciudad',
    'role': 'Cargo o rol del contacto',
    'decides': 'Quién decide',
    'event_date': 'Fecha de grado / entrega deseada',
    'budget': 'Presupuesto por estudiante',
    'competition': 'Otras cotizaciones / competencia',
    'found_us': 'Cómo nos conoció',
  };

  late final Map<String, TextEditingController> _c;
  late final List<List<TextEditingController>> _contacts;
  bool _busy = false;

  @override
  void initState() {
    super.initState();
    final q = widget.quote;
    final client = ((q['client'] as Map?) ?? {}).cast<String, dynamic>();
    _c = {
      'customer_name': TextEditingController(text: str(q['customer_name'])),
      'customer_phone': TextEditingController(text: str(q['customer_phone'])),
      'customer_email': TextEditingController(text: str(q['customer_email'])),
      'school': TextEditingController(text: str(q['school'])),
      'quantity': TextEditingController(text: str(q['quantity'])),
      for (final k in fields.keys) k: TextEditingController(text: str(client[k])),
    };
    final list = ((client['contacts'] as List?) ?? []).cast<Map>();
    _contacts = [
      for (final c in list) [TextEditingController(text: str(c['name'])), TextEditingController(text: str(c['role'])), TextEditingController(text: str(c['phone']))],
      [TextEditingController(), TextEditingController(), TextEditingController()],
    ];
  }

  @override
  void dispose() {
    for (final c in _c.values) {
      c.dispose();
    }
    for (final row in _contacts) {
      for (final c in row) {
        c.dispose();
      }
    }
    super.dispose();
  }

  Future<void> _save() async {
    setState(() => _busy = true);
    final body = <String, dynamic>{
      'customer_name': _c['customer_name']!.text.trim(),
      'customer_phone': _c['customer_phone']!.text.trim(),
      'customer_email': _c['customer_email']!.text.trim().isEmpty ? null : _c['customer_email']!.text.trim(),
      'school': _c['school']!.text.trim(),
      if (widget.quote['kind'] == 'colegio' && toInt(_c['quantity']!.text) > 0) 'quantity': toInt(_c['quantity']!.text),
      'client': {for (final k in fields.keys) k: _c[k]!.text.trim()},
      'contacts': [
        for (final r in _contacts)
          if (r[0].text.trim().isNotEmpty || r[2].text.trim().isNotEmpty) {'name': r[0].text.trim(), 'role': r[1].text.trim(), 'phone': r[2].text.trim()},
      ],
    };
    final r = await run(context, () => Api.instance.put('/admin/cotizaciones/${widget.quote['id']}/cliente', body), ok: 'Ficha guardada.');
    if (!mounted) return;
    setState(() => _busy = false);
    if (r != null) Navigator.pop(context, r);
  }

  Widget _f(String key, String label, {TextInputType? type}) => Padding(
        padding: const EdgeInsets.only(bottom: 10),
        child: TextField(controller: _c[key], keyboardType: type, decoration: InputDecoration(labelText: label)),
      );

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Ficha del cliente')),
      body: ListView(padding: const EdgeInsets.all(16), children: [
        _f('customer_name', 'Nombre del contacto'),
        _f('customer_phone', 'Celular / WhatsApp', type: TextInputType.phone),
        _f('customer_email', 'Correo', type: TextInputType.emailAddress),
        _f('school', widget.quote['kind'] == 'empresa' ? 'Empresa' : 'Colegio'),
        if (widget.quote['kind'] == 'colegio') _f('quantity', 'Estudiantes', type: TextInputType.number),
        for (final e in fields.entries) _f(e.key, e.value),
        const SizedBox(height: 6),
        const Text('Otros contactos (rector, coordinador, papás del comité…)', style: TextStyle(fontWeight: FontWeight.w700)),
        const SizedBox(height: 8),
        for (final r in _contacts)
          Padding(
            padding: const EdgeInsets.only(bottom: 8),
            child: Row(children: [
              Expanded(flex: 4, child: TextField(controller: r[0], decoration: const InputDecoration(hintText: 'Nombre'))),
              const SizedBox(width: 6),
              Expanded(flex: 3, child: TextField(controller: r[1], decoration: const InputDecoration(hintText: 'Rol'))),
              const SizedBox(width: 6),
              Expanded(flex: 4, child: TextField(controller: r[2], keyboardType: TextInputType.phone, decoration: const InputDecoration(hintText: 'Celular'))),
            ]),
          ),
        TextButton.icon(
          onPressed: () => setState(() => _contacts.add([TextEditingController(), TextEditingController(), TextEditingController()])),
          icon: const Icon(Icons.add),
          label: const Text('Otro contacto'),
        ),
        const SizedBox(height: 12),
        FilledButton(onPressed: _busy ? null : _save, child: const Text('Guardar ficha')),
        const SizedBox(height: 30),
      ]),
    );
  }
}

/// Nuevo cliente con su cotización (combo + cantidad).
class NewQuoteScreen extends StatefulWidget {
  const NewQuoteScreen({super.key});

  @override
  State<NewQuoteScreen> createState() => _NewQuoteScreenState();
}

class _NewQuoteScreenState extends State<NewQuoteScreen> {
  final _name = TextEditingController();
  final _phone = TextEditingController();
  final _email = TextEditingController();
  final _school = TextEditingController();
  final _qty = TextEditingController(text: '30');
  final _notes = TextEditingController();
  int? _combo;
  final Set<String> _extras = {};
  bool _busy = false;

  @override
  void dispose() {
    for (final c in [_name, _phone, _email, _school, _qty, _notes]) {
      c.dispose();
    }
    super.dispose();
  }

  Future<void> _save() async {
    setState(() => _busy = true);
    final r = await run(context, () => Api.instance.post('/admin/cotizaciones', {
          'customer_name': _name.text.trim(),
          'customer_phone': _phone.text.trim(),
          if (_email.text.trim().isNotEmpty) 'customer_email': _email.text.trim(),
          'school': _school.text.trim(),
          'quantity': toInt(_qty.text) > 0 ? toInt(_qty.text) : 1,
          'combo': _combo,
          'extras': _extras.toList(),
          'notes': _notes.text.trim(),
        }), ok: 'Cliente creado.');
    if (!mounted) return;
    setState(() => _busy = false);
    if (r != null) Navigator.pop(context, toInt(r['id']));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Nuevo cliente')),
      body: Loader<Json>(
        load: () => Api.instance.get('/admin/catalogo'),
        builder: (context, cat, _) {
          final combos = (cat['combos'] as List).cast<Map>();
          final extras = (cat['extras'] as List).cast<Map>();
          return ListView(padding: const EdgeInsets.all(16), children: [
            TextField(controller: _name, textCapitalization: TextCapitalization.words, decoration: const InputDecoration(labelText: 'Nombre *')),
            const SizedBox(height: 10),
            TextField(controller: _phone, keyboardType: TextInputType.phone, decoration: const InputDecoration(labelText: 'Celular / WhatsApp *')),
            const SizedBox(height: 10),
            TextField(controller: _email, keyboardType: TextInputType.emailAddress, decoration: const InputDecoration(labelText: 'Correo (opcional)')),
            const SizedBox(height: 10),
            TextField(controller: _school, textCapitalization: TextCapitalization.words, decoration: const InputDecoration(labelText: 'Colegio')),
            const SizedBox(height: 10),
            TextField(controller: _qty, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Número de estudiantes')),
            const SizedBox(height: 18),
            const Text('Combo', style: TextStyle(fontWeight: FontWeight.w800)),
            const SizedBox(height: 8),
            for (final c in combos)
              Card(
                color: _combo == toInt(c['number']) ? Brand.yellow.withValues(alpha: 0.15) : Brand.panel,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                  side: BorderSide(color: _combo == toInt(c['number']) ? Brand.yellow : Colors.transparent),
                ),
                child: ListTile(
                  onTap: () => setState(() => _combo = _combo == toInt(c['number']) ? null : toInt(c['number'])),
                  leading: CircleAvatar(backgroundColor: Brand.ink, child: Text('${c['number']}', style: const TextStyle(color: Brand.yellow, fontWeight: FontWeight.w900))),
                  title: Text((c['names'] as List).join(' + ')),
                  subtitle: c['gift'] == null ? null : Text('${c['gift']}'),
                  trailing: Text(money(c['price']), style: const TextStyle(fontWeight: FontWeight.w800)),
                ),
              ),
            const SizedBox(height: 12),
            const Text('Adicionales', style: TextStyle(fontWeight: FontWeight.w800)),
            Wrap(spacing: 8, runSpacing: 4, children: [
              for (final e in extras)
                FilterChip(
                  label: Text('${e['name']} · ${money(e['price'])}', style: const TextStyle(fontSize: 12)),
                  selected: _extras.contains(e['key']),
                  onSelected: (on) => setState(() => on ? _extras.add('${e['key']}') : _extras.remove(e['key'])),
                ),
            ]),
            const SizedBox(height: 12),
            TextField(controller: _notes, maxLines: 3, decoration: const InputDecoration(labelText: 'Notas')),
            const SizedBox(height: 18),
            FilledButton(onPressed: _busy ? null : _save, child: const Text('Crear y abrir')),
            const SizedBox(height: 30),
          ]);
        },
      ),
    );
  }
}
