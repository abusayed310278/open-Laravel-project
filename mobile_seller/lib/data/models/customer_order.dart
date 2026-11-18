import '../../core/network/api_config.dart';

class CustomerOrder {
  final int id;
  final String orderNumber;
  final String status;
  final String statusLabel;
  final double subtotal;
  final double shippingTotal;
  final double total;
  final String currency;
  final String? createdAt;
  final List<CustomerOrderItem> items;
  final String? customerName;
  final String? customerPhone;
  final String? vendorName;
  final String? paymentMethod;

  const CustomerOrder({
    required this.id,
    required this.orderNumber,
    required this.status,
    required this.statusLabel,
    required this.subtotal,
    required this.shippingTotal,
    required this.total,
    this.currency = 'QAR',
    this.createdAt,
    required this.items,
    this.customerName,
    this.customerPhone,
    this.vendorName,
    this.paymentMethod,
  });

  String get itemSummary {
    if (items.isEmpty) return 'Items in order';
    final first = items.first.title;
    if (items.length == 1) return first;
    return '$first +${items.length - 1} more';
  }

  factory CustomerOrder.fromJson(Map<String, dynamic> json) {
    final rawVendorOrders = json['vendor_orders'] as List? ?? [];
    final itemsList = <CustomerOrderItem>[];
    String? vName;
    String? payMethod;

    for (final vo in rawVendorOrders) {
      if (vo is Map<String, dynamic>) {
        if (vName == null && vo['vendor'] is Map<String, dynamic>) {
          vName = vo['vendor']['name']?.toString();
        }
        payMethod ??= vo['payment_method']?.toString();
        final rawItems = vo['items'] as List? ?? [];
        for (final it in rawItems) {
          if (it is Map<String, dynamic>) {
            itemsList.add(CustomerOrderItem.fromJson(it));
          }
        }
      }
    }

    final shippingAddr = json['shipping_address'] as Map<String, dynamic>?;

    return CustomerOrder(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      orderNumber: json['order_number']?.toString() ?? '#${json['id']}',
      status: json['status']?.toString() ?? 'pending',
      statusLabel: json['status_label']?.toString() ?? 'Pending',
      subtotal: (json['subtotal'] is num) ? (json['subtotal'] as num).toDouble() : 0.0,
      shippingTotal: (json['shipping_total'] is num) ? (json['shipping_total'] as num).toDouble() : 0.0,
      total: (json['total'] is num) ? (json['total'] as num).toDouble() : 0.0,
      currency: 'QAR',
      createdAt: json['created_at']?.toString(),
      items: itemsList,
      customerName: shippingAddr?['name']?.toString(),
      customerPhone: shippingAddr?['phone']?.toString(),
      vendorName: vName ?? 'Openbox Platform',
      paymentMethod: payMethod?.toUpperCase() ?? 'COD',
    );
  }
}

class CustomerOrderItem {
  final int id;
  final int? productId;
  final String title;
  final double price;
  final int quantity;
  final double total;
  final String? thumbnail;

  const CustomerOrderItem({
    required this.id,
    this.productId,
    required this.title,
    required this.price,
    required this.quantity,
    required this.total,
    this.thumbnail,
  });

  factory CustomerOrderItem.fromJson(Map<String, dynamic> json) {
    final thumb = json['thumbnail']?.toString() ?? json['image_url']?.toString();
    return CustomerOrderItem(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      productId: json['product_id'] as int?,
      title: json['product_title']?.toString() ?? json['title']?.toString() ?? 'Product',
      price: (json['price'] is num) ? (json['price'] as num).toDouble() : 0.0,
      quantity: json['quantity'] is int ? json['quantity'] : 1,
      total: (json['total'] is num) ? (json['total'] as num).toDouble() : 0.0,
      thumbnail: thumb != null ? ApiConfig.sanitizeUrl(thumb) : null,
    );
  }
}
