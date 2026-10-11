import 'package:flutter/material.dart';
import '../theme/app_colors.dart';
import '../theme/app_spacing.dart';

class StatusBadge extends StatelessWidget {
  final String status;
  final String? label;
  final bool isSmall;

  const StatusBadge({
    super.key,
    required this.status,
    this.label,
    this.isSmall = false,
  });

  @override
  Widget build(BuildContext context) {
    final (Color bg, Color text) = _getColors(status.toLowerCase());
    final displayLabel = label ?? status.replaceAll('_', ' ').toUpperCase();

    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: isSmall ? 8 : 10,
        vertical: isSmall ? 3 : 5,
      ),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(AppRadius.pill),
      ),
      child: Text(
        displayLabel,
        style: TextStyle(
          fontSize: isSmall ? 10 : 12,
          fontWeight: FontWeight.w700,
          color: text,
          letterSpacing: 0.2,
        ),
      ),
    );
  }

  (Color, Color) _getColors(String s) {
    switch (s) {
      case 'delivered':
      case 'published':
      case 'active':
      case 'approved':
        return (AppColors.successBg, AppColors.success);
      case 'shipped':
      case 'out_for_delivery':
      case 'info':
        return (AppColors.infoBg, AppColors.info);
      case 'processing':
      case 'packed':
        return (AppColors.purpleBg, AppColors.purple);
      case 'pending':
      case 'pending_approval':
      case 'confirmed':
        return (AppColors.warningBg, AppColors.warning);
      case 'cancelled':
      case 'rejected':
      case 'failed':
        return (AppColors.dangerBg, AppColors.danger);
      case 'draft':
      case 'unpublished':
      default:
        return (AppColors.slate100, AppColors.slate600);
    }
  }
}
