import 'package:flutter/material.dart';

import 'api.dart';
import 'offline.dart';
import 'screens/admin/admin_home.dart';
import 'screens/login.dart';
import 'screens/school/school_home.dart';
import 'screens/student/student_home.dart';
import 'theme.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const GlaurenApp());
}

class GlaurenApp extends StatelessWidget {
  const GlaurenApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: "G'Lauren",
      debugShowCheckedModeBanner: false,
      theme: buildTheme(),
      home: const Splash(),
    );
  }
}

/// Pantalla inicial: recupera la sesión guardada y lleva a cada quien a su inicio.
class Splash extends StatefulWidget {
  const Splash({super.key});

  @override
  State<Splash> createState() => _SplashState();
}

class _SplashState extends State<Splash> {
  @override
  void initState() {
    super.initState();
    _start();
  }

  Future<void> _start() async {
    final s = await Api.instance.restore();
    if (!mounted) return;
    if (s != null && s.role == 'admin') OfflineQueue.instance.sync();
    openHome(context, s?.role);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          Image.asset('assets/icon.png', width: 110),
          const SizedBox(height: 18),
          const CircularProgressIndicator(color: Brand.yellow),
        ]),
      ),
    );
  }
}

/// Abre el inicio según el perfil (o el login si no hay sesión).
void openHome(BuildContext context, String? role) {
  final Widget page = switch (role) {
    'admin' => const AdminHome(),
    'colegio' => const SchoolHome(),
    'estudiante' => const StudentHome(),
    _ => const LoginScreen(),
  };
  Navigator.of(context).pushAndRemoveUntil(
    MaterialPageRoute(builder: (_) => page),
    (_) => false,
  );
}

Future<void> logout(BuildContext context) async {
  await Api.instance.logout();
  if (context.mounted) openHome(context, null);
}
