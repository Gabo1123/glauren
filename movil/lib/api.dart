import 'dart:convert';
import 'dart:io';

import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;

/// Dirección del servidor. Se puede cambiar al compilar:
/// flutter build ... --dart-define=API_URL=https://otro.dominio/api/v1
const String kApiUrl = String.fromEnvironment(
  'API_URL',
  defaultValue: 'https://chaquetasdeprom.com/api/v1',
);

typedef Json = Map<String, dynamic>;

class ApiException implements Exception {
  ApiException(this.message, {this.status = 0});
  final String message;
  final int status;

  /// No hubo conexión (sirve para guardar cambios y enviarlos después).
  bool get offline => status == 0;

  @override
  String toString() => message;
}

/// Perfil de quien inició sesión.
class Session {
  Session({required this.role, required this.name, this.school});
  final String role; // admin | colegio | estudiante
  final String name;
  final String? school;

  factory Session.fromJson(Json j) => Session(
        role: (j['role'] ?? '').toString(),
        name: (j['name'] ?? '').toString(),
        school: j['school']?.toString(),
      );
}

/// Cliente HTTP de la app. Guarda el token en el llavero del teléfono.
class Api {
  Api._();
  static final Api instance = Api._();

  final _storage = const FlutterSecureStorage();
  String? _token;
  String _role = '';
  Session? session;

  String get role => _role;

  Future<Session?> restore() async {
    try {
      _token = await _storage.read(key: 'token');
      _role = await _storage.read(key: 'role') ?? '';
    } catch (_) {
      _token = null;
    }
    if (_token == null || _role.isEmpty) return null;
    try {
      final r = await get('/$_role/yo');
      session = Session.fromJson(r['user'] as Json);
      return session;
    } on ApiException catch (e) {
      if (e.status == 401) await _clear();
      if (e.offline && _role.isNotEmpty) {
        // Sin internet: entra igual con la sesión guardada
        session = Session(role: _role, name: '');
        return session;
      }
      return null;
    }
  }

  Future<void> saveSession(Json r) async {
    _token = r['token'] as String;
    session = Session.fromJson(r['user'] as Json);
    _role = session!.role;
    await _storage.write(key: 'token', value: _token);
    await _storage.write(key: 'role', value: _role);
  }

  Future<void> logout() async {
    try {
      await post('/$_role/salir');
    } catch (_) {}
    await _clear();
  }

  Future<void> _clear() async {
    _token = null;
    _role = '';
    session = null;
    try {
      await _storage.deleteAll();
    } catch (_) {}
  }

  Map<String, String> get _headers => {
        'Accept': 'application/json',
        if (_token != null) 'Authorization': 'Bearer $_token',
      };

  Uri _uri(String path, [Map<String, String>? query]) =>
      Uri.parse('$kApiUrl$path').replace(queryParameters: query);

  Future<Json> get(String path, {Map<String, String>? query}) =>
      _send(() => http.get(_uri(path, query), headers: _headers));

  Future<Json> post(String path, [Json body = const {}]) => _send(() => http.post(
        _uri(path),
        headers: {..._headers, 'Content-Type': 'application/json'},
        body: jsonEncode(body),
      ));

  Future<Json> put(String path, Json body) => _send(() => http.put(
        _uri(path),
        headers: {..._headers, 'Content-Type': 'application/json'},
        body: jsonEncode(body),
      ));

  /// Envía archivos (fotos y comprobantes). [files] = {'campo': File} o {'campo[]': [File, ...]}.
  Future<Json> upload(String path, Map<String, String> fields, Map<String, List<File>> files) =>
      _send(() async {
        final req = http.MultipartRequest('POST', _uri(path))
          ..headers.addAll(_headers)
          ..fields.addAll(fields);
        for (final entry in files.entries) {
          for (final f in entry.value) {
            req.files.add(await http.MultipartFile.fromPath(entry.key, f.path));
          }
        }
        return http.Response.fromStream(await req.send());
      });

  Future<Json> _send(Future<http.Response> Function() run) async {
    http.Response res;
    try {
      res = await run().timeout(const Duration(seconds: 40));
    } on SocketException {
      throw ApiException('Sin conexión a internet.');
    } on HttpException {
      throw ApiException('Sin conexión a internet.');
    } on Exception {
      throw ApiException('No pudimos conectar con el servidor. Revisa tu internet.');
    }

    dynamic data;
    try {
      data = res.body.isEmpty ? <String, dynamic>{} : jsonDecode(res.body);
    } catch (_) {
      data = null;
    }

    if (res.statusCode >= 200 && res.statusCode < 300) {
      if (data is Map<String, dynamic>) return data;
      if (data is List) return {'data': data};
      return <String, dynamic>{};
    }

    var msg = 'Algo salió mal (${res.statusCode}).';
    if (data is Map<String, dynamic>) {
      final errors = data['errors'];
      if (errors is Map && errors.isNotEmpty) {
        final first = errors.values.first;
        msg = first is List && first.isNotEmpty ? first.first.toString() : first.toString();
      } else if (data['message'] != null && data['message'].toString().isNotEmpty) {
        msg = data['message'].toString();
      }
    }
    if (res.statusCode == 401) await _clear();
    throw ApiException(msg, status: res.statusCode);
  }
}
