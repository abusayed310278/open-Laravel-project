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
      appBar: AppBar(
        backgroundColor: AppColors.surface,
        foregroundColor: AppColors.ink,
        elevation: 0,
        scrolledUnderElevation: 1,
        surfaceTintColor: Colors.transparent,
        shadowColor: AppColors.slate900.withValues(alpha: 0.06),
        title: const Text(
          'Orders',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w800,
            color: AppColors.ink,
            letterSpacing: -0.2,
          ),
        ),
      ),
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

  // Shared card chrome for both purchase and sale order cards.
  static BoxDecoration get _cardDecoration => BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.slate200.withValues(alpha: 0.7)),
        boxShadow: [
          BoxShadow(
            color: AppColors.slate900.withValues(alpha: 0.04),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      );

  // Money display with a de-emphasized currency suffix.
  Widget _buildAmount(double amount, {double fontSize = 14, Color color = AppColors.ink}) {
    return RichText(
      text: TextSpan(
        children: [
          TextSpan(
            text: _currencyFormat.format(amount),
            style: TextStyle(fontSize: fontSize, fontWeight: FontWeight.w800, color: color),
          ),
          TextSpan(
            text: ' QAR',
            style: TextStyle(
              fontSize: fontSize - 3,
              fontWeight: FontWeight.w600,
              color: color.withValues(alpha: 0.55),
            ),
          ),
        ],
      ),
    );
  }

  Widget _sectionLabel(String text) {
    return Text(
      text.toUpperCase(),
      style: const TextStyle(
        fontSize: 11,
        fontWeight: FontWeight.w700,
        color: AppColors.slate500,
        letterSpacing: 0.5,
      ),
    );
  }

  Widget _buildBody(SellerOrderProvider orderProvider) {
    return Column(
      children: [
        // Top Segmented Mode Selector: My Purchases vs Store Sales
        Container(
          margin: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.md, AppSpacing.lg, AppSpacing.sm),
          padding: const EdgeInsets.all(4),
          height: 48,
          decoration: BoxDecoration(
            color: AppColors.slate200.withValues(alpha: 0.55),
            borderRadius: BorderRadius.circular(AppRadius.md),
          ),
          child: Row(
            children: [
              Expanded(
                child: _buildModeSegment(
                  icon: Icons.shopping_bag_outlined,
                  label: 'My Purchases',
                  count: orderProvider.customerOrders.length,
                  isSelected: orderProvider.viewMode == OrderViewMode.purchases,
                  onTap: () => orderProvider.setViewMode(OrderViewMode.purchases),
                ),
              ),
              Expanded(
                child: _buildModeSegment(
                  icon: Icons.storefront_outlined,
                  label: 'Store Sales',
                  count: orderProvider.counts['all'] ?? 0,
                  isSelected: orderProvider.viewMode == OrderViewMode.sales,
                  onTap: () => orderProvider.setViewMode(OrderViewMode.sales),
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

  Widget _buildModeSegment({
    required IconData icon,
    required String label,
    required int count,
    required bool isSelected,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(AppRadius.sm),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        curve: Curves.easeOut,
        decoration: BoxDecoration(
          color: isSelected ? AppColors.brandYellow : Colors.transparent,
          borderRadius: BorderRadius.circular(AppRadius.sm),
          boxShadow: isSelected
              ? [
                  BoxShadow(
                    color: AppColors.brandYellowDark.withValues(alpha: 0.35),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                  ),
                ]
              : null,
        ),
        alignment: Alignment.center,
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              icon,
              size: 15,
              color: isSelected ? AppColors.ink : AppColors.slate600,
            ),
            const SizedBox(width: 6),
            Text(
              '$label ($count)',
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w700,
                color: isSelected ? AppColors.ink : AppColors.slate600,
              ),
            ),
          ],
        ),
      ),
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
        decoration: _cardDecoration,
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
                      color: AppColors.slate700,
                      letterSpacing: 0.2,
                    ),
                  ),
                ),
                StatusBadge(status: order.status),
              ],
            ),
            const SizedBox(height: AppSpacing.md),
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 48,
                  height: 48,
                  decoration: BoxDecoration(
                    color: AppColors.brand50,
                    borderRadius: BorderRadius.circular(AppRadius.sm),
                    border: Border.all(color: AppColors.brand200),
                  ),
                  alignment: Alignment.center,
                  child: const Icon(
                    Icons.shopping_bag_rounded,
                    size: 22,
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
                          height: 1.25,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Sold by ${order.vendorName}',
                        style: const TextStyle(fontSize: 11.5, color: AppColors.slate500),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: AppSpacing.sm),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    _buildAmount(order.total),
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppColors.brandYellow,
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        order.paymentMethod ?? 'COD',
                        style: const TextStyle(
                          fontSize: 9.5,
                          fontWeight: FontWeight.w800,
                          color: AppColors.ink,
                          letterSpacing: 0.2,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
            const SizedBox(height: AppSpacing.md),
            Divider(height: 1, color: AppColors.slate200.withValues(alpha: 0.8)),
            const SizedBox(height: AppSpacing.sm),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  order.createdAt != null
                      ? DateFormat('MMM d, yyyy · h:mm a').format(DateTime.parse(order.createdAt!))
                      : '',
                  style: const TextStyle(fontSize: 11.5, color: AppColors.slate400),
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
        padding: const EdgeInsets.fromLTRB(AppSpacing.xl, AppSpacing.md, AppSpacing.xl, AppSpacing.xl),
        decoration: const BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.vertical(top: Radius.circular(AppRadius.xl)),
        ),
        child: SafeArea(
          top: false,
          child: SingleChildScrollView(
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
                        letterSpacing: 0.2,
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
                const SizedBox(height: AppSpacing.xl),
                _sectionLabel('Items Ordered'),
                const SizedBox(height: AppSpacing.sm),
                ...order.items.map(
                  (item) => Padding(
                    padding: const EdgeInsets.symmetric(vertical: 6),
                    child: Row(
                      children: [
                        Container(
                          width: 26,
                          height: 26,
                          decoration: BoxDecoration(
                            color: AppColors.brandYellow,
                            borderRadius: BorderRadius.circular(6),
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
                            style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w600, color: AppColors.ink),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: AppSpacing.lg),
                Container(
                  padding: const EdgeInsets.all(AppSpacing.md),
                  decoration: BoxDecoration(
                    color: AppColors.slate50,
                    borderRadius: BorderRadius.circular(AppRadius.md),
                  ),
                  child: Column(
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Total Amount',
                            style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: AppColors.ink),
                          ),
                          _buildAmount(order.total, fontSize: 17),
                        ],
                      ),
                      if (order.customerName != null) ...[
                        const SizedBox(height: AppSpacing.sm),
                        Divider(height: 1, color: AppColors.slate200.withValues(alpha: 0.8)),
                        const SizedBox(height: AppSpacing.sm),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text('Deliver to', style: TextStyle(fontSize: 12.5, color: AppColors.slate500)),
                            Text(
                              '${order.customerName} (${order.customerPhone ?? ''})',
                              style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: AppColors.ink),
                            ),
                          ],
                        ),
                      ],
                    ],
                  ),
                ),
              ],
            ),
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
          padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.sm),
          child: TextField(
            controller: _searchController,
            onSubmitted: (val) => orderProvider.searchOrders(val),
            style: const TextStyle(fontSize: 14, color: AppColors.ink),
            decoration: InputDecoration(
              hintText: 'Search #order, customer, product...',
              hintStyle: const TextStyle(fontSize: 13.5, color: AppColors.slate400),
              prefixIcon: const Icon(Icons.search, size: 20, color: AppColors.slate400),
              suffixIcon: _searchController.text.isNotEmpty
                  ? IconButton(
                      icon: const Icon(Icons.clear, size: 18, color: AppColors.slate400),
                      onPressed: () {
                        _searchController.clear();
                        orderProvider.searchOrders('');
                      },
                    )
                  : null,
              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              filled: true,
              fillColor: AppColors.surface,
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(AppRadius.md),
                borderSide: BorderSide(color: AppColors.slate200.withValues(alpha: 0.9)),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(AppRadius.md),
                borderSide: BorderSide(color: AppColors.slate200.withValues(alpha: 0.9)),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(AppRadius.md),
                borderSide: const BorderSide(color: AppColors.brandYellowDark, width: 1.4),
              ),
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
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 150),
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  decoration: BoxDecoration(
                    color: isSelected ? AppColors.brandYellow : AppColors.surface,
                    borderRadius: BorderRadius.circular(AppRadius.md),
                    border: Border.all(
                      color: isSelected ? AppColors.brandYellowDark : AppColors.slate200,
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
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: isSelected ? AppColors.ink.withValues(alpha: 0.12) : AppColors.slate100,
                            borderRadius: BorderRadius.circular(AppRadius.sm),
                          ),
                          child: Text(
                            '$count',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w700,
                              color: isSelected ? AppColors.ink : AppColors.slate600,
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
            color: AppColors.brandYellowDark,
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
                    separatorBuilder: (_, _) => const SizedBox(height: AppSpacing.md),
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
        decoration: _cardDecoration,
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
                      color: AppColors.slate700,
                      letterSpacing: 0.2,
                    ),
                  ),
                ),
                StatusBadge(status: order.status),
              ],
            ),
            const SizedBox(height: AppSpacing.md),
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 48,
                  height: 48,
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
                      : const Icon(Icons.inventory_2, size: 22, color: AppColors.slate400),
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
                      const SizedBox(height: 3),
                      Text(
                        order.itemSummary,
                        style: const TextStyle(fontSize: 12, color: AppColors.slate500),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: AppSpacing.sm),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    _buildAmount(order.total),
                    const SizedBox(height: 3),
                    Text(
                      order.createdAt != null
                          ? DateFormat('MMM d, yyyy').format(DateTime.parse(order.createdAt))
                          : '',
                      style: const TextStyle(fontSize: 11, color: AppColors.slate400),
                    ),
                  ],
                ),
              ],
            ),
            const SizedBox(height: AppSpacing.md),
            Divider(height: 1, color: AppColors.slate200.withValues(alpha: 0.8)),
            const SizedBox(height: AppSpacing.sm),
            const Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                Text(
                  'View Order',
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.ink),
                ),
                SizedBox(width: 2),
                Icon(Icons.chevron_right_rounded, size: 16, color: AppColors.ink),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
