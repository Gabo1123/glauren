/// Formatos en español de Colombia, sin paquetes extra.
const _months = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
const _monthsLong = [
  'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
  'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
];

/// 1234567 → $1.234.567
String money(dynamic v) {
  final n = (v is num ? v : num.tryParse('${v ?? 0}') ?? 0).round();
  final s = n.abs().toString();
  final b = StringBuffer();
  for (var i = 0; i < s.length; i++) {
    if (i > 0 && (s.length - i) % 3 == 0) b.write('.');
    b.write(s[i]);
  }
  return '${n < 0 ? '-' : ''}\$$b';
}

DateTime? parseDate(dynamic v) => v == null ? null : DateTime.tryParse(v.toString())?.toLocal();

/// "12 nov"
String shortDate(dynamic v) {
  final d = parseDate(v);
  return d == null ? '—' : '${d.day} ${_months[d.month - 1]}';
}

/// "12 de noviembre de 2026"
String longDate(dynamic v) {
  final d = parseDate(v);
  return d == null ? '—' : '${d.day} de ${_monthsLong[d.month - 1]} de ${d.year}';
}

/// "hace 3 h", "hace 2 días"
String ago(dynamic v) {
  final d = parseDate(v);
  if (d == null) return '';
  final diff = DateTime.now().difference(d);
  if (diff.inMinutes < 1) return 'ahora';
  if (diff.inMinutes < 60) return 'hace ${diff.inMinutes} min';
  if (diff.inHours < 24) return 'hace ${diff.inHours} h';
  if (diff.inDays < 30) return 'hace ${diff.inDays} d';
  return shortDate(v);
}

/// yyyy-mm-dd
String isoDay(DateTime d) =>
    '${d.year}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}';

String str(dynamic v, [String empty = '']) => v == null || '$v'.isEmpty ? empty : '$v';

int toInt(dynamic v) => v is num ? v.toInt() : int.tryParse('${v ?? ''}') ?? 0;

/// Número de WhatsApp para Colombia (agrega 57 a celulares de 10 dígitos).
String waNumber(String phone) {
  final p = phone.replaceAll(RegExp(r'\D'), '');
  return p.length == 10 ? '57$p' : p;
}
