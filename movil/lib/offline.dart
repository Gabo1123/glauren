import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'api.dart';

/// Medidas tomadas sin internet: se guardan en el teléfono y se envían solas al volver la conexión.
class OfflineQueue extends ChangeNotifier {
  OfflineQueue._();
  static final OfflineQueue instance = OfflineQueue._();

  static const _key = 'pending_measures';
  bool _syncing = false;

  Future<List<Json>> _read() async {
    final p = await SharedPreferences.getInstance();
    final raw = p.getString(_key);
    if (raw == null) return [];
    try {
      return (jsonDecode(raw) as List).cast<Map<String, dynamic>>();
    } catch (_) {
      return [];
    }
  }

  Future<void> _write(List<Json> items) async {
    final p = await SharedPreferences.getInstance();
    await p.setString(_key, jsonEncode(items));
    notifyListeners();
  }

  Future<int> count() async => (await _read()).length;

  /// Medidas pendientes de un alumno (para mostrarlas aunque no se hayan enviado).
  Future<Json?> pendingFor(int studentId) async {
    for (final i in await _read()) {
      if (i['student'] == studentId) return (i['data'] as Map).cast<String, dynamic>();
    }
    return null;
  }

  /// Guarda o reemplaza las medidas de un alumno.
  Future<void> add(int contractId, int studentId, Json data) async {
    final items = await _read();
    items.removeWhere((i) => i['student'] == studentId);
    items.add({'contract': contractId, 'student': studentId, 'data': data});
    await _write(items);
  }

  /// Envía lo pendiente. Devuelve cuántas medidas quedaron enviadas.
  Future<int> sync() async {
    if (_syncing) return 0;
    _syncing = true;
    var sent = 0;
    try {
      final items = await _read();
      final left = <Json>[];
      for (final i in items) {
        try {
          await Api.instance.put('/admin/contratos/${i['contract']}/alumnos/${i['student']}/medidas', (i['data'] as Map).cast<String, dynamic>());
          sent++;
        } on ApiException catch (e) {
          // Sin internet: se queda para después. Si el servidor la rechaza (dato inválido), se descarta.
          if (e.offline || e.status == 401 || e.status >= 500) left.add(i);
        }
      }
      await _write(left);
    } finally {
      _syncing = false;
    }
    return sent;
  }
}
