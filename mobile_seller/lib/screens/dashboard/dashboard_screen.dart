import 'package:fl_chart/fl_chart.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/loading_view.dart';
import '../../core/widgets/stat_card.dart';
import '../../core/widgets/status_badge.dart';
import '../../providers/seller_auth_provider.dart';
import '../../providers/seller_dashboard_provider.dart';
import '../orders/order_detail_screen.dart';
import '../products/add_product_screen.dart';

class DashboardScreen extends StatefulWidget {
  final void Function(int tabIndex)? onNavigateTab;

  const DashboardScreen({super.key, this.onNavigateTab});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final _currencyFormat = NumberFormat('#,##0.00', 'en_US');

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<SellerDashboardProvider>().fetchDashboard();
    });
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<SellerAuthProvider>();
    final dashboardProvider = context.watch<SellerDashboardProvider>();
    final user = authProvider.user;
    final metrics = dashboardProvider.metrics;

    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        title: Row(
          children: [
            Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                color: AppColors.brand100,
                borderRadius: BorderRadius.circular(AppRadius.sm),
              ),
              child: user?.logoUrl != null
                  ? ClipRRect(
                      borderRadius: BorderRadius.circular(AppRadius.sm),
                      child: Image.network(
                        user!.logoUrl!,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => const Icon(Icons.storefront, color: AppColors.brand600, size: 20),
                      ),
                    )
                  : const Icon(Icons.storefront, color: AppColors.brand600, size: 20),
            ),
            const SizedBox(width: AppSpacing.sm),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    user?.storeName ?? 'Seller Portal',
                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  Text(
                    user?.roleLabel ?? 'Store Owner',
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w500, color: AppColors.slate400),
                  ),
                ],
              ),
            ),
          ],
        ),
        actions: [
          if (user != null)
            Container(
              margin: const EdgeInsets.only(right: AppSpacing.lg),
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(
                color: AppColors.brand50,
                borderRadius: BorderRadius.circular(AppRadius.pill),
                border: Border.all(color: AppColors.brand200),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.bolt_rounded, size: 14, color: AppColors.brand600),
                  const SizedBox(width: 4),
                  Text(
                    '${user.listingCredits} Credits',
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: AppColors.brand700,
                    ),
                  ),
                ],
              ),
            ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          await context.read<SellerDashboardProvider>().fetchDashboard();
          await context.read<SellerAuthProvider>().refreshProfile();
        },
        color: AppColors.brand500,
        child: dashboardProvider.isLoading && metrics == null
            ? const LoadingView(message: 'Loading dashboard analytics...')
            : SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(AppSpacing.lg),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // 1. 4-Stat Cards Grid (2x2)
                    _buildStatsGrid(metrics),
                    const SizedBox(height: AppSpacing.xl),

                    // 2. Sales Trend Line Chart
                    _buildSalesChartCard(dashboardProvider),
                    const SizedBox(height: AppSpacing.xl),

                    // 3. Quick Action Row
                    _buildQuickActionsRow(context),
                    const SizedBox(height: AppSpacing.xl),

                    // 4. Top Performing Products
                    if (dashboardProvider.topProducts.isNotEmpty) ...[
                      _buildTopProductsSection(dashboardProvider),
                      const SizedBox(height: AppSpacing.xl),
                    ],

                    // 5. Recent Orders
                    _buildRecentOrdersSection(context, dashboardProvider),
                    const SizedBox(height: AppSpacing.xxl),
                  ],
                ),
              ),
      ),
    );
  }

  Widget _buildStatsGrid(dynamic metrics) {
    final revenue = metrics?.periodRevenue != null ? _currencyFormat.format(metrics.periodRevenue) : '0.00';
    final orders = metrics?.periodOrders?.toString() ?? '0';
    final activeProducts = metrics?.activeProducts?.toString() ?? '0';
    final views = metrics?.totalViews != null ? _formatViews(metrics.totalViews) : '0';

    final revPct = metrics?.revenueChangePct != null
        ? '${metrics.revenueChangePct >= 0 ? '+' : ''}${metrics.revenueChangePct}%'
        : null;
    final ordersPct = metrics?.ordersChangePct != null
        ? '${metrics.ordersChangePct >= 0 ? '+' : ''}${metrics.ordersChangePct}%'
        : null;

    return Column(
      children: [
        Row(
          children: [
            Expanded(
              child: StatCard(
                label: 'Revenue',
                value: '$revenue QAR',
                changePct: revPct,
                isPositiveChange: (metrics?.revenueChangePct ?? 0) >= 0,
                icon: Icons.payments_outlined,
                iconColor: AppColors.brand600,
                iconBgColor: AppColors.brand50,
              ),
            ),
            const SizedBox(width: AppSpacing.md),
            Expanded(
              child: StatCard(
                label: 'Orders',
                value: orders,
                changePct: ordersPct,
                isPositiveChange: (metrics?.ordersChangePct ?? 0) >= 0,
                icon: Icons.shopping_bag_outlined,
                iconColor: AppColors.brand600,
                iconBgColor: AppColors.brand50,
              ),
            ),
          ],
        ),
        const SizedBox(height: AppSpacing.md),
        Row(
          children: [
            Expanded(
              child: StatCard(
                label: 'Active Listings',
                value: activeProducts,
                icon: Icons.inventory_2_outlined,
                iconColor: AppColors.brand600,
                iconBgColor: AppColors.brand50,
              ),
            ),
            const SizedBox(width: AppSpacing.md),
            Expanded(
              child: StatCard(
                label: 'Total Views',
                value: views,
                icon: Icons.visibility_outlined,
                iconColor: AppColors.brand600,
                iconBgColor: AppColors.brand50,
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildSalesChartCard(SellerDashboardProvider provider) {
    final chartData = provider.chartData;
    final hasData = chartData != null && chartData.revenue.isNotEmpty;

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
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Sales Overview',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w700,
                  color: AppColors.ink,
                ),
              ),
              Row(
                children: [
                  _buildPeriodPill(provider, 7, '7 Days'),
                  const SizedBox(width: 6),
                  _buildPeriodPill(provider, 30, '30 Days'),
                ],
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.lg),
          SizedBox(
            height: 180,
            child: hasData
                ? LineChart(
                    LineChartData(
                      gridData: FlGridData(
                        show: true,
                        drawVerticalLine: false,
                        horizontalInterval: _calcInterval(chartData.revenue),
                        getDrawingHorizontalLine: (value) => FlLine(
                          color: AppColors.slate100,
                          strokeWidth: 1,
                        ),
                      ),
                      titlesData: FlTitlesData(
                        show: true,
                        rightTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                        topTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                        leftTitles: AxisTitles(
                          sideTitles: SideTitles(
                            showTitles: true,
                            reservedSize: 42,
                            getTitlesWidget: (value, meta) {
                              if (value == meta.max || value == meta.min) return const SizedBox.shrink();
                              return Text(
                                _compactCurrency(value),
                                style: const TextStyle(fontSize: 10, color: AppColors.slate400),
                              );
                            },
                          ),
                        ),
                        bottomTitles: AxisTitles(
                          sideTitles: SideTitles(
                            showTitles: true,
                            reservedSize: 22,
                            interval: (chartData.labels.length / 4).ceilToDouble().clamp(1.0, 30.0),
                            getTitlesWidget: (value, meta) {
                              final index = value.toInt();
                              if (index >= 0 && index < chartData.labels.length) {
                                return Padding(
                                  padding: const EdgeInsets.only(top: 4),
                                  child: Text(
                                    chartData.labels[index],
                                    style: const TextStyle(fontSize: 10, color: AppColors.slate400),
                                  ),
                                );
                              }
                              return const SizedBox.shrink();
                            },
                          ),
                        ),
                      ),
                      borderData: FlBorderData(show: false),
                      lineBarsData: [
                        LineChartBarData(
                          spots: chartData.revenue.asMap().entries.map((e) {
                            return FlSpot(e.key.toDouble(), e.value);
                          }).toList(),
                          isCurved: true,
                          curveSmoothness: 0.35,
                          color: const Color(0xFF7C83D4),
                          barWidth: 2.5,
                          isStrokeCapRound: true,
                          dotData: const FlDotData(show: false),
                          belowBarData: BarAreaData(
                            show: true,
                            color: const Color(0xFF7C83D4).withOpacity(0.12),
                          ),
                        ),
                      ],
                    ),
                  )
                : const Center(
                    child: Text(
                      'No sales recorded in this period',
                      style: TextStyle(color: AppColors.slate400, fontSize: 13),
                    ),
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildPeriodPill(SellerDashboardProvider provider, int days, String label) {
    final isSelected = provider.selectedDays == days;
    return GestureDetector(
      onTap: () => provider.setDays(days),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.brand500 : AppColors.slate100,
          borderRadius: BorderRadius.circular(AppRadius.pill),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w600,
            color: isSelected ? Colors.white : AppColors.slate600,
          ),
        ),
      ),
    );
  }

  Widget _buildQuickActionsRow(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: _buildActionBtn(
            icon: Icons.add_circle_outline_rounded,
            label: 'Add Product',
            color: AppColors.brand500,
            onTap: () {
              Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const AddProductScreen()),
              );
            },
          ),
        ),
        const SizedBox(width: AppSpacing.md),
        Expanded(
          child: _buildActionBtn(
            icon: Icons.receipt_long_outlined,
            label: 'All Orders',
            color: AppColors.info,
            onTap: () => widget.onNavigateTab?.call(1),
          ),
        ),
        const SizedBox(width: AppSpacing.md),
        Expanded(
          child: _buildActionBtn(
            icon: Icons.account_balance_wallet_outlined,
            label: 'Payouts',
            color: AppColors.success,
            onTap: () => widget.onNavigateTab?.call(3),
          ),
        ),
      ],
    );
  }

  Widget _buildActionBtn({
    required IconData icon,
    required String label,
    required Color color,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(AppRadius.md),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: AppSpacing.md, horizontal: 8),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(AppRadius.md),
          border: Border.all(color: AppColors.slate200),
        ),
        child: Column(
          children: [
            Icon(icon, size: 22, color: color),
            const SizedBox(height: 6),
            Text(
              label,
              style: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: AppColors.ink,
              ),
              textAlign: TextAlign.center,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTopProductsSection(SellerDashboardProvider provider) {
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
            'Top Performing Products',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w700,
              color: AppColors.ink,
            ),
          ),
          const SizedBox(height: AppSpacing.md),
          ...provider.topProducts.asMap().entries.map((entry) {
            final index = entry.key + 1;
            final product = entry.value;

            return Padding(
              padding: const EdgeInsets.only(bottom: AppSpacing.md),
              child: Row(
                children: [
                  Container(
                    width: 24,
                    height: 24,
                    decoration: BoxDecoration(
                      color: index == 1 ? AppColors.brand100 : AppColors.slate100,
                      shape: BoxShape.circle,
                    ),
                    alignment: Alignment.center,
                    child: Text(
                      '$index',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        color: index == 1 ? AppColors.brand700 : AppColors.slate600,
                      ),
                    ),
                  ),
                  const SizedBox(width: AppSpacing.sm),
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: AppColors.slate100,
                      borderRadius: BorderRadius.circular(AppRadius.sm),
                    ),
                    clipBehavior: Clip.antiAlias,
                    child: product.thumbnail != null
                        ? Image.network(
                            product.thumbnail!,
                            fit: BoxFit.cover,
                            errorBuilder: (_, __, ___) => const Icon(Icons.image, size: 20, color: AppColors.slate400),
                          )
                        : const Icon(Icons.image, size: 20, color: AppColors.slate400),
                  ),
                  const SizedBox(width: AppSpacing.md),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          product.title,
                          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.ink),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        Text(
                          '${product.unitsSold} units sold',
                          style: const TextStyle(fontSize: 11, color: AppColors.slate400),
                        ),
                      ],
                    ),
                  ),
                  Text(
                    '${_currencyFormat.format(product.revenue)} QAR',
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

  Widget _buildRecentOrdersSection(BuildContext context, SellerDashboardProvider provider) {
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
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Recent Orders',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w700,
                  color: AppColors.ink,
                ),
              ),
              TextButton(
                onPressed: () => widget.onNavigateTab?.call(1),
                style: TextButton.styleFrom(
                  padding: EdgeInsets.zero,
                  minimumSize: Size.zero,
                  tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                ),
                child: const Text(
                  'View All',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: AppColors.brand600,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.md),
          if (provider.recentOrders.isEmpty)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 24),
              child: Center(
                child: Text('No orders received yet', style: TextStyle(color: AppColors.slate400, fontSize: 13)),
              ),
            )
          else
            ...provider.recentOrders.map((order) {
              return InkWell(
                onTap: () {
                  Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => OrderDetailScreen(vendorOrderId: order.id),
                    ),
                  );
                },
                child: Padding(
                  padding: const EdgeInsets.symmetric(vertical: 10),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: AppColors.slate100,
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          order.orderNumber,
                          style: const TextStyle(
                            fontFamily: 'monospace',
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: AppColors.slate600,
                          ),
                        ),
                      ),
                      const SizedBox(width: AppSpacing.md),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              order.customerName,
                              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.ink),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                            Text(
                              '${order.itemsCount} ${order.itemsCount == 1 ? 'item' : 'items'}',
                              style: const TextStyle(fontSize: 11, color: AppColors.slate400),
                            ),
                          ],
                        ),
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          StatusBadge(status: order.status, isSmall: true),
                          const SizedBox(height: 4),
                          Text(
                            '${_currencyFormat.format(order.total)} QAR',
                            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.ink),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              );
            }),
        ],
      ),
    );
  }

  String _formatViews(int views) {
    if (views >= 1000) {
      return '${(views / 1000).toStringAsFixed(1)}K';
    }
    return views.toString();
  }

  double _calcInterval(List<double> values) {
    if (values.isEmpty) return 1000.0;
    final max = values.reduce((a, b) => a > b ? a : b);
    if (max <= 0) return 500.0;
    return (max / 4).clamp(100.0, 50000.0);
  }

  String _compactCurrency(double val) {
    if (val >= 1000) {
      return '${(val / 1000).toStringAsFixed(0)}K';
    }
    return val.toInt().toString();
  }
}
