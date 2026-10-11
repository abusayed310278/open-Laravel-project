import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/custom_text_field.dart';
import '../../core/widgets/loading_view.dart';
import '../../core/widgets/status_badge.dart';
import '../../providers/seller_order_provider.dart';

class OrderDetailScreen extends StatefulWidget {
  final int vendorOrderId;

  const OrderDetailScreen({super.key, required this.vendorOrderId});

  @override
  State<OrderDetailScreen> createState() => _OrderDetailScreenState();
}

class _OrderDetailScreenState extends State<OrderDetailScreen> {
  final _currencyFormat = NumberFormat('#,##0.00', 'en_US');
  final _trackingController = TextEditingController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<SellerOrderProvider>().fetchOrderDetail(widget.vendorOrderId);
    });
  }

  @override
  void dispose() {
    _trackingController.dispose();
    super.dispose();
  }

  void _showStatusDialog(String targetStatus, String label) {
    _trackingController.text = context.read<SellerOrderProvider>().selectedOrderDetail?.trackingNumber ?? '';

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text('Update to $label'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Are you sure you want to transition this order to "$label"?',
              style: const TextStyle(fontSize: 13, color: AppColors.slate600),
            ),
            if (targetStatus == 'shipped' || targetStatus == 'out_for_delivery') ...[
              const SizedBox(height: AppSpacing.md),
              CustomTextField(
                controller: _trackingController,
                label: 'Courier Tracking Number',
                hint: 'e.g. QPOST-10928392',
              ),
            ],
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.of(ctx).pop();
              final provider = context.read<SellerOrderProvider>();
              final success = await provider.updateOrderStatus(
                widget.vendorOrderId,
                targetStatus,
                trackingNumber: _trackingController.text,
              );

              if (mounted) {
                if (success) {
                  AppSnackBar.success(context, 'Order status updated successfully.');
                } else {
                  AppSnackBar.error(context, provider.errorMessage ?? 'Update failed.');
                }
              }
            },
            child: const Text('Confirm'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final orderProvider = context.watch<SellerOrderProvider>();
    final order = orderProvider.selectedOrderDetail;

    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        title: Text(order?.orderNumber ?? 'Order #${widget.vendorOrderId}'),
      ),
      body: orderProvider.isDetailLoading && order == null
          ? const LoadingView(message: 'Loading order details...')
          : order == null
              ? Center(
                  child: Text(orderProvider.errorMessage ?? 'Order not found', style: const TextStyle(color: AppColors.slate500)),
                )
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(AppSpacing.lg),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // 1. Order Status Banner
                      _buildStatusHeader(order),
                      const SizedBox(height: AppSpacing.lg),

                      // 2. Customer & Shipping Info
                      _buildCustomerAndShipping(order),
                      const SizedBox(height: AppSpacing.lg),

                      // 3. Items List
                      _buildItemsSection(order),
                      const SizedBox(height: AppSpacing.lg),

                      // 4. Payment & Totals Summary
                      _buildPaymentSummary(order),
                      const SizedBox(height: AppSpacing.xxl),
                    ],
                  ),
                ),
      bottomNavigationBar: order != null && order.nextStatuses.isNotEmpty
          ? Container(
              padding: const EdgeInsets.all(AppSpacing.lg),
              decoration: BoxDecoration(
                color: AppColors.surface,
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.04),
                    offset: const Offset(0, -2),
                    blurRadius: 8,
                  ),
                ],
              ),
              child: SafeArea(
                child: Row(
                  children: order.nextStatuses.map((opt) {
                    final isCancel = opt.value == 'cancelled';
                    return Expanded(
                      child: Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 4),
                        child: CustomButton(
                          text: opt.label,
                          isOutlined: isCancel,
                          backgroundColor: isCancel ? AppColors.danger : AppColors.brand500,
                          textColor: isCancel ? AppColors.danger : Colors.white,
                          onPressed: () => _showStatusDialog(opt.value, opt.label),
                        ),
                      ),
                    );
                  }).toList(),
                ),
              ),
            )
          : null,
    );
  }

  Widget _buildStatusHeader(dynamic order) {
    return Container(
      padding: const EdgeInsets.all(AppSpacing.lg),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.slate200.withOpacity(0.8)),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                order.orderNumber,
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w800,
                  color: AppColors.ink,
                  fontFamily: 'monospace',
                ),
              ),
              const SizedBox(height: 4),
              Text(
                order.createdAt != null
                    ? DateFormat('MMMM dd, yyyy · hh:mm a').format(DateTime.parse(order.createdAt!))
                    : '',
                style: const TextStyle(fontSize: 12, color: AppColors.slate400),
              ),
            ],
          ),
          StatusBadge(status: order.status),
        ],
      ),
    );
  }

  Widget _buildCustomerAndShipping(dynamic order) {
    final shipping = order.shippingAddress;

    return Container(
      padding: const EdgeInsets.all(AppSpacing.lg),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.slate200.withOpacity(0.8)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: const [
              Icon(Icons.person_outline, size: 20, color: AppColors.slate500),
              SizedBox(width: 8),
              Text(
                'Customer & Delivery',
                style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.ink),
              ),
            ],
          ),
          const Divider(height: 24, color: AppColors.slate100),
          _buildInfoRow('Name', order.customerName),
          if (order.customerPhone != null) _buildInfoRow('Phone', order.customerPhone!),
          if (order.customerEmail != null) _buildInfoRow('Email', order.customerEmail!),
          if (shipping != null) ...[
            const SizedBox(height: 8),
            _buildInfoRow(
              'Delivery Address',
              [
                shipping.addressLine1,
                if (shipping.addressLine2 != null && shipping.addressLine2!.isNotEmpty) shipping.addressLine2,
                shipping.city,
                shipping.country,
              ].where((e) => e != null && e.isNotEmpty).join(', '),
            ),
          ],
          if (order.trackingNumber != null && order.trackingNumber!.isNotEmpty) ...[
            const SizedBox(height: 8),
            _buildInfoRow('Tracking #', order.trackingNumber!),
          ],
        ],
      ),
    );
  }

  Widget _buildItemsSection(dynamic order) {
    return Container(
      padding: const EdgeInsets.all(AppSpacing.lg),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.slate200.withOpacity(0.8)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Order Items (${order.items.length})',
            style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.ink),
          ),
          const Divider(height: 24, color: AppColors.slate100),
          ...order.items.map((item) {
            return Padding(
              padding: const EdgeInsets.only(bottom: AppSpacing.md),
              child: Row(
                children: [
                  Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      color: AppColors.slate100,
                      borderRadius: BorderRadius.circular(AppRadius.sm),
                    ),
                    clipBehavior: Clip.antiAlias,
                    child: item.thumbnail != null
                        ? Image.network(
                            item.thumbnail!,
                            fit: BoxFit.cover,
                            errorBuilder: (_, __, ___) => const Icon(Icons.inventory_2, size: 24, color: AppColors.slate400),
                          )
                        : const Icon(Icons.inventory_2, size: 24, color: AppColors.slate400),
                  ),
                  const SizedBox(width: AppSpacing.md),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          item.title,
                          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.ink),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 2),
                        Text(
                          '${_currencyFormat.format(item.unitPrice)} QAR × ${item.quantity}',
                          style: const TextStyle(fontSize: 12, color: AppColors.slate500),
                        ),
                      ],
                    ),
                  ),
                  Text(
                    '${_currencyFormat.format(item.totalPrice)} QAR',
                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.ink),
                  ),
                ],
              ),
            );
          }),
        ],
      ),
    );
  }

  Widget _buildPaymentSummary(dynamic order) {
    return Container(
      padding: const EdgeInsets.all(AppSpacing.lg),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.slate200.withOpacity(0.8)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Payment Summary',
            style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.ink),
          ),
          const Divider(height: 20, color: AppColors.slate100),
          _buildSummaryRow('Subtotal', '${_currencyFormat.format(order.subtotal)} QAR'),
          const SizedBox(height: 6),
          _buildSummaryRow('Shipping', '${_currencyFormat.format(order.shipping)} QAR'),
          if (order.tax > 0) ...[
            const SizedBox(height: 6),
            _buildSummaryRow('Tax', '${_currencyFormat.format(order.tax)} QAR'),
          ],
          const Divider(height: 20, color: AppColors.slate100),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Total Amount',
                style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: AppColors.ink),
              ),
              Text(
                '${_currencyFormat.format(order.total)} QAR',
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w800,
                  color: AppColors.brand600,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 110,
            child: Text(
              label,
              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.slate400),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.ink),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSummaryRow(String label, String value) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: const TextStyle(fontSize: 13, color: AppColors.slate500)),
        Text(value, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.ink)),
      ],
    );
  }
}
