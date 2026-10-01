import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/utils/formatters.dart';
import '../../core/widgets/empty_state.dart';
import '../../core/widgets/order_status_pill.dart';
import '../../data/models/order.dart';
import '../../providers/order_provider.dart';
import 'order_detail_screen.dart';

class OrdersListScreen extends StatefulWidget {
  const OrdersListScreen({super.key});

  @override
  State<OrdersListScreen> createState() => _OrdersListScreenState();
}

class _OrdersListScreenState extends State<OrdersListScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => context.read<OrderProvider>().fetchOrders());
  }

  @override
  Widget build(BuildContext context) {
    final orderProvider = context.watch<OrderProvider>();
    final orders = orderProvider.orders;

    return Scaffold(
      appBar: AppBar(title: const Text('My Orders')),
      body: orderProvider.isLoading && orders.isEmpty
          ? const Center(child: CircularProgressIndicator())
          : orders.isEmpty
              ? const EmptyState(icon: Icons.receipt_long_outlined, title: 'No orders yet', message: 'Your order history will show up here once you place an order.')
              : RefreshIndicator(
                  onRefresh: () => context.read<OrderProvider>().fetchOrders(),
                  child: ListView.separated(
                    padding: const EdgeInsets.all(AppSpacing.lg),
                    itemCount: orders.length,
                    separatorBuilder: (_, _) => const SizedBox(height: AppSpacing.md),
                    itemBuilder: (context, i) => _OrderCard(order: orders[i]),
                  ),
                ),
    );
  }
}

class _OrderCard extends StatelessWidget {
  const _OrderCard({required this.order});
  final Order order;

  @override
  Widget build(BuildContext context) {
    final firstItem = order.items.isNotEmpty
        ? order.items.first
        : const OrderItem(
            productTitle: 'Order Items',
            imageUrl: 'https://via.placeholder.com/150',
            price: 0,
            quantity: 1,
            sellerName: 'Store',
          );

    return GestureDetector(
      onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => OrderDetailScreen(order: order))),
      child: Container(
        padding: const EdgeInsets.all(AppSpacing.md),
        decoration: BoxDecoration(
          color: AppColors.surface,
          border: Border.all(color: AppColors.slate200),
          borderRadius: BorderRadius.circular(AppRadius.md),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Text(order.id.startsWith('#') ? order.id : '#${order.id}', style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                const Spacer(),
                OrderStatusPill(status: order.status),
              ],
            ),
            const SizedBox(height: AppSpacing.sm),
            Row(
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(AppRadius.sm),
                  child: Image.network(
                    firstItem.imageUrl,
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
                      Text(
                        order.items.length > 1 ? '${firstItem.productTitle} + ${order.items.length - 1} more' : firstItem.productTitle,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
                      ),
                      const SizedBox(height: 2),
                      Text('${order.placedAt.day}/${order.placedAt.month}/${order.placedAt.year}', style: const TextStyle(fontSize: 11, color: AppColors.slate400)),
                    ],
                  ),
                ),
                Text(formatPrice(order.total), style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14)),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
