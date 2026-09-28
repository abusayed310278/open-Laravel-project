import 'package:flutter/material.dart';

import '../theme/app_colors.dart';
import '../theme/app_spacing.dart';

enum _SnackType { neutral, success, error, info }

/// Single entry point for every snackbar in the app so toasts look and
/// behave the same everywhere (icon, colors, duration, one-at-a-time).
abstract final class AppSnackbar {
  static void show(
    BuildContext context,
    String message, {
    IconData icon = Icons.info_outline_rounded,
    String? actionLabel,
    VoidCallback? onAction,
    Duration duration = const Duration(seconds: 3),
  }) {
    _showRaw(context, message, _SnackType.neutral, icon, actionLabel, onAction, duration);
  }

  static void success(
    BuildContext context,
    String message, {
    String? actionLabel,
    VoidCallback? onAction,
    Duration duration = const Duration(seconds: 3),
  }) {
    _showRaw(context, message, _SnackType.success, Icons.check_circle_rounded, actionLabel, onAction, duration);
  }

  static void showSuccess(
    BuildContext context,
    String message, {
    String? actionLabel,
    VoidCallback? onAction,
    Duration duration = const Duration(seconds: 3),
  }) =>
      success(context, message, actionLabel: actionLabel, onAction: onAction, duration: duration);

  static void error(
    BuildContext context,
    String message, {
    String? actionLabel,
    VoidCallback? onAction,
    Duration duration = const Duration(seconds: 4),
  }) {
    _showRaw(context, message, _SnackType.error, Icons.error_rounded, actionLabel, onAction, duration);
  }

  static void showError(
    BuildContext context,
    String message, {
    String? actionLabel,
    VoidCallback? onAction,
    Duration duration = const Duration(seconds: 4),
  }) =>
      error(context, message, actionLabel: actionLabel, onAction: onAction, duration: duration);

  static void info(
    BuildContext context,
    String message, {
    String? actionLabel,
    VoidCallback? onAction,
    Duration duration = const Duration(seconds: 3),
  }) {
    _showRaw(context, message, _SnackType.info, Icons.info_rounded, actionLabel, onAction, duration);
  }

  static Color _accentFor(_SnackType type) => switch (type) {
        _SnackType.success => AppColors.success,
        _SnackType.error => AppColors.danger,
        _SnackType.info => AppColors.brand400,
        _SnackType.neutral => AppColors.slate300,
      };

  static void _showRaw(
    BuildContext context,
    String message,
    _SnackType type,
    IconData icon,
    String? actionLabel,
    VoidCallback? onAction,
    Duration duration,
  ) {
    final messenger = ScaffoldMessenger.of(context);
    messenger.hideCurrentSnackBar();
    messenger.showSnackBar(
      SnackBar(
        duration: duration,
        behavior: SnackBarBehavior.floating,
        backgroundColor: AppColors.ink,
        margin: const EdgeInsets.fromLTRB(AppSpacing.lg, 0, AppSpacing.lg, AppSpacing.lg),
        padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.md),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
        content: Row(
          children: [
            Icon(icon, size: 20, color: _accentFor(type)),
            const SizedBox(width: AppSpacing.sm),
            Expanded(
              child: Text(message, style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.w600)),
            ),
          ],
        ),
        action: actionLabel == null
            ? null
            : SnackBarAction(
                label: actionLabel,
                textColor: AppColors.brand400,
                onPressed: onAction ?? () {},
              ),
      ),
    );
  }
}
