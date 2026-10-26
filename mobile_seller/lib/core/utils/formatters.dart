/// Matches the web app's default currency (USD) — see project memory:
/// "Currency: USD ($), not QAR".
String formatPrice(num value) {
  final fixed = value.toStringAsFixed(value % 1 == 0 ? 0 : 2);
  final parts = fixed.split('.');
  final whole = parts[0];
  final buffer = StringBuffer();
  for (int i = 0; i < whole.length; i++) {
    if (i > 0 && (whole.length - i) % 3 == 0) buffer.write(',');
    buffer.write(whole[i]);
  }
  final result = buffer.toString();
  return parts.length > 1 ? '\$$result.${parts[1]}' : '\$$result';
}
