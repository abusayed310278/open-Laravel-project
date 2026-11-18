import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/empty_view.dart';
import '../../core/widgets/loading_view.dart';
import '../../core/widgets/status_badge.dart';
import '../../data/models/customer_order.dart';
import '../../providers/seller_auth_provider.dart';
import '../../providers/seller_order_provider.dart';
import '../auth/login_screen.dart';
import 'order_detail_screen.dart';

class OrdersScreen extends StatefulWidget {
  const OrdersScreen({super.key});

  @override
  State<OrdersScreen> createState() => _OrdersScreenState();
}

class _OrdersScreenState extends State<OrdersScreen> {
  final _searchController = TextEditingController();
  final _currencyFormat = NumberFormat('#,##0.00', 'en_US');

  static const List<Map<String, String>> _statusTabs = [
    {'key': 'all', 'label': 'All'},
    {'key': 'pending', 'label': 'Pending'},
    {'key': 'confirmed', 'label': 'Confirmed'},
    {'key': 'processing', 'label': 'Processing'},
    {'key': 'shipped', 'label': 'Shipped'},
    {'key': 'delivered', 'label': 'Delivered'},
    {'key': 'cancelled', 'label': 'Cancelled'},
  ];

  bool _hasFetched = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted && context.read<SellerAuthProvider>().isAuthenticated) {
        _hasFetched = true;
        context.read<SellerOrderProvider>().fetchAll();
      }
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final orderProvider = context.watch<SellerOrderProvider>();
    final isAuthenticated = context.watch<SellerAuthProvider>().isAuthenticated;

    if (isAuthenticated && !_hasFetched && !orderProvider.isLoading) {
      _hasFetched = true;
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (mounted) context.read<SellerOrderProvider>().fetchAll();
      });
    }

    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(title: const Text('Orders')),
      body: isAuthenticated ? _buildBody(orderProvider) : _buildSignInPrompt(),
    );
  }

  Widget _buildSignInPrompt() {
    return EmptyView(
      icon: Icons.lock_outline_rounded,
      title: 'Sign in to view your orders',
      subtitle: 'Log in to your account to see and manage your orders.',
      actionLabel: 'Sign In',
      onAction: () {
        Navigator.of(context)
            .push(MaterialPageRoute(builder: (_) => const LoginScreen()));
      },
    );
  }

  Widget _buildBody(SellerOrderProvider orderProvider) {
    return Column(
      children: [
        // Top Segmented Mode Selector: My Purchases vs Store Sales
        Container(
          margin: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.sm),
          padding: const EdgeInsets.all(4),
          decoration: BoxDecoration(
            color: AppColors.slate200.withValues(alpha: 0.6),
            borderRadius: BorderRadius.circular(AppRadius.md),
          ),
          child: Row(
            children: [
              // 1. My Purchases
              Expanded(
                child: InkWell(
                  onTap: () => orderProvider.setViewMode(OrderViewMode.purchases),
                  borderRadius: BorderRadius.circular(AppRadius.sm),
                  child: Container(
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    decoration: BoxDecoration(
                      color: orderProvider.viewMode == OrderViewMode.purchases ? AppColors.brandYellow : Colors.transparent,
                      borderRadius: BorderRadius.circular(AppRadius.sm),
                      boxShadow: orderProvider.viewMode == OrderViewMode.purchases
                          ? [BoxShadow(color: AppColors.brandYellow.withValues(alpha: 0.4), blurRadius: 6, offset: const Offset(0, 2))]
                          : null,
                    ),
                    alignment: Alignment.center,
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.shopping_bag_outlined,
                          size: 15,
                          color: orderProvider.viewMode == OrderViewMode.purchases ? AppColors.ink : AppColors.slate600,
                        ),
                        const SizedBox(width: 6),
                        Text(
                          'My Purchases (${orderProvider.customerOrders.length})',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w700,
                            color: orderProvider.viewMode == OrderViewMode.purchases ? AppColors.ink : AppColors.slate600,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
              // 2. Store Sales
              Expanded(
                child: InkWell(
                  onTap: () => orderProvider.setViewMode(OrderViewMode.sales),
                  borderRadius: BorderRadius.circular(AppRadius.sm),
                  child: Container(
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    decoration: BoxDecoration(
                      color: orderProvider.viewMode == OrderViewMode.sales ? AppColors.brandYellow : Colors.transparent,
                      borderRadius: BorderRadius.circular(AppRadius.sm),
                      boxShadow: orderProvider.viewMode == OrderViewMode.sales
                          ? [BoxShadow(color: AppColors.brandYellow.withValues(alpha: 0.4), blurRadius: 6, offset: const Offset(0, 2))]
                          : null,
                    ),
                    alignment: Alignment.center,
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.storefront_outlined,
                          size: 15,
                          color: orderProvider.viewMode == OrderViewMode.sales ? AppColors.ink : AppColors.slate600,
                        ),
                        const SizedBox(width: 6),
                        Text(
                          'Store Sales (${orderProvider.counts['all'] ?? 0})',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w700,
                            color: orderProvider.viewMode == OrderViewMode.sales ? AppColors.ink : AppColors.slate600,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),

        // Active View: Purchases or Sales
        Expanded(
          child: orderProvider.viewMode == OrderViewMode.purchases
              ? _buildPurchasesView(orderProvider)
              : _buildSalesView(orderProvider),
        ),
      ],
    );
  }

  Widget _buildPurchasesView(SellerOrderProvider orderProvider) {
    if (orderProvider.isCustomerLoading && orderProvider.customerOrders.isEmpty) {
      return const LoadingView(message: 'Loading purchases...');
    }

    if (orderProvider.customerOrders.isEmpty) {
      return RefreshIndicator(
        onRefresh: () => orderProvider.fetchCustomerOrders(),
        color: AppColors.brandYellowDark,
        child: ListView(
          children: const [
            SizedBox(height: 60),
            EmptyView(
              icon: Icons.shopping_bag_outlined,
              title: 'No Purchases Yet',
              subtitle: 'Items you purchase on Openbox will appear here.',
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: () => orderProvider.fetchCustomerOrders(),
      color: AppColors.brandYellowDark,
      child: ListView.separated(
        padding: const EdgeInsets.all(AppSpacing.lg),
        itemCount: orderProvider.customerOrders.length,
        separatorBuilder: (_, _) => const SizedBox(height: AppSpacing.md),
        itemBuilder: (context, index) {
          final order = orderProvider.customerOrders[index];
          return _buildCustomerOrderCard(context, order);
        },
      ),
    );
  }

  Widget _buildCustomerOrderCard(BuildContext context, CustomerOrder order) {
    return InkWell(
      onTap: () => _showCustomerOrderDetailSheet(context, order),
      borderRadius: BorderRadius.circular(AppRadius.lg),
      child: Container(
        padding: const EdgeInsets.all(AppSpacing.lg),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(AppRadius.lg),
          border: Border.all(color: AppColors.slate200.withValues(alpha: 0.8)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.02),
              blurRadius: 6,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: AppColors.slate100,
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(
                    order.orderNumber,
                    style: const TextStyle(
                      fontFamily: 'monospace',
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                      color: AppColors.ink,
                    ),
                  ),
                ),
                StatusBadge(status: order.status),
              ],
            ),
            const SizedBox(height: AppSpacing.md),
            Row(
              children: [
                Container(
                  width: 46,
                  height: 46,
                  decoration: BoxDecoration(
                    color: AppColors.brand50,
                    borderRadius: BorderRadius.circular(AppRadius.sm),
                    border: Border.all(color: AppColors.brand200),
                  ),
                  child: const Icon(
                    Icons.shopping_bag_rounded,
                    size: 24,
                    color: AppColors.ink,
                  ),
                ),
                const SizedBox(width: AppSpacing.md),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        order.itemSummary,
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          color: AppColors.ink,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 3),
                      Text(
                        'Sold by: ${order.vendorName}',
                        style: const TextStyle(
                          fontSize: 11,
                          color: AppColors.slate500,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: AppSpacing.sm),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      '${_currencyFormat.format(order.total)} QAR',
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w800,
                        color: AppColors.ink,
                      ),
                    ),
                    const SizedBox(height: 3),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1.5),
                      decoration: BoxDecoration(
                        color: AppColors.brandYellow,
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        order.paymentMethod ?? 'COD',
                        style: const TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w800,
                          color: AppColors.ink,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
            const SizedBox(height: AppSpacing.sm),
            const Divider(height: 1),
            const SizedBox(height: AppSpacing.xs),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  order.createdAt != null
                      ? DateFormat('MMM d, yyyy · h:mm a').format(DateTime.parse(order.createdAt!))
                      : '',
                  style: const TextStyle(fontSize: 11, color: AppColors.slate400),
                ),
                const Row(
                  children: [
                    Text(
                      'View Details',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        color: AppColors.ink,
                      ),
                    ),
                    SizedBox(width: 2),
                    Icon(Icons.chevron_right_rounded, size: 16, color: AppColors.ink),
                  ],
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  void _showCustomerOrderDetailSheet(BuildContext context, CustomerOrder order) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => Container(
        padding: const EdgeInsets.all(AppSpacing.xl),
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(AppRadius.xl)),
        ),
        child: SafeArea(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: AppColors.slate300,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: AppSpacing.lg),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    order.orderNumber,
                    style: const TextStyle(
                      fontFamily: 'monospace',
                      fontSize: 16,
                      fontWeight: FontWeight.w800,
                      color: AppColors.ink,
                    ),
                  ),
                  StatusBadge(status: order.status),
                ],
              ),
              const SizedBox(height: 4),
              Text(
                'Placed on ${order.createdAt != null ? DateFormat('MMMM d, yyyy · h:mm a').format(DateTime.parse(order.createdAt!)) : ''}',
                style: const TextStyle(fontSize: 12, color: AppColors.slate500),
              ),
              const Divider(height: AppSpacing.xl),
              const Text('Items Ordered', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: AppColors.ink)),
              const SizedBox(height: AppSpacing.sm),
              ...order.items.map(
                (item) => Padding(
                  padding: const EdgeInsets.symmetric(vertical: 4),
                  child: Row(
                    children: [
                      Container(
                        width: 24,
                        height: 24,
                        decoration: BoxDecoration(
                          color: AppColors.brandYellow,
                          borderRadius: BorderRadius.circular(4),
                        ),
                        alignment: Alignment.center,
                        child: Text(
                          '${item.quantity}x',
                          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.ink),
                        ),
                      ),
                      const SizedBox(width: AppSpacing.sm),
                      Expanded(
                        child: Text(
                          item.title,
                          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.ink),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const Divider(height: AppSpacing.xl),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Total Amount', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 15, color: AppColors.ink)),
                  Text(
                    '${_currencyFormat.format(order.total)} QAR',
                    style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 17, color: AppColors.ink),
                  ),
                ],
              ),
              if (order.customerName != null) ...[
                const SizedBox(height: AppSpacing.sm),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Deliver to', style: TextStyle(fontSize: 12, color: AppColors.slate500)),
                    Text(
                      '${order.customerName} (${order.customerPhone ?? ''})',
                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.ink),
                    ),
                  ],
                ),
              ],
              const SizedBox(height: AppSpacing.xl),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSalesView(SellerOrderProvider orderProvider) {
    return Column(
      children: [
        // 1. Search Bar
        Padding(
          padding: const EdgeInsets.symmetric(
            horizontal: AppSpacing.lg,
            vertical: AppSpacing.sm,
          ),
          child: TextField(
            controller: _searchController,
            onSubmitted: (val) => orderProvider.searchOrders(val),
            decoration: InputDecoration(
              hintText: 'Search #order, customer, product...',
              prefixIcon: const Icon(
                Icons.search,
                size: 20,
                color: AppColors.slate400,
              ),
              suffixIcon: _searchController.text.isNotEmpty
                  ? IconButton(
                      icon: const Icon(
                        Icons.clear,
                        size: 18,
                        color: AppColors.slate400,
                      ),
                      onPressed: () {
                        _searchController.clear();
                        orderProvider.searchOrders('');
                      },
                    )
                  : null,
              contentPadding: const EdgeInsets.symmetric(
                horizontal: 14,
                vertical: 10,
              ),
              fillColor: AppColors.surface,
            ),
          ),
        ),

        // 2. Horizontal Status Tabs with Counts
        SizedBox(
          height: 44,
          child: ListView.separated(
            padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
            scrollDirection: Axis.horizontal,
            itemCount: _statusTabs.length,
            separatorBuilder: (_, _) => const SizedBox(width: 8),
            itemBuilder: (context, index) {
              final tab = _statusTabs[index];
              final key = tab['key']!;
              final label = tab['label']!;
              final isSelected = orderProvider.selectedStatus == key;
              final count = orderProvider.counts[key] ?? 0;

              return GestureDetector(
                onTap: () => orderProvider.setStatus(key),
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 14,
                    vertical: 8,
                  ),
                  decoration: BoxDecoration(
                    color: isSelected ? AppColors.brandYellow : AppColors.surface,
                    borderRadius: BorderRadius.circular(AppRadius.md),
                    border: Border.all(
                      color: isSelected
                          ? AppColors.brandYellowDark
                          : AppColors.slate200,
                    ),
                  ),
                  alignment: Alignment.center,
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        label,
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w700,
                          color: isSelected ? AppColors.ink : AppColors.slate700,
                        ),
                      ),
                      if (count > 0) ...[
                        const SizedBox(width: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 6,
                            vertical: 2,
                          ),
                          decoration: BoxDecoration(
                            color: isSelected
                                ? AppColors.ink.withValues(alpha: 0.12)
                                : AppColors.slate100,
                            borderRadius: BorderRadius.circular(AppRadius.sm),
                          ),
                          child: Text(
                            '$count',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w700,
                              color: isSelected
                                  ? AppColors.ink
                                  : AppColors.slate600,
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              );
            },
          ),
        ),
        const SizedBox(height: AppSpacing.sm),

        // 3. Orders List View
        Expanded(
          child: RefreshIndicator(
            onRefresh: () => orderProvider.fetchOrders(),
            color: AppColors.brand500,
            child: orderProvider.isLoading && orderProvider.orders.isEmpty
                ? const LoadingView(message: 'Loading orders...')
                : orderProvider.orders.isEmpty
                ? const EmptyView(
                    icon: Icons.receipt_long_outlined,
                    title: 'No Orders Found',
                    subtitle: 'Orders matching your filter will appear here.',
                  )
                : ListView.separated(
                    padding: const EdgeInsets.all(AppSpacing.lg),
                    itemCount: orderProvider.orders.length,
                    separatorBuilder: (_, _) =>
                        const SizedBox(height: AppSpacing.md),
                    itemBuilder: (context, index) {
                      final order = orderProvider.orders[index];
                      return _buildOrderCard(context, order);
                    },
                  ),
          ),
        ),
      ],
    );
  }

  Widget _buildOrderCard(BuildContext context, dynamic order) {
    return InkWell(
      onTap: () {
        Navigator.of(context).push(
          MaterialPageRoute(
            builder: (_) => OrderDetailScreen(vendorOrderId: order.id),
          ),
        );
      },
      borderRadius: BorderRadius.circular(AppRadius.lg),
      child: Container(
        padding: const EdgeInsets.all(AppSpacing.lg),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(AppRadius.lg),
          border: Border.all(color: AppColors.slate200.withValues(alpha: 0.8)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.02),
              blurRadius: 6,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 3,
                  ),
                  decoration: BoxDecoration(
                    color: AppColors.slate100,
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(
                    order.orderNumber,
                    style: const TextStyle(
                      fontFamily: 'monospace',
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                      color: AppColors.ink,
                    ),
                  ),
                ),
                StatusBadge(status: order.status),
              ],
            ),
            const SizedBox(height: AppSpacing.md),
            Row(
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: AppColors.slate100,
                    borderRadius: BorderRadius.circular(AppRadius.sm),
                  ),
                  clipBehavior: Clip.antiAlias,
                  child: order.thumbnail != null
                      ? Image.network(
                          order.thumbnail!,
                          fit: BoxFit.cover,
                          errorBuilder: (_, _, _) => const Icon(
                            Icons.inventory_2,
                            size: 22,
                            color: AppColors.slate400,
                          ),
                        )
                      : const Icon(
                          Icons.inventory_2,
                          size: 22,
                          color: AppColors.slate400,
                        ),
                ),
                const SizedBox(width: AppSpacing.md),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        order.customerName,
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          color: AppColors.ink,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        order.itemSummary,
                        style: const TextStyle(
                          fontSize: 12,
                          color: AppColors.slate500,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      '${_currencyFormat.format(order.total)} QAR',
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w800,
                        color: AppColors.brand600,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      order.createdAt != null
                          ? DateFormat('MMM d, yyyy')
                                .format(DateTime.parse(order.createdAt))
                          : '',
                      style: const TextStyle(
                        fontSize: 11,
                        color: AppColors.slate400,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
