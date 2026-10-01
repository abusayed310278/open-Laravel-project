import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/utils/formatters.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../core/widgets/order_status_pill.dart';
import '../../data/models/order.dart';
import '../../providers/order_provider.dart';

class OrderDetailScreen extends StatelessWidget {
  const OrderDetailScreen({super.key, required this.order});

  final Order order;

  Future<void> _pickAndUploadPaymentProof(BuildContext context) async {
    try {
      final picker = ImagePicker();
      final XFile? image = await picker.pickImage(source: ImageSource.gallery);
      if (image == null) return;

      if (!context.mounted) return;

      final orderProvider = context.read<OrderProvider>();
      final vendorOrderId = int.tryParse(order.id.replaceAll(RegExp(r'[^0-9]'), '')) ?? 1;

      AppSnackbar.show(context, 'Uploading payment proof...');
      final success = await orderProvider.submitPaymentProof(
        vendorOrderId: vendorOrderId,
        proofFile: File(image.path),
        notes: 'Payment proof uploaded via mobile app',
      );

      if (!context.mounted) return;

      if (success) {
        AppSnackbar.showSuccess(context, 'Payment proof uploaded successfully!');
      } else {
        AppSnackbar.showError(context, orderProvider.errorMessage ?? 'Upload failed');
      }
    } catch (e) {
      if (context.mounted) {
        AppSnackbar.showError(context, 'Failed to pick image: $e');
      }
    }
  }

  void _showRefundDialog(BuildContext context) {
    final reasonController = TextEditingController();
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Request Refund'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Please provide the reason for your refund request:'),
            const SizedBox(height: AppSpacing.md),
            TextField(
              controller: reasonController,
              maxLines: 3,
              decoration: const InputDecoration(
                hintText: 'e.g. Defective item, wrong item delivered...',
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () async {
              final reason = reasonController.text.trim();
              if (reason.isEmpty) {
                AppSnackbar.showError(ctx, 'Please enter a reason');
                return;
              }
              Navigator.of(ctx).pop();

              final vendorOrderId = int.tryParse(order.id.replaceAll(RegExp(r'[^0-9]'), '')) ?? 1;
              final orderProvider = context.read<OrderProvider>();

              final success = await orderProvider.requestRefund(
                vendorOrderId: vendorOrderId,
                reason: reason,
              );

              if (!context.mounted) return;

              if (success) {
                AppSnackbar.showSuccess(context, 'Refund requested successfully!');
              } else {
                AppSnackbar.showError(context, orderProvider.errorMessage ?? 'Refund request submitted.');
              }
            },
            child: const Text('Submit Request'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isCancelled = order.status == OrderStatus.cancelled;
    final currentStep = Order.pipeline.indexOf(order.status);

    return Scaffold(
      appBar: AppBar(title: Text('Order #${order.id}')),
      body: ListView(
        padding: const EdgeInsets.all(AppSpacing.lg),
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('Placed on ${order.placedAt.day}/${order.placedAt.month}/${order.placedAt.year}', style: const TextStyle(fontSize: 12, color: AppColors.slate500)),
              OrderStatusPill(status: order.status),
            ],
          ),
          const SizedBox(height: AppSpacing.xl),
          if (!isCancelled) ...[
            Container(
              padding: const EdgeInsets.all(AppSpacing.lg),
              decoration: BoxDecoration(color: AppColors.surface, border: Border.all(color: AppColors.slate200), borderRadius: BorderRadius.circular(AppRadius.md)),
              child: Column(
                children: [
                  if (order.trackingNumber != null)
                    Padding(
                      padding: const EdgeInsets.only(bottom: AppSpacing.lg),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text('Tracking number', style: TextStyle(fontSize: 12, color: AppColors.slate500)),
                          Text(order.trackingNumber!, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
                        ],
                      ),
                    ),
                  ...List.generate(Order.pipeline.length, (i) {
                    final step = Order.pipeline[i];
                    final done = i <= currentStep;
                    final isLast = i == Order.pipeline.length - 1;
                    return IntrinsicHeight(
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Column(
                            children: [
                              Icon(
                                done ? Icons.check_circle_rounded : Icons.radio_button_unchecked_rounded,
                                size: 20,
                                color: done ? AppColors.success : AppColors.slate300,
                              ),
                              if (!isLast)
                                Expanded(
                                  child: Container(width: 2, color: i < currentStep ? AppColors.success : AppColors.slate200),
                                ),
                            ],
                          ),
                          const SizedBox(width: AppSpacing.md),
                          Expanded(
                            child: Padding(
                              padding: const EdgeInsets.only(bottom: AppSpacing.lg),
                              child: Text(
                                step.label,
                                style: TextStyle(fontSize: 13, fontWeight: done ? FontWeight.w700 : FontWeight.w500, color: done ? AppColors.ink : AppColors.slate400),
                              ),
                            ),
                          ),
                        ],
                      ),
                    );
                  }),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.xl),
          ],
          const Text('Items', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.ink)),
          const SizedBox(height: AppSpacing.sm),
          ...order.items.map(
            (item) => Container(
              margin: const EdgeInsets.only(bottom: AppSpacing.sm),
              padding: const EdgeInsets.all(AppSpacing.md),
              decoration: BoxDecoration(color: AppColors.surface, border: Border.all(color: AppColors.slate200), borderRadius: BorderRadius.circular(AppRadius.md)),
              child: Row(
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(AppRadius.sm),
                    child: Image.network(
                      item.imageUrl,
                      width: 56,
                      height: 56,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => const Icon(Icons.image_not_supported),
                    ),
                  ),
                  const SizedBox(width: AppSpacing.md),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(item.productTitle, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600), maxLines: 2, overflow: TextOverflow.ellipsis),
                        Text('Sold by ${item.sellerName}', style: const TextStyle(fontSize: 11, color: AppColors.slate500)),
                        Text('Qty ${item.quantity}', style: const TextStyle(fontSize: 11, color: AppColors.slate500)),
                      ],
                    ),
                  ),
                  Text(formatPrice(item.price), style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                ],
              ),
            ),
          ),
          const SizedBox(height: AppSpacing.md),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('Total', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700)),
              Text(formatPrice(order.total), style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w800)),
            ],
          ),
          const SizedBox(height: AppSpacing.xl),
          Row(
            children: [
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: () => _pickAndUploadPaymentProof(context),
                  icon: const Icon(Icons.upload_file_outlined, size: 18),
                  label: const Text('Payment Proof'),
                ),
              ),
              const SizedBox(width: AppSpacing.md),
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: () => _showRefundDialog(context),
                  icon: const Icon(Icons.assignment_return_outlined, size: 18),
                  label: const Text('Request Refund'),
                ),
              ),
            ],
          ),
          if (order.status == OrderStatus.delivered) ...[
            const SizedBox(height: AppSpacing.md),
            ElevatedButton.icon(
              onPressed: () => AppSnackbar.showSuccess(context, 'Thanks! Review form submitted.'),
              icon: const Icon(Icons.rate_review_outlined, size: 18),
              label: const Text('Write a Review'),
            ),
          ],
        ],
      ),
    );
  }
}
