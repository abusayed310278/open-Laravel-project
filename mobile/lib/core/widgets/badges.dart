import 'package:flutter/material.dart';

import '../../data/models/product.dart';
import '../theme/app_colors.dart';
import '../theme/app_spacing.dart';

class GradeBadge extends StatelessWidget {
  const GradeBadge({super.key, required this.grade});

  final ProductGrade grade;

  Color get _color => switch (grade) {
        ProductGrade.a => AppColors.gradeA,
        ProductGrade.b => AppColors.gradeB,
        ProductGrade.c => AppColors.gradeC,
      };

  @override
  Widget build(BuildContext context) {
    return _Pill(
      color: _color,
      icon: Icons.verified_rounded,
      label: 'Grade ${grade.short}',
    );
  }
}

class VerifiedBadge extends StatelessWidget {
  const VerifiedBadge({super.key});

  @override
  Widget build(BuildContext context) {
    return const _Pill(color: AppColors.verified, icon: Icons.shield_rounded, label: 'Verified');
  }
}

class WarehouseBadge extends StatelessWidget {
  const WarehouseBadge({super.key});

  @override
  Widget build(BuildContext context) {
    return const _Pill(color: AppColors.brand700, icon: Icons.warehouse_rounded, label: 'Openbox');
  }
}

class NewBadge extends StatelessWidget {
  const NewBadge({super.key});

  @override
  Widget build(BuildContext context) {
    return const _Pill(color: AppColors.success, icon: Icons.fiber_new_rounded, label: 'New');
  }
}

class DiscountBadge extends StatelessWidget {
  const DiscountBadge({super.key, required this.percent});

  final int percent;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: AppSpacing.sm, vertical: 2),
      decoration: BoxDecoration(
        color: AppColors.danger,
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        '-$percent%',
        style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700),
      ),
    );
  }
}

class _Pill extends StatelessWidget {
  const _Pill({required this.color, required this.icon, required this.label});

  final Color color;
  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: AppSpacing.sm, vertical: 3),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(AppSpacing.sm),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 12, color: color),
          const SizedBox(width: 3),
          Text(label, style: TextStyle(color: color, fontSize: 11, fontWeight: FontWeight.w700)),
        ],
      ),
    );
  }
}
