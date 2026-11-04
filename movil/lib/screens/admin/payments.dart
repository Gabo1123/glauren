import 'package:flutter/material.dart';

import '../../api.dart';
import '../../format.dart';
import '../../theme.dart';
import '../../widgets/common.dart';
import 'admin_home.dart';
import 'contracts.dart';

/// Comprobantes de estudiantes y anticipos por aprobar.
class PaymentsTab extends StatelessWidget {
  const PaymentsTab({super.key});

  @override
  Widget build(BuildContext context) {
    return Loader<Json>(
      load: () => Api.instance.get('/admin/pagos'),
      builder: (context, d, reload) {
        final students = (d['students'] as List).cast<Map<String, dynamic>>();
        final deposits = (d['deposits'] as List).cast<Map<String, dynamic>>();
        return ListView(padding: const EdgeInsets.all(16), children: [
          Row(children: [
            const Expanded(child: Text('Pagos por aprobar', style: TextStyle(fontSize: 24, fontWeight: FontWeight.w900))),
            TextButton.icon(
              icon: const Icon(Icons.account_balance_wallet_outlined),
              label: const Text('Cartera'),
              onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const CarteraScreen())),
            ),
          ]),
          const SizedBox(height: 12),
          if (students.isEmpty && deposits.isEmpty)
            const Box(child: Text('🎉 No hay comprobantes pendientes.', style: TextStyle(color: Brand.muted))),
          for (final c in deposits)
            Box(
              title: 'Anticipo · ${c['school']}',
              icon: Icons.savings_outlined,
              color: Brand.info,
              child: Row(children: [
                Expanded(child: Text('${money(c['due'])} · ${ago(c['declared_at'])}')),
                FilledButton(
                  onPressed: () async {
                    await Navigator.push(context, MaterialPageRoute(builder: (_) => ContractScreen(id: toInt(c['contract_id']))));
                    await reload();
                  },
                  child: const Text('Revisar'),
                ),
              ]),
            ),
          for (final s in students) _StudentProof(s, onDone: reload),
        ]);
      },
    );
  }
}

class _StudentProof extends StatelessWidget {
  const _StudentProof(this.s, {required this.onDone});
  final Map<String, dynamic> s;
  final Future<void> Function() onDone;

  @override
  Widget build(BuildContext context) {
    final proof = s['payment_proof_url'] as String?;
    return Box(
      title: str(s['name']),
      icon: Icons.person_outline,
      trailing: Text(money(s['amount_due']), style: const TextStyle(fontWeight: FontWeight.w900, color: Brand.yellow)),
      child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
        Text('${s['school'] ?? ''} · ${str(s['payment_method'], 'sin método')}', style: const TextStyle(color: Brand.muted)),
        if (proof != null) ...[
          const SizedBox(height: 10),
          GestureDetector(
            onTap: () => showDialog(
              context: context,
              builder: (_) => Dialog(backgroundColor: Brand.ink, child: InteractiveViewer(child: Image.network(proof))),
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(10),
              child: Image.network(proof, height: 180, fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) => Container(height: 60, color: Brand.panel2, alignment: Alignment.center, child: const Text('No se pudo cargar la imagen'))),
            ),
          ),
        ],
        const SizedBox(height: 10),
        Row(children: [
          Expanded(
            child: FilledButton(
              style: FilledButton.styleFrom(backgroundColor: Brand.ok),
              onPressed: () async {
                final r = await run(context, () => Api.instance.post('/admin/pagos/${s['id']}/aprobar'), ok: 'Pago aprobado.');
                if (r != null) await onDone();
              },
              child: const Text('Aprobar'),
            ),
          ),
          const SizedBox(width: 8),
          TextButton(
            onPressed: () async {
              if (!await confirm(context, '¿Rechazar el comprobante?', body: 'El estudiante podrá volver a subirlo.')) return;
              if (!context.mounted) return;
              final r = await run(context, () => Api.instance.post('/admin/pagos/${s['id']}/rechazar'), ok: 'Rechazado.');
              if (r != null) await onDone();
            },
            child: const Text('Rechazar', style: TextStyle(color: Brand.bad)),
          ),
        ]),
      ]),
    );
  }
}
