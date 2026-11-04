import 'package:flutter/material.dart';

import '../api.dart';
import '../theme.dart';

/// Tarjeta con título.
class Box extends StatelessWidget {
  const Box({super.key, this.title, this.icon, required this.child, this.trailing, this.color, this.padding});
  final String? title;
  final IconData? icon;
  final Widget child;
  final Widget? trailing;
  final Color? color;
  final EdgeInsets? padding;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      padding: padding ?? const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Brand.panel,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: color ?? const Color(0x22FFFFFF)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (title != null)
            Padding(
              padding: const EdgeInsets.only(bottom: 10),
              child: Row(children: [
                if (icon != null) Icon(icon, size: 18, color: Brand.yellow),
                if (icon != null) const SizedBox(width: 8),
                Expanded(
                  child: Text(title!, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15)),
                ),
                if (trailing != null) trailing!,
              ]),
            ),
          child,
        ],
      ),
    );
  }
}

/// Píldora de estado.
class Pill extends StatelessWidget {
  const Pill(this.text, {super.key, this.color = Brand.yellow});
  final String text;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.16),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withValues(alpha: 0.5)),
      ),
      child: Text(text, style: TextStyle(color: color, fontSize: 12, fontWeight: FontWeight.w700)),
    );
  }
}

/// Número grande con etiqueta.
class Stat extends StatelessWidget {
  const Stat(this.label, this.value, {super.key, this.color, this.onTap});
  final String label;
  final String value;
  final Color? color;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(14),
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(color: Brand.panel, borderRadius: BorderRadius.circular(14)),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(value, style: TextStyle(fontSize: 26, fontWeight: FontWeight.w900, color: color ?? Brand.paper)),
          const SizedBox(height: 2),
          Text(label, style: const TextStyle(color: Brand.muted, fontSize: 12.5)),
        ]),
      ),
    );
  }
}

/// Línea de tiempo de producción.
class StageTimeline extends StatelessWidget {
  const StageTimeline({super.key, required this.stages, required this.current});
  final Map<String, dynamic> stages;
  final String? current;

  @override
  Widget build(BuildContext context) {
    final keys = stages.keys.toList();
    final idx = current == null ? -1 : keys.indexOf(current!);
    return Column(
      children: [
        for (var i = 0; i < keys.length; i++)
          Row(children: [
            Column(children: [
              Container(
                width: 26,
                height: 26,
                alignment: Alignment.center,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: i < idx ? Brand.ok : (i == idx ? Brand.yellow : Brand.panel2),
                ),
                child: i < idx
                    ? const Icon(Icons.check, size: 16, color: Brand.ink)
                    : Text('${i + 1}', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: i == idx ? Brand.ink : Brand.muted)),
              ),
              if (i < keys.length - 1) Container(width: 2, height: 18, color: i < idx ? Brand.ok : Brand.panel2),
            ]),
            const SizedBox(width: 12),
            Expanded(
              child: Padding(
                padding: EdgeInsets.only(bottom: i < keys.length - 1 ? 18 : 0),
                child: Text(
                  '${stages[keys[i]]}',
                  style: TextStyle(
                    fontWeight: i == idx ? FontWeight.w800 : FontWeight.w500,
                    color: i == idx ? Brand.yellow : (i < idx ? Brand.paper : Brand.muted),
                  ),
                ),
              ),
            ),
          ]),
      ],
    );
  }
}

/// Carga datos y muestra error con botón de reintentar.
class Loader<T> extends StatefulWidget {
  const Loader({super.key, required this.load, required this.builder});
  final Future<T> Function() load;
  final Widget Function(BuildContext context, T data, Future<void> Function() reload) builder;

  @override
  State<Loader<T>> createState() => _LoaderState<T>();
}

class _LoaderState<T> extends State<Loader<T>> {
  T? _data;
  String? _error;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _reload();
  }

  Future<void> _reload() async {
    setState(() {
      _loading = _data == null;
      _error = null;
    });
    try {
      final d = await widget.load();
      if (!mounted) return;
      setState(() {
        _data = d;
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString();
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) return const Center(child: CircularProgressIndicator());
    if (_data == null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            const Icon(Icons.wifi_off, size: 40, color: Brand.muted),
            const SizedBox(height: 10),
            Text(_error ?? 'No se pudo cargar.', textAlign: TextAlign.center),
            const SizedBox(height: 14),
            FilledButton(onPressed: _reload, child: const Text('Reintentar')),
          ]),
        ),
      );
    }
    return RefreshIndicator(
      color: Brand.yellow,
      onRefresh: _reload,
      child: widget.builder(context, _data as T, _reload),
    );
  }
}

void toast(BuildContext context, String msg, {bool error = false}) {
  ScaffoldMessenger.of(context)
    ..hideCurrentSnackBar()
    ..showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: error ? Brand.bad : Brand.panel2,
    ));
}

/// Ejecuta una acción mostrando el error si falla. Devuelve el resultado o null.
Future<T?> run<T>(BuildContext context, Future<T> Function() action, {String? ok}) async {
  try {
    final r = await action();
    if (ok != null && context.mounted) toast(context, ok);
    return r;
  } on ApiException catch (e) {
    if (context.mounted) toast(context, e.message, error: true);
  } catch (e) {
    if (context.mounted) toast(context, 'Error: $e', error: true);
  }
  return null;
}

/// Hoja inferior para elegir una opción.
Future<String?> pickOption(BuildContext context, String title, Map<String, String> options, {String? selected}) {
  return showModalBottomSheet<String>(
    context: context,
    backgroundColor: Brand.panel,
    showDragHandle: true,
    isScrollControlled: true,
    builder: (ctx) => SafeArea(
      child: ConstrainedBox(
        constraints: BoxConstraints(maxHeight: MediaQuery.of(ctx).size.height * 0.7),
        child: ListView(shrinkWrap: true, children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 0, 20, 8),
            child: Text(title, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 17)),
          ),
          for (final e in options.entries)
            ListTile(
              title: Text(e.value),
              trailing: e.key == selected ? const Icon(Icons.check, color: Brand.yellow) : null,
              onTap: () => Navigator.pop(ctx, e.key),
            ),
        ]),
      ),
    ),
  );
}

/// Pregunta sí/no.
Future<bool> confirm(BuildContext context, String title, {String? body, String yes = 'Sí'}) async {
  final r = await showDialog<bool>(
    context: context,
    builder: (ctx) => AlertDialog(
      backgroundColor: Brand.panel,
      title: Text(title),
      content: body == null ? null : Text(body),
      actions: [
        TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancelar')),
        FilledButton(onPressed: () => Navigator.pop(ctx, true), child: Text(yes)),
      ],
    ),
  );
  return r ?? false;
}

Color paymentColor(String? status) => switch (status) {
      'pagado' => Brand.ok,
      'en_revision' => Brand.info,
      _ => Brand.yellow,
    };

String paymentLabel(String? status) => switch (status) {
      'pagado' => 'Pagado',
      'en_revision' => 'En revisión',
      _ => 'Pendiente',
    };
