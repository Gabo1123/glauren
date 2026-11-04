import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../api.dart';
import '../../format.dart';
import '../../main.dart';
import '../../theme.dart';
import '../../widgets/common.dart';

/// Inicio del estudiante: su chaqueta, su pago y las fotos.
class StudentHome extends StatefulWidget {
  const StudentHome({super.key});

  @override
  State<StudentHome> createState() => _StudentHomeState();
}

class _StudentHomeState extends State<StudentHome> {
  Json? _d;

  void _set(Json? d) {
    if (d != null) setState(() => _d = d);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Mi chaqueta'),
        actions: [IconButton(icon: const Icon(Icons.logout), onPressed: () => logout(context))],
      ),
      body: Loader<Json>(
        load: () async => _d = await Api.instance.get('/estudiante'),
        builder: (context, loaded, reload) {
          final d = _d ?? loaded;
          final pay = (d['payment'] as Map).cast<String, dynamic>();
          final photos = (d['photos'] as List).cast<Map>();
          return ListView(padding: const EdgeInsets.all(16), children: [
            Text('¡Hola, ${str(d['nickname'], str(d['name']).split(' ').first)}! 👋', style: const TextStyle(fontSize: 26, fontWeight: FontWeight.w900)),
            Text('${str(d['school'])} · Prom ${str(d['year'])}', style: const TextStyle(color: Brand.muted)),
            const SizedBox(height: 16),
            if (d['survey_url'] != null)
              Box(
                color: Brand.yellow,
                child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
                  const Text('⭐ ¿Cómo te fue con tu chaqueta?', style: TextStyle(fontWeight: FontWeight.w800)),
                  const SizedBox(height: 8),
                  FilledButton(onPressed: () => launchUrl(Uri.parse('${d['survey_url']}'), mode: LaunchMode.externalApplication), child: const Text('Calificar en 1 minuto')),
                ]),
              ),
            Box(
              title: 'Mi pedido',
              icon: Icons.checkroom,
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(str(d['combo'])),
                const SizedBox(height: 4),
                Text(d['measured'] == true ? 'Medidas tomadas ✓${d['size'] != null ? ' · talla ${d['size']}' : ''}' : 'Pendiente toma de medidas',
                    style: TextStyle(color: d['measured'] == true ? Brand.ok : Brand.muted)),
                if (d['delivery_date'] != null && d['stage'] != 'entregado') ...[
                  const SizedBox(height: 4),
                  Text('Entrega estimada: ${longDate(d['delivery_date'])}', style: const TextStyle(color: Brand.yellow)),
                ],
              ]),
            ),
            if (pay['school_pays'] != true) _payment(pay),
            if (d['stages'] != null)
              Box(title: '¿En qué va mi chaqueta?', icon: Icons.timeline, child: StageTimeline(stages: (d['stages'] as Map).cast<String, dynamic>(), current: d['stage'] as String?)),
            if (photos.isNotEmpty)
              Box(
                title: 'Fotos del taller',
                icon: Icons.photo_library_outlined,
                child: GridView.count(
                  crossAxisCount: 3,
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  mainAxisSpacing: 6,
                  crossAxisSpacing: 6,
                  children: [
                    for (final p in photos)
                      ClipRRect(
                        borderRadius: BorderRadius.circular(8),
                        child: Image.network('${p['url']}', fit: BoxFit.cover, errorBuilder: (_, __, ___) => Container(color: Brand.panel2)),
                      ),
                  ],
                ),
              ),
            TextButton.icon(
              icon: const Icon(Icons.public),
              label: const Text('Abrir mi portal web (muro y anuario)'),
              onPressed: () => launchUrl(Uri.parse('${d['web_url']}'), mode: LaunchMode.externalApplication),
            ),
          ]);
        },
      ),
    );
  }

  Widget _payment(Json pay) {
    final status = '${pay['status']}';
    final methods = (pay['methods'] as List).cast<Map>();
    return Box(
      title: 'Mi pago',
      icon: Icons.payments_outlined,
      color: paymentColor(status),
      trailing: Pill(paymentLabel(status), color: paymentColor(status)),
      child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
        Text(money(pay['amount']), style: const TextStyle(fontSize: 28, fontWeight: FontWeight.w900)),
        if (status == 'pagado') const Text('¡Gracias! Tu pago está aprobado.', style: TextStyle(color: Brand.ok)),
        if (status == 'en_revision') const Text('Recibimos tu comprobante. Te confirmamos pronto.', style: TextStyle(color: Brand.info)),
        if (status != 'pagado' && status != 'en_revision') ...[
          const SizedBox(height: 6),
          for (final m in methods)
            if (m['account'] != null && '${m['account']}'.isNotEmpty) Text('${m['label']}: ${m['account']}', style: const TextStyle(color: Brand.muted)),
          const SizedBox(height: 10),
          FilledButton.icon(icon: const Icon(Icons.upload), label: const Text('Ya pagué: subir comprobante'), onPressed: () => _pay(methods)),
        ],
      ]),
    );
  }

  Future<void> _pay(List<Map> methods) async {
    if (methods.isEmpty) {
      toast(context, 'Por ahora el pago se coordina con tu colegio.');
      return;
    }
    final method = methods.length == 1
        ? '${methods.first['key']}'
        : await pickOption(context, '¿Cómo pagaste?', {for (final m in methods) '${m['key']}': '${m['label']}'});
    if (method == null || !mounted) return;
    File? file;
    if (method != 'efectivo') {
      final source = await pickOption(context, 'Foto del comprobante', {'camara': 'Tomar foto', 'galeria': 'Elegir de la galería'});
      if (source == null) return;
      final f = await ImagePicker().pickImage(source: source == 'camara' ? ImageSource.camera : ImageSource.gallery, maxWidth: 2000, imageQuality: 85);
      if (f == null) return;
      file = File(f.path);
    }
    if (!mounted) return;
    final File? proof = file;
    _set(await run(
      context,
      () => Api.instance.upload('/estudiante/pago', {'method': method}, {if (proof != null) 'proof': [proof]}),
      ok: 'Comprobante enviado.',
    ));
  }
}
