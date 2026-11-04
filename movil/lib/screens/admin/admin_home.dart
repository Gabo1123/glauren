import 'package:flutter/material.dart';

import '../../api.dart';
import '../../format.dart';
import '../../main.dart';
import '../../offline.dart';
import '../../theme.dart';
import '../../widgets/common.dart';
import 'contracts.dart';
import 'payments.dart';
import 'quotes.dart';

/// Inicio del equipo: Hoy · Clientes · Contratos · Pagos.
class AdminHome extends StatefulWidget {
  const AdminHome({super.key});

  @override
  State<AdminHome> createState() => _AdminHomeState();
}

class _AdminHomeState extends State<AdminHome> {
  int _tab = 0;

  @override
  Widget build(BuildContext context) {
    final pages = [
      TodayTab(onGo: (i) => setState(() => _tab = i)),
      const QuotesTab(),
      const ContractsTab(),
      const PaymentsTab(),
    ];
    return Scaffold(
      body: SafeArea(child: IndexedStack(index: _tab, children: pages)),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _tab,
        onDestinationSelected: (i) => setState(() => _tab = i),
        destinations: const [
          NavigationDestination(icon: Icon(Icons.wb_sunny_outlined), selectedIcon: Icon(Icons.wb_sunny, color: Brand.ink), label: 'Hoy'),
          NavigationDestination(icon: Icon(Icons.people_outline), selectedIcon: Icon(Icons.people, color: Brand.ink), label: 'Clientes'),
          NavigationDestination(icon: Icon(Icons.content_cut_outlined), selectedIcon: Icon(Icons.content_cut, color: Brand.ink), label: 'Contratos'),
          NavigationDestination(icon: Icon(Icons.payments_outlined), selectedIcon: Icon(Icons.payments, color: Brand.ink), label: 'Pagos'),
        ],
      ),
    );
  }
}

class TodayTab extends StatelessWidget {
  const TodayTab({super.key, required this.onGo});
  final void Function(int tab) onGo;

  @override
  Widget build(BuildContext context) {
    return Loader<Json>(
      load: () async {
        await OfflineQueue.instance.sync();
        return Api.instance.get('/admin/hoy');
      },
      builder: (context, d, reload) {
        final c = (d['counts'] as Map).cast<String, dynamic>();
        final call = (d['call_today'] as List).cast<Map<String, dynamic>>();
        final viewed = (d['viewed_no_reply'] as List).cast<Map<String, dynamic>>();
        return ListView(padding: const EdgeInsets.all(16), children: [
          Row(children: [
            Expanded(
              child: Text('Hola, ${Api.instance.session?.name.split(' ').first ?? ''} 👋',
                  style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w900)),
            ),
            PopupMenuButton<String>(
              icon: const Icon(Icons.more_vert),
              onSelected: (v) async {
                if (v == 'salir' && await confirm(context, '¿Cerrar sesión?')) {
                  if (context.mounted) logout(context);
                }
                if (v == 'cartera' && context.mounted) {
                  Navigator.push(context, MaterialPageRoute(builder: (_) => const CarteraScreen()));
                }
              },
              itemBuilder: (_) => const [
                PopupMenuItem(value: 'cartera', child: Text('Cartera')),
                PopupMenuItem(value: 'salir', child: Text('Cerrar sesión')),
              ],
            ),
          ]),
          const SizedBox(height: 4),
          Text(longDate(DateTime.now().toIso8601String()), style: const TextStyle(color: Brand.muted)),
          const SizedBox(height: 16),
          _PendingSync(onSynced: reload),
          GridView.count(
            crossAxisCount: 2,
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            mainAxisSpacing: 10,
            crossAxisSpacing: 10,
            childAspectRatio: 1.9,
            children: [
              Stat('Llamar hoy', '${c['call_today']}', color: Brand.yellow, onTap: () => onGo(1)),
              Stat('Vieron y no respondieron', '${c['viewed_no_reply']}', color: Brand.info, onTap: () => onGo(1)),
              Stat('Pagos por aprobar', '${toInt(c['payments_review']) + toInt(c['deposits_review'])}', color: Brand.orange, onTap: () => onGo(3)),
              Stat('En producción', '${c['in_production']}', onTap: () => onGo(2)),
            ],
          ),
          const SizedBox(height: 10),
          Box(
            child: Row(children: [
              const Icon(Icons.emoji_events_outlined, color: Brand.ok),
              const SizedBox(width: 10),
              Expanded(child: Text('Ganadas este mes: ${c['won_this_month']}')),
              Text(money(c['won_value_month']), style: const TextStyle(fontWeight: FontWeight.w900, color: Brand.ok)),
            ]),
          ),
          if (call.isNotEmpty) Box(title: 'Llamar hoy', icon: Icons.phone_in_talk_outlined, padding: const EdgeInsets.fromLTRB(16, 16, 16, 4), child: Column(children: [for (final q in call) QuoteTile(q, onBack: reload)])),
          if (viewed.isNotEmpty) Box(title: 'La vieron y no han respondido', icon: Icons.visibility_outlined, padding: const EdgeInsets.fromLTRB(16, 16, 16, 4), child: Column(children: [for (final q in viewed) QuoteTile(q, onBack: reload)])),
          if (call.isEmpty && viewed.isEmpty)
            const Box(child: Text('🎉 Hoy no tienes llamadas pendientes.', style: TextStyle(color: Brand.muted))),
        ]);
      },
    );
  }
}

class _PendingSync extends StatelessWidget {
  const _PendingSync({required this.onSynced});
  final Future<void> Function() onSynced;

  @override
  Widget build(BuildContext context) {
    return ListenableBuilder(
      listenable: OfflineQueue.instance,
      builder: (context, _) => FutureBuilder<int>(
        future: OfflineQueue.instance.count(),
        builder: (context, snap) {
          final n = snap.data ?? 0;
          if (n == 0) return const SizedBox.shrink();
          return Box(
            color: Brand.orange,
            child: Row(children: [
              const Icon(Icons.cloud_upload_outlined, color: Brand.orange),
              const SizedBox(width: 10),
              Expanded(child: Text('$n medida(s) guardadas sin internet.')),
              TextButton(
                onPressed: () async {
                  final sent = await OfflineQueue.instance.sync();
                  if (context.mounted) toast(context, sent > 0 ? 'Enviadas $sent medida(s).' : 'Aún sin conexión.');
                  await onSynced();
                },
                child: const Text('Enviar'),
              ),
            ]),
          );
        },
      ),
    );
  }
}

class CarteraScreen extends StatelessWidget {
  const CarteraScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Cartera')),
      body: Loader<Json>(
        load: () => Api.instance.get('/admin/cartera'),
        builder: (context, d, reload) {
          final t = (d['totals'] as Map).cast<String, dynamic>();
          final rows = (d['rows'] as List).cast<Map<String, dynamic>>();
          return ListView(padding: const EdgeInsets.all(16), children: [
            Row(children: [
              Expanded(child: Stat('Recaudado', money(t['paid']), color: Brand.ok)),
              const SizedBox(width: 10),
              Expanded(child: Stat('Por cobrar', money(t['balance']), color: Brand.yellow)),
            ]),
            const SizedBox(height: 14),
            for (final r in rows)
              Box(
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Row(children: [
                    Expanded(child: Text(str(r['school']), style: const TextStyle(fontWeight: FontWeight.w800))),
                    Text('${r['pct']}%', style: const TextStyle(color: Brand.muted)),
                  ]),
                  const SizedBox(height: 8),
                  ClipRRect(
                    borderRadius: BorderRadius.circular(6),
                    child: LinearProgressIndicator(value: toInt(r['pct']) / 100, minHeight: 7, color: Brand.ok, backgroundColor: Brand.panel2),
                  ),
                  const SizedBox(height: 8),
                  Text('Recaudado ${money(r['paid'])} de ${money(r['total'])} · saldo ${money(r['balance'])} · ${r['pending_students']} deben',
                      style: const TextStyle(color: Brand.muted, fontSize: 13)),
                ]),
              ),
          ]);
        },
      ),
    );
  }
}
