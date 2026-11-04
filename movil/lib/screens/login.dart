import 'dart:io';

import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../api.dart';
import '../main.dart';
import '../theme.dart';

/// Login único: equipo y colegios con correo; estudiantes con documento y PIN.
class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

enum _Mode { email, student }

class _LoginScreenState extends State<LoginScreen> {
  _Mode _mode = _Mode.email;
  bool _busy = false;
  String? _error;

  // Correo
  final _email = TextEditingController();
  final _pass = TextEditingController();
  bool _hidePass = true;
  String? _challenge; // 2FA
  String _phoneHint = '';
  final _code = TextEditingController();

  // Estudiante
  final _doc = TextEditingController();
  final _pin = TextEditingController();
  final _pin2 = TextEditingController();
  String _studentStep = 'doc'; // doc | pin | create
  String _studentName = '';

  String get _device => '${Platform.isIOS ? 'iPhone' : 'Android'} · app';

  @override
  void dispose() {
    for (final c in [_email, _pass, _code, _doc, _pin, _pin2]) {
      c.dispose();
    }
    super.dispose();
  }

  Future<void> _go(Future<void> Function() f) async {
    setState(() {
      _busy = true;
      _error = null;
    });
    try {
      await f();
    } on ApiException catch (e) {
      if (mounted) setState(() => _error = e.message);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  Future<void> _loginEmail() => _go(() async {
        if (_challenge != null) {
          final r = await Api.instance.post('/login/verificar', {'challenge': _challenge, 'code': _code.text.trim()});
          await _done(r);
          return;
        }
        final r = await Api.instance.post('/login', {
          'email': _email.text.trim(),
          'password': _pass.text,
          'device': _device,
        });
        if (r['needs_2fa'] == true) {
          setState(() {
            _challenge = r['challenge'] as String;
            _phoneHint = '${r['phone_hint'] ?? ''}';
          });
          return;
        }
        await _done(r);
      });

  Future<void> _loginStudent() => _go(() async {
        final body = <String, dynamic>{'document': _doc.text.trim(), 'device': _device};
        if (_studentStep != 'doc') body['pin'] = _pin.text.trim();
        if (_studentStep == 'create') body['pin2'] = _pin2.text.trim();
        final r = await Api.instance.post('/login/estudiante', body);
        if (r['needs_pin_create'] == true || r['needs_pin'] == true) {
          setState(() {
            _studentStep = r['needs_pin_create'] == true ? 'create' : 'pin';
            _studentName = '${r['name'] ?? ''}';
          });
          return;
        }
        await _done(r);
      });

  Future<void> _done(Json r) async {
    await Api.instance.saveSession(r);
    if (mounted) openHome(context, Api.instance.role);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 420),
              child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
                Center(child: Image.asset('assets/icon.png', width: 90)),
                const SizedBox(height: 16),
                const Text("G'LAUREN", textAlign: TextAlign.center, style: TextStyle(fontSize: 30, fontWeight: FontWeight.w900, letterSpacing: 2)),
                const Text('Chaquetas de prom', textAlign: TextAlign.center, style: TextStyle(color: Brand.muted)),
                const SizedBox(height: 26),
                SegmentedButton<_Mode>(
                  segments: const [
                    ButtonSegment(value: _Mode.email, icon: Icon(Icons.badge_outlined), label: Text('Equipo o colegio')),
                    ButtonSegment(value: _Mode.student, icon: Icon(Icons.school_outlined), label: Text('Estudiante')),
                  ],
                  selected: {_mode},
                  showSelectedIcon: false,
                  onSelectionChanged: (s) => setState(() {
                    _mode = s.first;
                    _error = null;
                  }),
                ),
                const SizedBox(height: 20),
                if (_mode == _Mode.email) ..._emailForm() else ..._studentForm(),
                if (_error != null) ...[
                  const SizedBox(height: 14),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(color: Brand.bad.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(10)),
                    child: Text(_error!, style: const TextStyle(color: Brand.bad)),
                  ),
                ],
              ]),
            ),
          ),
        ),
      ),
    );
  }

  List<Widget> _emailForm() {
    if (_challenge != null) {
      return [
        Text('Te enviamos un código por WhatsApp al $_phoneHint.', textAlign: TextAlign.center),
        const SizedBox(height: 14),
        TextField(
          controller: _code,
          keyboardType: TextInputType.number,
          maxLength: 6,
          textAlign: TextAlign.center,
          style: const TextStyle(fontSize: 26, letterSpacing: 8, fontWeight: FontWeight.w800),
          decoration: const InputDecoration(counterText: '', hintText: '••••••'),
          onSubmitted: (_) => _loginEmail(),
        ),
        const SizedBox(height: 14),
        FilledButton(onPressed: _busy ? null : _loginEmail, child: _busy ? const _Spin() : const Text('Verificar')),
        TextButton(onPressed: () => setState(() => _challenge = null), child: const Text('Volver')),
      ];
    }
    return [
      TextField(
        controller: _email,
        keyboardType: TextInputType.emailAddress,
        autocorrect: false,
        decoration: const InputDecoration(labelText: 'Correo', prefixIcon: Icon(Icons.mail_outline)),
      ),
      const SizedBox(height: 12),
      TextField(
        controller: _pass,
        obscureText: _hidePass,
        decoration: InputDecoration(
          labelText: 'Contraseña',
          prefixIcon: const Icon(Icons.lock_outline),
          suffixIcon: IconButton(
            icon: Icon(_hidePass ? Icons.visibility_outlined : Icons.visibility_off_outlined),
            onPressed: () => setState(() => _hidePass = !_hidePass),
          ),
        ),
        onSubmitted: (_) => _loginEmail(),
      ),
      const SizedBox(height: 18),
      FilledButton(onPressed: _busy ? null : _loginEmail, child: _busy ? const _Spin() : const Text('Entrar')),
      const SizedBox(height: 10),
      const Text('Los colegios entran con el correo y la contraseña de su portal.', textAlign: TextAlign.center, style: TextStyle(color: Brand.muted, fontSize: 12.5)),
    ];
  }

  List<Widget> _studentForm() {
    return [
      if (_studentStep == 'doc') ...[
        TextField(
          controller: _doc,
          keyboardType: TextInputType.number,
          decoration: const InputDecoration(labelText: 'Número de documento (T.I. o C.C.)', prefixIcon: Icon(Icons.badge_outlined)),
          onSubmitted: (_) => _loginStudent(),
        ),
      ] else ...[
        Text('¡Hola, $_studentName!', textAlign: TextAlign.center, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800)),
        const SizedBox(height: 6),
        Text(_studentStep == 'create' ? 'Crea un PIN de 4 números para entrar siempre.' : 'Escribe tu PIN de 4 números.',
            textAlign: TextAlign.center, style: const TextStyle(color: Brand.muted)),
        const SizedBox(height: 14),
        _pinField(_pin, 'PIN'),
        if (_studentStep == 'create') ...[const SizedBox(height: 12), _pinField(_pin2, 'Repite el PIN')],
      ],
      const SizedBox(height: 18),
      FilledButton(onPressed: _busy ? null : _loginStudent, child: _busy ? const _Spin() : Text(_studentStep == 'doc' ? 'Continuar' : 'Entrar')),
      if (_studentStep != 'doc') ...[
        TextButton(
          onPressed: () => setState(() {
            _studentStep = 'doc';
            _pin.clear();
            _pin2.clear();
          }),
          child: const Text('Cambiar documento'),
        ),
        TextButton(
          onPressed: () => launchUrl(Uri.parse('https://chaquetasdeprom.com/estudiante/olvide-pin'), mode: LaunchMode.externalApplication),
          child: const Text('Olvidé mi PIN'),
        ),
      ],
    ];
  }

  Widget _pinField(TextEditingController c, String label) => TextField(
        controller: c,
        keyboardType: TextInputType.number,
        obscureText: true,
        maxLength: 4,
        textAlign: TextAlign.center,
        style: const TextStyle(fontSize: 24, letterSpacing: 10, fontWeight: FontWeight.w800),
        decoration: InputDecoration(labelText: label, counterText: ''),
        onSubmitted: (_) => _loginStudent(),
      );
}

class _Spin extends StatelessWidget {
  const _Spin();

  @override
  Widget build(BuildContext context) =>
      const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.5, color: Brand.ink));
}

