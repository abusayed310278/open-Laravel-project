class DashboardMetrics {
  final double totalRevenue;
  final double periodRevenue;
  final double revenueChangePct;
  final int totalOrders;
  final int periodOrders;
  final double ordersChangePct;
  final int activeProducts;
  final int totalProducts;
  final int totalViews;
  final int listingCredits;
  final String currency;

  const DashboardMetrics({
    required this.totalRevenue,
    required this.periodRevenue,
    required this.revenueChangePct,
    required this.totalOrders,
    required this.periodOrders,
    required this.ordersChangePct,
    required this.activeProducts,
    required this.totalProducts,
    required this.totalViews,
    required this.listingCredits,
    this.currency = 'QAR',
  });

  factory DashboardMetrics.fromJson(Map<String, dynamic> json) {
    return DashboardMetrics(
      totalRevenue: (json['total_revenue'] is num) ? (json['total_revenue'] as num).toDouble() : 0.0,
      periodRevenue: (json['period_revenue'] is num) ? (json['period_revenue'] as num).toDouble() : 0.0,
      revenueChangePct: (json['revenue_change_pct'] is num) ? (json['revenue_change_pct'] as num).toDouble() : 0.0,
      totalOrders: json['total_orders'] is int ? json['total_orders'] : 0,
      periodOrders: json['period_orders'] is int ? json['period_orders'] : 0,
      ordersChangePct: (json['orders_change_pct'] is num) ? (json['orders_change_pct'] as num).toDouble() : 0.0,
      activeProducts: json['active_products'] is int ? json['active_products'] : 0,
      totalProducts: json['total_products'] is int ? json['total_products'] : 0,
      totalViews: json['total_views'] is int ? json['total_views'] : 0,
      listingCredits: json['listing_credits'] is int ? json['listing_credits'] : 0,
      currency: json['currency']?.toString() ?? 'QAR',
    );
  }
}

class OrderCounts {
  final int toProcess;
  final int shipping;
  final int toReview;

  const OrderCounts({this.toProcess = 0, this.shipping = 0, this.toReview = 0});

  factory OrderCounts.fromJson(Map<String, dynamic> json) {
    int read(String key) => json[key] is int ? json[key] as int : int.tryParse(json[key]?.toString() ?? '') ?? 0;

    return OrderCounts(
      toProcess: read('to_process'),
      shipping: read('shipping'),
      toReview: read('to_review'),
    );
  }
}

class ChartData {
  final List<String> labels;
  final List<double> revenue;
  final List<int> orders;

  const ChartData({
    required this.labels,
    required this.revenue,
    required this.orders,
  });

  factory ChartData.fromJson(Map<String, dynamic> json) {
    final labelsList = (json['labels'] as List?)?.map((e) => e.toString()).toList() ?? [];
    final revenueList = (json['revenue'] as List?)
            ?.map((e) => (e is num) ? e.toDouble() : double.tryParse(e.toString()) ?? 0.0)
            .toList() ??
        [];
    final ordersList = (json['orders'] as List?)
            ?.map((e) => (e is int) ? e : int.tryParse(e.toString()) ?? 0)
            .toList() ??
        [];

    return ChartData(
      labels: labelsList,
      revenue: revenueList,
      orders: ordersList,
    );
  }
}

class TopProductItem {
  final int? productId;
  final String title;
  final String? thumbnail;
  final int unitsSold;
  final double revenue;
  final String currency;

  const TopProductItem({
    this.productId,
    required this.title,
    this.thumbnail,
    required this.unitsSold,
    required this.revenue,
    this.currency = 'QAR',
  });

  factory TopProductItem.fromJson(Map<String, dynamic> json) {
    return TopProductItem(
      productId: json['product_id'] as int?,
      title: json['title']?.toString() ?? '',
      thumbnail: json['thumbnail']?.toString(),
      unitsSold: json['units_sold'] is int ? json['units_sold'] : 0,
      revenue: (json['revenue'] is num) ? (json['revenue'] as num).toDouble() : 0.0,
      currency: json['currency']?.toString() ?? 'QAR',
    );
  }
}

class RecentOrderItem {
  final int id;
  final String orderNumber;
  final String customerName;
  final String? customerEmail;
  final String status;
  final String statusLabel;
  final double total;
  final String currency;
  final int itemsCount;
  final String? createdAt;

  const RecentOrderItem({
    required this.id,
    required this.orderNumber,
    required this.customerName,
    this.customerEmail,
    required this.status,
    required this.statusLabel,
    required this.total,
    this.currency = 'QAR',
    required this.itemsCount,
    this.createdAt,
  });

  factory RecentOrderItem.fromJson(Map<String, dynamic> json) {
    return RecentOrderItem(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      orderNumber: json['order_number']?.toString() ?? '#${json['id']}',
      customerName: json['customer_name']?.toString() ?? 'Customer',
      customerEmail: json['customer_email']?.toString(),
      status: json['status']?.toString() ?? 'pending',
      statusLabel: json['status_label']?.toString() ?? 'Pending',
      total: (json['total'] is num) ? (json['total'] as num).toDouble() : 0.0,
      currency: json['currency']?.toString() ?? 'QAR',
      itemsCount: json['items_count'] is int ? json['items_count'] : 1,
      createdAt: json['created_at']?.toString(),
    );
  }
}
