import 'package:flutter/material.dart';

/// Colores de la marca G'Lauren.
class Brand {
  static const yellow = Color(0xFFFFC72C);
  static const orange = Color(0xFFFF6B1A);
  static const ink = Color(0xFF0E0E0E);
  static const panel = Color(0xFF1A1A20);
  static const panel2 = Color(0xFF24242C);
  static const paper = Color(0xFFF4EFE3);
  static const muted = Color(0xFF9A9AA7);
  static const ok = Color(0xFF25D366);
  static const bad = Color(0xFFFF6B6B);
  static const info = Color(0xFF4EA1FF);
}

ThemeData buildTheme() {
  final scheme = ColorScheme.fromSeed(
    seedColor: Brand.yellow,
    brightness: Brightness.dark,
  ).copyWith(
    primary: Brand.yellow,
    onPrimary: Brand.ink,
    secondary: Brand.orange,
    surface: Brand.ink,
    onSurface: Brand.paper,
    error: Brand.bad,
  );
  return ThemeData(
    useMaterial3: true,
    colorScheme: scheme,
    scaffoldBackgroundColor: Brand.ink,
    appBarTheme: const AppBarTheme(
      backgroundColor: Brand.ink,
      foregroundColor: Brand.paper,
      elevation: 0,
      centerTitle: false,
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: Brand.panel,
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: Brand.yellow, width: 1.5),
      ),
      labelStyle: const TextStyle(color: Brand.muted),
    ),
    filledButtonTheme: FilledButtonThemeData(
      style: FilledButton.styleFrom(
        backgroundColor: Brand.yellow,
        foregroundColor: Brand.ink,
        minimumSize: const Size(48, 50),
        textStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
    ),
    navigationBarTheme: const NavigationBarThemeData(
      backgroundColor: Brand.panel,
      indicatorColor: Brand.yellow,
    ),
    snackBarTheme: const SnackBarThemeData(behavior: SnackBarBehavior.floating),
  );
}
