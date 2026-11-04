import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../api.dart';
import '../../format.dart';
import '../../offline.dart';
import '../../theme.dart';
import '../../widgets/common.dart';

class ContractsTab extends StatelessWidget {
  const ContractsTab({super.key});

  @override
  Widget build(BuildContext context) {
    return Loader<Json>(
      load: () => Api.instance.get('/admin/contratos'),
      builder: (context, d, reload) {
        final list = (d['data'] as List).cast<Map<String, dynamic>>();
        return ListView(padding: const EdgeInsets.all(16), children: [
          const Text('Contratos activos', style: TextStyle(fontSize: 24, fontWeight: FontWeight.w900)),
          const SizedBox(height: 12),
          if (list.isEmpty) const Text('No hay contratos activos.', style: TextStyle(color: Brand.muted)),
          for (final c in list)
            Box(
              padding: const EdgeInsets.all(14),
              child: InkWell(
                onTap: () async {
                  await Navigator.push(context, MaterialPageRoute(builder: (_) => ContractScreen(id: toInt(c['id']))));
                  await reload();
                },
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Row(children: [
                    Expanded(child: Text(str(c['school'], str(c['code'])), style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16))),
                    Pill(str(c['stage_label'])),
                  ]),
                  const SizedBox(height: 8),
                  Text('${c['code']} · entrega ${shortDate(c['delivery_date'])}', style: const TextStyle(color: Brand.muted, fontSize: 13)),
                  const SizedBox(height: 10),
                  Row(children: [
                    _mini(Icons.straighten, 'Medidas', toInt(c['measured']), toInt(c['students'])),
                    const SizedBox(width: 16),
                    _mini(Icons.payments_outlined, 'Pagos', toInt(c['paid']), toInt(c['students'])),
                  ]),
                ]),
              ),
            ),
        ]);
      },
    );
  }

  Widget _mini(IconData icon, String label, int done, int total) => Expanded(
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Icon(icon, size: 14, color: Brand.muted),
            const SizedBox(width: 4),
            Text('$label $done/$total', style: const TextStyle(fontSize: 12.5, color: Brand.muted)),
          ]),
          const SizedBox(height: 4),
          ClipRRect(
            borderRadius: BorderRadius.circular(4),
            child: LinearProgressIndicator(
              value: total == 0 ? 0 : done / total,
              minHeight: 6,
              color: done == total && total > 0 ? Brand.ok : Brand.yellow,
              backgroundColor: Brand.panel2,
            ),
          ),
        ]),
      );
}

/// Contrato: etapa, anticipo, fotos y alumnos (con toma de medidas).
class ContractScreen extends StatefulWidget {
  const ContractScreen({super.key, required this.id});
  final int id;

  @override
  State<ContractScreen> createState() => _ContractScreenState();
}

class _ContractScreenState extends State<ContractScreen> {
  Json? _c;
  final _filter = TextEditingController();

  String get _base => '/admin/contratos/${widget.id}';

  @override
  void dispose() {
    _filter.dispose();
    super.dispose();
  }

  void _set(Json? c) {
    if (c != null) setState(() => _c = c);
  }

  Future<void> _reloadFromServer() async => _set(await run(context, () => Api.instance.get(_base)));

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(str(_c?['school'], 'Contrato'))),
      body: Loader<Json>(
        load: () async => _c = await Api.instance.get(_base),
        builder: (context, loaded, reload) {
          final c = _c ?? loaded;
          final students = (c['students'] as List).cast<Map<String, dynamic>>();
          final f = _filter.text.trim().toLowerCase();
          final shown = f.isEmpty
              ? students
              : students.where((s) => '${s['name']} ${s['document']} ${s['nickname'] ?? ''}'.toLowerCase().contains(f)).toList();
          final pending = students.where((s) => s['measured'] != true).toList();
          final dep = ((c['deposit'] as Map?) ?? {}).cast<String, dynamic>();
          final photos = (c['photos'] as List).cast<Map>();
          final stages = (c['stages'] as Map).cast<String, dynamic>();

          return ListView(padding: const EdgeInsets.all(16), children: [
            Text('${c['code']} · ${c['year']}', style: const TextStyle(color: Brand.muted)),
            const SizedBox(height: 10),
            Box(
              title: 'Etapa de producción',
              icon: Icons.content_cut,
              trailing: Text('Entrega ${shortDate(c['delivery_date'])}', style: const TextStyle(color: Brand.muted, fontSize: 12.5)),
              child: InkWell(
                onTap: () async {
                  final s = await pickOption(context, 'Cambiar etapa', stages.map((k, v) => MapEntry(k, '$v')), selected: '${c['stage']}');
                  if (s == null || s == c['stage'] || !context.mounted) return;
                  if (!await confirm(context, '¿Pasar a "${stages[s]}"?', body: 'Los estudiantes con avisos activos reciben un WhatsApp.')) return;
                  if (!context.mounted) return;
                  _set(await run(context, () => Api.instance.post('$_base/etapa', {'production_status': s}), ok: 'Etapa actualizada.'));
                },
                child: Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(color: Brand.yellow.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(12)),
                  child: Row(children: [
                    Expanded(child: Text(str(c['stage_label']), style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 18, color: Brand.yellow))),
                    const Icon(Icons.swap_vert, color: Brand.yellow),
                  ]),
                ),
              ),
            ),
            if (dep['status'] != null) _deposit(dep),
            Box(
              title: 'Fotos del avance (${photos.length})',
              icon: Icons.photo_camera_outlined,
              trailing: IconButton(icon: const Icon(Icons.add_a_photo, color: Brand.yellow), onPressed: _addPhotos),
              child: photos.isEmpty
                  ? const Text('Sube fotos del taller: el colegio y los estudiantes las ven en su app.', style: TextStyle(color: Brand.muted))
                  : SizedBox(
                      height: 92,
                      child: ListView.separated(
                        scrollDirection: Axis.horizontal,
                        itemCount: photos.length,
                        separatorBuilder: (_, __) => const SizedBox(width: 8),
                        itemBuilder: (_, i) => ClipRRect(
                          borderRadius: BorderRadius.circular(10),
                          child: Image.network('${photos[i]['url']}', width: 92, height: 92, fit: BoxFit.cover,
                              errorBuilder: (_, __, ___) => Container(width: 92, color: Brand.panel2)),
                        ),
                      ),
                    ),
            ),
            Box(
              title: 'Alumnos (${students.length})',
              icon: Icons.groups_outlined,
              trailing: IconButton(icon: const Icon(Icons.person_add_alt, color: Brand.yellow), onPressed: _addStudent),
              child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
                if (pending.isNotEmpty)
                  FilledButton.icon(
                    icon: const Icon(Icons.straighten),
                    label: Text('Tomar medidas · faltan ${pending.length}'),
                    onPressed: () async {
                      await Navigator.push(context, MaterialPageRoute(builder: (_) => MeasureWizard(contractId: widget.id, students: pending)));
                      await _reloadFromServer();
                    },
                  ),
                const SizedBox(height: 10),
                TextField(
                  controller: _filter,
                  onChanged: (_) => setState(() {}),
                  decoration: const InputDecoration(hintText: 'Buscar alumno', prefixIcon: Icon(Icons.search), isDense: true),
                ),
                const SizedBox(height: 6),
                for (final s in shown)
                  ListTile(
                    contentPadding: EdgeInsets.zero,
                    onTap: () async {
                      await Navigator.push(context, MaterialPageRoute(builder: (_) => MeasureWizard(contractId: widget.id, students: [s])));
                      await _reloadFromServer();
                    },
                    leading: Icon(s['measured'] == true ? Icons.check_circle : Icons.radio_button_unchecked,
                        color: s['measured'] == true ? Brand.ok : Brand.muted),
                    title: Text(str(s['name'])),
                    subtitle: Text([str(s['document']), if (s['size'] != null) 'Talla ${s['size']}', if (s['nickname'] != null) '"${s['nickname']}"'].join(' · ')),
                    trailing: Pill(paymentLabel(s['payment_status'] as String?), color: paymentColor(s['payment_status'] as String?)),
                  ),
              ]),
            ),
          ]);
        },
      ),
    );
  }

  Widget _deposit(Json dep) {
    final status = '${dep['status']}';
    return Box(
      title: 'Anticipo del colegio',
      icon: Icons.savings_outlined,
      color: status == 'en_revision' ? Brand.info : null,
      trailing: Pill(str(dep['label']), color: status == 'pagado' ? Brand.ok : (status == 'en_revision' ? Brand.info : Brand.yellow)),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text('Pedido ${money(dep['amount'])} · pagado ${money(dep['paid'])}'),
        if (status == 'en_revision') ...[
          const SizedBox(height: 10),
          if (dep['proof_url'] != null)
            OutlinedButton.icon(
              icon: const Icon(Icons.receipt_long),
              label: const Text('Ver comprobante'),
              onPressed: () => launchUrl(Uri.parse('${dep['proof_url']}'), mode: LaunchMode.externalApplication),
            ),
          const SizedBox(height: 8),
          Row(children: [
            Expanded(
              child: FilledButton(
                style: FilledButton.styleFrom(backgroundColor: Brand.ok),
                onPressed: () async {
                  final r = await run(context, () => Api.instance.post('$_base/anticipo', {'action': 'aprobar'}), ok: 'Anticipo aprobado.');
                  if (r != null) await _reloadFromServer();
                },
                child: const Text('Aprobar'),
              ),
            ),
            const SizedBox(width: 8),
            TextButton(
              onPressed: () async {
                final r = await run(context, () => Api.instance.post('$_base/anticipo', {'action': 'rechazar'}), ok: 'Comprobante rechazado.');
                if (r != null) await _reloadFromServer();
              },
              child: const Text('No corresponde', style: TextStyle(color: Brand.bad)),
            ),
          ]),
        ],
      ]),
    );
  }

  Future<void> _addPhotos() async {
    final source = await pickOption(context, 'Agregar fotos', {'camara': 'Tomar foto', 'galeria': 'Elegir de la galería'});
    if (source == null) return;
    final picker = ImagePicker();
    final List<XFile> files;
    if (source == 'camara') {
      final f = await picker.pickImage(source: ImageSource.camera, maxWidth: 2000, imageQuality: 82);
      files = f == null ? [] : [f];
    } else {
      files = await picker.pickMultiImage(maxWidth: 2000, imageQuality: 82);
    }
    if (files.isEmpty || !mounted) return;
    toast(context, 'Subiendo ${files.length} foto(s)…');
    _set(await run(
      context,
      () => Api.instance.upload('$_base/fotos', {'phase': 'proceso'}, {'photos[]': files.map((x) => File(x.path)).toList()}),
      ok: 'Fotos subidas.',
    ));
  }

  Future<void> _addStudent() async {
    final name = TextEditingController();
    final doc = TextEditingController();
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: Brand.panel,
        title: const Text('Agregar alumno'),
        content: Column(mainAxisSize: MainAxisSize.min, children: [
          TextField(controller: name, textCapitalization: TextCapitalization.words, decoration: const InputDecoration(labelText: 'Nombre completo')),
          const SizedBox(height: 10),
          TextField(controller: doc, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Documento')),
        ]),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancelar')),
          FilledButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Agregar')),
        ],
      ),
    );
    if (ok != true || !mounted) return;
    final r = await run(context, () => Api.instance.post('$_base/alumnos', {'name': name.text.trim(), 'document': doc.text.trim()}), ok: 'Alumno agregado.');
    if (r != null) await _reloadFromServer();
  }
}

/// Toma de medidas alumno por alumno. Funciona sin internet: lo guarda y lo envía después.
class MeasureWizard extends StatefulWidget {
  const MeasureWizard({super.key, required this.contractId, required this.students});
  final int contractId;
  final List<Map<String, dynamic>> students;

  @override
  State<MeasureWizard> createState() => _MeasureWizardState();
}

class _MeasureWizardState extends State<MeasureWizard> {
  static const sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
  static const numeric = {'chest': 'Pecho', 'waist': 'Cintura', 'hip': 'Cadera', 'sleeve_length': 'Manga', 'talle_length': 'Talle'};

  int _i = 0;
  String? _size;
  String? _gender;
  final Map<String, TextEditingController> _n = {for (final k in numeric.keys) k: TextEditingController()};
  final _nick = TextEditingController();
  bool _busy = false;
  int _savedOffline = 0;

  Map<String, dynamic> get _s => widget.students[_i];

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    for (final c in _n.values) {
      c.dispose();
    }
    _nick.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    final s = _s;
    final pending = await OfflineQueue.instance.pendingFor(toInt(s['id']));
    final src = {...s, ...?pending};
    String fmt(dynamic v) {
      if (v == null) return '';
      final d = double.tryParse('$v');
      if (d == null) return '';
      return d == d.roundToDouble() ? d.toInt().toString() : d.toString();
    }

    setState(() {
      _size = src['size'] as String?;
      _gender = src['gender'] as String?;
      for (final k in numeric.keys) {
        _n[k]!.text = fmt(src[k]);
      }
      _nick.text = str(src['nickname']);
    });
  }

  Future<void> _save({required bool next}) async {
    final data = <String, dynamic>{
      if (_size != null) 'size': _size,
      if (_gender != null) 'gender': _gender,
      if (_nick.text.trim().isNotEmpty) 'nickname': _nick.text.trim(),
      for (final k in numeric.keys)
        if (_n[k]!.text.trim().isNotEmpty) k: double.tryParse(_n[k]!.text.trim().replaceAll(',', '.')),
    }..removeWhere((k, v) => v == null);

    setState(() => _busy = true);
    try {
      await Api.instance.put('/admin/contratos/${widget.contractId}/alumnos/${_s['id']}/medidas', data);
      if (mounted) toast(context, '✓ ${_s['name']}');
    } on ApiException catch (e) {
      if (e.offline) {
        await OfflineQueue.instance.add(widget.contractId, toInt(_s['id']), data);
        _savedOffline++;
        if (mounted) toast(context, 'Sin internet: guardado en el teléfono. Se envía solo después.');
      } else {
        if (mounted) toast(context, e.message, error: true);
        setState(() => _busy = false);
        return;
      }
    }
    if (!mounted) return;
    setState(() => _busy = false);
    if (next && _i < widget.students.length - 1) {
      setState(() => _i++);
      await _load();
    } else {
      Navigator.pop(context);
    }
  }

  @override
  Widget build(BuildContext context) {
    final s = _s;
    final total = widget.students.length;
    return Scaffold(
      appBar: AppBar(
        title: Text(total > 1 ? 'Medidas ${_i + 1} de $total' : 'Medidas'),
        actions: [if (_savedOffline > 0) const Padding(padding: EdgeInsets.only(right: 12), child: Icon(Icons.cloud_off, color: Brand.orange))],
      ),
      body: ListView(padding: const EdgeInsets.all(16), children: [
        if (total > 1)
          ClipRRect(
            borderRadius: BorderRadius.circular(4),
            child: LinearProgressIndicator(value: (_i + 1) / total, minHeight: 5, color: Brand.yellow, backgroundColor: Brand.panel2),
          ),
        const SizedBox(height: 14),
        Text(str(s['name']), style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w900)),
        Text('Documento ${s['document']}', style: const TextStyle(color: Brand.muted)),
        const SizedBox(height: 18),
        const Text('Talla', style: TextStyle(fontWeight: FontWeight.w800)),
        const SizedBox(height: 8),
        Wrap(spacing: 8, runSpacing: 8, children: [
          for (final z in sizes)
            ChoiceChip(
              label: Text(z, style: const TextStyle(fontWeight: FontWeight.w800)),
              selected: _size == z,
              onSelected: (_) => setState(() => _size = _size == z ? null : z),
            ),
        ]),
        const SizedBox(height: 16),
        const Text('Sexo', style: TextStyle(fontWeight: FontWeight.w800)),
        const SizedBox(height: 8),
        Wrap(spacing: 8, children: [
          for (final g in {'mujer': 'Mujer', 'hombre': 'Hombre'}.entries)
            ChoiceChip(label: Text(g.value), selected: _gender == g.key, onSelected: (_) => setState(() => _gender = g.key)),
        ]),
        const SizedBox(height: 16),
        const Text('Medidas en cm', style: TextStyle(fontWeight: FontWeight.w800)),
        const SizedBox(height: 8),
        GridView.count(
          crossAxisCount: 2,
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          mainAxisSpacing: 10,
          crossAxisSpacing: 10,
          childAspectRatio: 2.6,
          children: [
            for (final e in numeric.entries)
              TextField(
                controller: _n[e.key],
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                textInputAction: TextInputAction.next,
                decoration: InputDecoration(labelText: e.value, suffixText: 'cm'),
              ),
          ],
        ),
        const SizedBox(height: 12),
        TextField(controller: _nick, decoration: const InputDecoration(labelText: 'Apodo para bordar')),
        const SizedBox(height: 22),
        Row(children: [
          if (total > 1 && _i < total - 1)
            Expanded(
              child: OutlinedButton(
                onPressed: _busy
                    ? null
                    : () async {
                        setState(() => _i++);
                        await _load();
                      },
                child: const Text('Saltar'),
              ),
            ),
          if (total > 1 && _i < total - 1) const SizedBox(width: 10),
          Expanded(
            flex: 2,
            child: FilledButton(
              onPressed: _busy ? null : () => _save(next: true),
              child: Text(total > 1 && _i < total - 1 ? 'Guardar y siguiente' : 'Guardar'),
            ),
          ),
        ]),
        const SizedBox(height: 30),
      ]),
    );
  }
}
