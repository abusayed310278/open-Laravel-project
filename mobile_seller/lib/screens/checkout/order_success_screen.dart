import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/custom_button.dart';

class OrderSuccessScreen extends StatelessWidget {
  final Map<String, dynamic> order;
  final String paymentMethod;
  final Map<String, dynamic>? address;

  const OrderSuccessScreen({
    super.key,
    required this.order,
    required this.paymentMethod,
    this.address,
  });

  @override
  Widget build(BuildContext context) {
    final currencyFormat = NumberFormat('#,##0.00', 'en_US');
    final orderNumber = order['order_number']?.toString() ?? '#${order['id']}';
    final total = (order['total'] as num?)?.toDouble() ?? 0.0;
    final isStripe = paymentMethod == 'stripe';

    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, _) {
        if (!didPop) {
          Navigator.of(context).popUntil((route) => route.isFirst);
        }
      },
      child: Scaffold(
        backgroundColor: AppColors.slate50,
        appBar: AppBar(
          automaticallyImplyLeading: false,
          title: const Text('Order Confirmation'),
          centerTitle: true,
        ),
        body: SafeArea(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(AppSpacing.xl),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                const SizedBox(height: AppSpacing.md),
                // Success Badge
                Container(
                  width: 80,
                  height: 80,
                  decoration: BoxDecoration(
                    color: AppColors.successBg,
                    shape: BoxShape.circle,
                    boxShadow: [
                      BoxShadow(
                        color: AppColors.success.withValues(alpha: 0.2),
                        blurRadius: 16,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: const Icon(
                    Icons.check_rounded,
                    size: 46,
                    color: AppColors.success,
                  ),
                ),
                const SizedBox(height: AppSpacing.lg),

                const Text(
                  'Order Placed Successfully!',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w800,
                    color: AppColors.ink,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 6),
                Text(
                  isStripe
                      ? 'Your card payment has been verified via Stripe.'
                      : 'Your order is confirmed and will be delivered soon.',
                  style: const TextStyle(
                    fontSize: 13,
                    color: AppColors.slate600,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: AppSpacing.xl),

                // Order Details Card
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(AppSpacing.lg),
                  decoration: BoxDecoration(
                    color: AppColors.surface,
                    borderRadius: BorderRadius.circular(AppRadius.lg),
                    border: Border.all(color: AppColors.slate200),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.03),
                        blurRadius: 8,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Column(
                    children: [
                      _buildRow('Order Number', orderNumber, isBold: true, valueColor: AppColors.brand700),
                      const Divider(height: 24, color: AppColors.slate100),
                      _buildRow('Payment Method', isStripe ? 'Card (Stripe)' : 'Cash on Delivery'),
                      const Divider(height: 24, color: AppColors.slate100),
                      _buildRow('Payment Status', isStripe ? 'Paid' : 'Pay on Delivery',
                          valueColor: isStripe ? AppColors.success : AppColors.warning),
                      const Divider(height: 24, color: AppColors.slate100),
                      _buildRow('Total Amount', '${currencyFormat.format(total)} QAR',
                          isBold: true, valueColor: AppColors.ink, valueSize: 16),
                      if (address != null) ...[
                        const Divider(height: 24, color: AppColors.slate100),
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Delivery To',
                              style: TextStyle(fontSize: 13, color: AppColors.slate500),
                            ),
                            const Spacer(),
                            Expanded(
                              flex: 2,
                              child: Text(
                                '${address!['name'] ?? ''}\n${address!['line1'] ?? ''}, ${address!['city'] ?? 'Doha'}',
                                textAlign: TextAlign.right,
                                style: const TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.w600,
                                  color: AppColors.ink,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ],
                  ),
                ),
                const SizedBox(height: AppSpacing.lg),

                // Payment Instruction Note
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(AppSpacing.md),
                  decoration: BoxDecoration(
                    color: isStripe ? AppColors.infoBg.withValues(alpha: 0.6) : AppColors.brand50,
                    borderRadius: BorderRadius.circular(AppRadius.md),
                    border: Border.all(
                      color: isStripe ? AppColors.info.withValues(alpha: 0.3) : AppColors.brand200,
                    ),
                  ),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Icon(
                        isStripe ? Icons.verified_user_outlined : Icons.info_outline_rounded,
                        size: 20,
                        color: isStripe ? AppColors.info : AppColors.brand700,
                      ),
                      const SizedBox(width: AppSpacing.sm),
                      Expanded(
                        child: Text(
                          isStripe
                              ? 'Your card payment has been processed securely. An invoice and order confirmation have been generated.'
                              : 'Please ensure you have ${currencyFormat.format(total)} QAR ready in cash upon arrival of your delivery courier.',
                          style: TextStyle(
                            fontSize: 12,
                            height: 1.4,
                            color: isStripe ? AppColors.slate800 : AppColors.brand900,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: AppSpacing.xxl),

                // Action Buttons
                CustomButton(
                  text: 'Continue Shopping',
                  backgroundColor: AppColors.brand600,
                  onPressed: () {
                    Navigator.of(context).popUntil((route) => route.isFirst);
                  },
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildRow(String label, String value,
      {bool isBold = false, Color? valueColor, double valueSize = 13}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: const TextStyle(fontSize: 13, color: AppColors.slate500),
        ),
        Text(
          value,
          style: TextStyle(
            fontSize: valueSize,
            fontWeight: isBold ? FontWeight.w800 : FontWeight.w600,
            color: valueColor ?? AppColors.ink,
          ),
        ),
      ],
    );
  }
}

