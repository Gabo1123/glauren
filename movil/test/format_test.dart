import 'package:flutter_test/flutter_test.dart';
import 'package:glauren/format.dart';

void main() {
  test('money', () {
    expect(money(5589000), '\$5.589.000');
    expect(money(0), '\$0');
    expect(money('342200'), '\$342.200');
  });
  test('waNumber', () {
    expect(waNumber('300 123 4567'), '573001234567');
    expect(waNumber('573001234567'), '573001234567');
  });
  test('dates', () {
    expect(isoDay(DateTime(2026, 3, 7)), '2026-03-07');
    expect(longDate('2026-11-04'), '4 de noviembre de 2026');
  });
}
