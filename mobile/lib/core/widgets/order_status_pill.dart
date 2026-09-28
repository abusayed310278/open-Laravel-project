import 'package:flutter/material.dart';

import '../../data/models/order.dart';
import '../theme/app_colors.dart';
import '../theme/app_spacing.dart';

class OrderStatusPill extends StatelessWidget {
  const OrderStatusPill({super.key, required this.status});

  final OrderStatus status;

  Color get _color => switch (status) {
        OrderStatus.delivered => AppColors.success,
        OrderStatus.cancelled => AppColors.danger,
        OrderStatus.confirmed || OrderStatus.processing => AppColors.info,
        OrderStatus.packed || OrderStatus.shipped || OrderStatus.outForDelivery => AppColors.warning,
      };

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: AppSpacing.sm, vertical: 4),
      decoration: BoxDecoration(color: _color.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(AppRadius.sm)),
      child: Text(status.label, style: TextStyle(color: _color, fontSize: 12, fontWeight: FontWeight.w700)),
    );
  }
}
