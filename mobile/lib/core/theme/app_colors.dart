import 'package:flutter/material.dart';

/// Brand palette — mirrors the web app's `--color-brand-*` amber scale
/// (resources/css/app.css) so the mobile app stays visually consistent
/// with the Openbox marketplace website.
abstract final class AppColors {
  static const Color brand50 = Color(0xFFFFFBEB);
  static const Color brand100 = Color(0xFFFEF3C7);
  static const Color brand200 = Color(0xFFFDE68A);
  static const Color brand300 = Color(0xFFFCD34D);
  static const Color brand400 = Color(0xFFFBBF24);
  static const Color brand500 = Color(0xFFF59E0B);
  static const Color brand600 = Color(0xFFD97706);
  static const Color brand700 = Color(0xFFB45309);
  static const Color brand800 = Color(0xFF92400E);
  static const Color brand900 = Color(0xFF78350F);

  static const Color ink = Color(0xFF0F172A);
  static const Color slate700 = Color(0xFF334155);
  static const Color slate500 = Color(0xFF64748B);
  static const Color slate400 = Color(0xFF94A3B8);
  static const Color slate300 = Color(0xFFCBD5E1);
  static const Color slate200 = Color(0xFFE2E8F0);
  static const Color slate100 = Color(0xFFF1F5F9);
  static const Color slate50 = Color(0xFFF8FAFC);
  static const Color surface = Color(0xFFFFFFFF);

  static const Color success = Color(0xFF16A34A);
  static const Color successBg = Color(0xFFDCFCE7);
  static const Color warning = Color(0xFFD97706);
  static const Color warningBg = Color(0xFFFEF3C7);
  static const Color danger = Color(0xFFDC2626);
  static const Color dangerBg = Color(0xFFFEE2E2);
  static const Color info = Color(0xFF2563EB);
  static const Color infoBg = Color(0xFFDBEAFE);

  /// Condition-grade colors (Grade A / B / C — see grading system).
  static const Color gradeA = Color(0xFF16A34A);
  static const Color gradeB = Color(0xFF2563EB);
  static const Color gradeC = Color(0xFFD97706);

  static const Color verified = Color(0xFF2563EB);
}
