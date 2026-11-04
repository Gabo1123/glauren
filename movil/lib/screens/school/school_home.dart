import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../api.dart';
import '../../format.dart';
import '../../main.dart';
import '../../theme.dart';
import '../../widgets/common.dart';

/// Inicio del colegio: su(s) contrato(s).
class SchoolHome extends StatelessWidget {
  const SchoolHome({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("G'Lauren"),
        actions: [IconButton(icon: const Icon(Icons.logout), onPressed: () => logout(context))],
      ),
      body: Loader<Json>(
        load: () => Api.instance.get('/colegio/contratos'),
        builder: (context, d, reload) {
          final list = (d['data'] as List).cast<Map<String, dynamic>>();
          if (list.length == 1) return SchoolContract(id: toInt(list.first['id']), embedded: true);
          return ListView(padding: const EdgeInsets.all(16), children: [
            Text(str(d['school']), style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w900)),
            const SizedBox(height: 12),
            if (list.isEmpty) const Text('Aún no tienes contratos.', style: TextStyle(color: Brand.muted)),
            for (final c in list)
              Box(
                child: ListTile(
                  contentPadding: EdgeInsets.zero,
                  title: Text('Promoción ${c['year']}', style: const TextStyle(fontWeight: FontWeight.w800)),
                  subtitle: Text('${c['stage_label']} · ${c['paid']}/${c['students']} pagos'),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => Scaffold(appBar: AppBar(), body: SchoolContract(id: toInt(c['id']))))),
                ),
              ),
          ]);
        },
      ),
    );
  }
}

class SchoolContract extends StatefulWidget {
  const SchoolContract({super.key, required this.id, this.embedded = false});
  final int id;
  final bool embedded;

  @override
  State<SchoolContract> createState() => _SchoolContractState();
}

class _SchoolContractState extends State<SchoolContract> {
  Json? _c;

  String get _base => '/colegio/contratos/${widget.id}';

  void _set(Json? c) {
    if (c != null) setState(() => _c = c);
  }

  @override
  Widget build(BuildContext context) {
    return Loader<Json>(
      load: () async => _c = await Api.instance.get(_base),
      builder: (context, loaded, reload) {
        final c = _c ?? loaded;
        final pay = (c['payments'] as Map).cast<String, dynamic>();
        final dep = (c['deposit'] as Map).cast<String, dynamic>();
        final students = (c['students'] as List).cast<Map<String, dynamic>>();
        final photos = (c['photos'] as List).cast<Map>();
        return ListView(padding: const EdgeInsets.all(16), children: [
          Text('Promoción ${c['year']}', style: const TextStyle(fontSize: 26, fontWeight: FontWeight.w900)),
          Text('Contrato ${c['code']} · entrega estimada ${longDate(c['delivery_date'])}', style: const TextStyle(color: Brand.muted)),
          const SizedBox(height: 14),
          if (c['sign_url'] != null)
            Box(
              color: Brand.yellow,
              child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
                const Text('✍️ Tu contrato está listo para firmar', style: TextStyle(fontWeight: FontWeight.w800)),
                const SizedBox(height: 8),
                FilledButton(onPressed: () => launchUrl(Uri.parse('${c['sign_url']}'), mode: LaunchMode.externalApplication), child: const Text('Ver y firmar')),
              ]),
            ),
          if (dep['status'] != null && dep['status'] != 'pagado') _deposit(dep),
          Box(
            title: 'Pagos del curso',
            icon: Icons.payments_outlined,
            child: Row(children: [
              Expanded(child: _num('Pagaron', pay['paid'], Brand.ok)),
              Expanded(child: _num('En revisión', pay['review'], Brand.info)),
              Expanded(child: _num('Pendientes', pay['pending'], Brand.yellow)),
            ]),
          ),
          Box(title: '¿En qué va?', icon: Icons.timeline, child: StageTimeline(stages: (c['stages'] as Map).cast<String, dynamic>(), current: c['stage'] as String?)),
          if (photos.isNotEmpty)
            Box(
              title: 'Fotos del avance',
              icon: Icons.photo_library_outlined,
              child: SizedBox(
                height: 110,
                child: ListView.separated(
                  scrollDirection: Axis.horizontal,
                  itemCount: photos.length,
                  separatorBuilder: (_, __) => const SizedBox(width: 8),
                  itemBuilder: (_, i) => ClipRRect(
                    borderRadius: BorderRadius.circular(10),
                    child: Image.network('${photos[i]['url']}', width: 110, height: 110, fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => Container(width: 110, color: Brand.panel2)),
                  ),
                ),
              ),
            ),
          Box(
            title: 'Alumnos (${students.length})',
            icon: Icons.groups_outlined,
            trailing: c['signed'] == true ? null : IconButton(icon: const Icon(Icons.person_add_alt, color: Brand.yellow), onPressed: _addStudent),
            child: Column(children: [
              if (c['signed'] == true)
                const Padding(
                  padding: EdgeInsets.only(bottom: 8),
                  child: Text('La lista se cerró al firmar el contrato.', style: TextStyle(color: Brand.muted, fontSize: 13)),
                ),
              for (final s in students)
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  dense: true,
                  leading: Icon(s['measured'] == true ? Icons.straighten : Icons.straighten_outlined, color: s['measured'] == true ? Brand.ok : Brand.muted),
                  title: Text(str(s['name'])),
                  subtitle: Text(str(s['nickname'], str(s['document']))),
                  trailing: Pill(paymentLabel(s['payment_status'] as String?), color: paymentColor(s['payment_status'] as String?)),
                ),
            ]),
          ),
        ]);
      },
    );
  }

  Widget _num(String label, dynamic v, Color color) => Column(children: [
        Text('${v ?? 0}', style: TextStyle(fontSize: 26, fontWeight: FontWeight.w900, color: color)),
        Text(label, style: const TextStyle(color: Brand.muted, fontSize: 12)),
      ]);

  Widget _deposit(Json dep) {
    final review = dep['status'] == 'en_revision';
    return Box(
      title: review ? 'Recibimos tu comprobante' : 'Anticipo para iniciar la producción',
      icon: Icons.savings_outlined,
      color: review ? Brand.info : Brand.yellow,
      child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
        if (review)
          const Text('Lo estamos revisando. Te confirmamos apenas quede aprobado.', style: TextStyle(color: Brand.muted))
        else ...[
          Text(money(dep['due']), style: const TextStyle(fontSize: 30, fontWeight: FontWeight.w900)),
          if (str(dep['account']).isNotEmpty) Text('Consigna o transfiere a: ${dep['account']}'),
          const SizedBox(height: 10),
          FilledButton.icon(icon: const Icon(Icons.upload), label: const Text('Subir comprobante'), onPressed: _uploadDeposit),
        ],
      ]),
    );
  }

  Future<void> _uploadDeposit() async {
    final source = await pickOption(context, 'Comprobante', {'camara': 'Tomar foto', 'galeria': 'Elegir de la galería'});
    if (source == null) return;
    final f = await ImagePicker().pickImage(source: source == 'camara' ? ImageSource.camera : ImageSource.gallery, maxWidth: 2000, imageQuality: 85);
    if (f == null || !mounted) return;
    _set(await run(context, () => Api.instance.upload('$_base/anticipo', {}, {'proof': [File(f.path)]}), ok: '¡Gracias! Recibimos el comprobante.'));
  }

  Future<void> _addStudent() async {
    final name = TextEditingController();
    final doc = TextEditingController();
    final phone = TextEditingController();
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: Brand.panel,
        title: const Text('Agregar alumno'),
        content: SingleChildScrollView(
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            TextField(controller: name, textCapitalization: TextCapitalization.words, decoration: const InputDecoration(labelText: 'Nombre completo *')),
            const SizedBox(height: 10),
            TextField(controller: doc, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Documento (T.I. / C.C.) *')),
            const SizedBox(height: 10),
            TextField(controller: phone, keyboardType: TextInputType.phone, decoration: const InputDecoration(labelText: 'Celular (opcional)')),
          ]),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancelar')),
          FilledButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Agregar')),
        ],
      ),
    );
    if (ok != true || !mounted) return;
    _set(await run(context, () => Api.instance.post('$_base/alumnos', {
          'name': name.text.trim(),
          'document': doc.text.trim(),
          if (phone.text.trim().isNotEmpty) 'phone': phone.text.trim(),
        }), ok: 'Alumno agregado.'));
  }
}
