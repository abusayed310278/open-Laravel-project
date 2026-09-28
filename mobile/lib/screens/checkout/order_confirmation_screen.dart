import 'package:flutter/material.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../navigation/app_shell.dart';
import '../orders/order_detail_screen.dart';
import '../../data/mock/mock_data.dart';

class OrderConfirmationScreen extends StatelessWidget {
  const OrderConfirmationScreen({super.key, required this.orderId});

  final String orderId;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(AppSpacing.xxl),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                width: 96,
                height: 96,
                decoration: const BoxDecoration(color: AppColors.successBg, shape: BoxShape.circle),
                child: const Icon(Icons.check_rounded, color: AppColors.success, size: 52),
              ),
              const SizedBox(height: AppSpacing.xl),
              const Text('Order placed!', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: AppColors.ink)),
              const SizedBox(height: AppSpacing.sm),
              Text(
                'Your order #$orderId has been confirmed. You\'ll receive updates as it\'s processed and shipped.',
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 13, color: AppColors.slate500, height: 1.5),
              ),
              const SizedBox(height: AppSpacing.xxxl),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () => Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => OrderDetailScreen(order: MockData.orders.first)),
                  ),
                  child: const Text('Track order'),
                ),
              ),
              const SizedBox(height: AppSpacing.md),
              SizedBox(
                width: double.infinity,
                child: OutlinedButton(
                  onPressed: () => Navigator.of(context).pushAndRemoveUntil(
                    MaterialPageRoute(builder: (_) => const AppShell()),
                    (route) => false,
                  ),
                  child: const Text('Continue shopping'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
