import '../../core/network/api_config.dart';

class VendorOrderSummary {
  final int id;
  final int? orderId;
  final String orderNumber;
  final String status;
  final String statusLabel;
  final String customerName;
  final String? customerPhone;
  final int itemsCount;
  final String itemSummary;
  final String? thumbnail;
  final double subtotal;
  final double shipping;
  final double tax;
  final double total;
  final String currency;
  final String? paymentMethod;
  final String? createdAt;

  const VendorOrderSummary({
    required this.id,
    this.orderId,
    required this.orderNumber,
    required this.status,
    required this.statusLabel,
    required this.customerName,
    this.customerPhone,
    required this.itemsCount,
    required this.itemSummary,
    this.thumbnail,
    required this.subtotal,
    required this.shipping,
    required this.tax,
    required this.total,
    this.currency = 'QAR',
    this.paymentMethod,
    this.createdAt,
  });

  factory VendorOrderSummary.fromJson(Map<String, dynamic> json) {
    return VendorOrderSummary(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      orderId: json['order_id'] as int?,
      orderNumber: json['order_number']?.toString() ?? '#${json['id']}',
      status: json['status']?.toString() ?? 'pending',
      statusLabel: json['status_label']?.toString() ?? 'Pending',
      customerName: json['customer_name']?.toString() ?? 'Customer',
      customerPhone: json['customer_phone']?.toString(),
      itemsCount: json['items_count'] is int ? json['items_count'] : 1,
      itemSummary: json['item_summary']?.toString() ?? '',
      thumbnail: json['thumbnail'] != null ? ApiConfig.sanitizeUrl(json['thumbnail'].toString()) : null,
      subtotal: (json['subtotal'] is num) ? (json['subtotal'] as num).toDouble() : 0.0,
      shipping: (json['shipping'] is num) ? (json['shipping'] as num).toDouble() : 0.0,
      tax: (json['tax'] is num) ? (json['tax'] as num).toDouble() : 0.0,
      total: (json['total'] is num) ? (json['total'] as num).toDouble() : 0.0,
      currency: json['currency']?.toString() ?? 'QAR',
      paymentMethod: json['payment_method']?.toString(),
      createdAt: json['created_at']?.toString(),
    );
  }
}

class OrderItemData {
  final int id;
  final int? productId;
  final String title;
  final String? thumbnail;
  final double unitPrice;
  final int quantity;
  final double totalPrice;

  const OrderItemData({
    required this.id,
    this.productId,
    required this.title,
    this.thumbnail,
    required this.unitPrice,
    required this.quantity,
    required this.totalPrice,
  });

  factory OrderItemData.fromJson(Map<String, dynamic> json) {
    return OrderItemData(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      productId: json['product_id'] as int?,
      title: json['title']?.toString() ?? '',
      thumbnail: json['thumbnail'] != null ? ApiConfig.sanitizeUrl(json['thumbnail'].toString()) : null,
      unitPrice: (json['unit_price'] is num) ? (json['unit_price'] as num).toDouble() : 0.0,
      quantity: json['quantity'] is int ? json['quantity'] : 1,
      totalPrice: (json['total_price'] is num) ? (json['total_price'] as num).toDouble() : 0.0,
    );
  }
}

class ShippingAddressData {
  final String? recipientName;
  final String? phone;
  final String? addressLine1;
  final String? addressLine2;
  final String? city;
  final String? state;
  final String? postalCode;
  final String? country;

  const ShippingAddressData({
    this.recipientName,
    this.phone,
    this.addressLine1,
    this.addressLine2,
    this.city,
    this.state,
    this.postalCode,
    this.country,
  });

  factory ShippingAddressData.fromJson(Map<String, dynamic> json) {
    return ShippingAddressData(
      recipientName: json['recipient_name']?.toString(),
      phone: json['phone']?.toString(),
      addressLine1: json['address_line_1']?.toString(),
      addressLine2: json['address_line_2']?.toString(),
      city: json['city']?.toString(),
      state: json['state']?.toString(),
      postalCode: json['postal_code']?.toString(),
      country: json['country']?.toString(),
    );
  }
}

class NextStatusOption {
  final String value;
  final String label;

  const NextStatusOption({required this.value, required this.label});

  factory NextStatusOption.fromJson(Map<String, dynamic> json) {
    return NextStatusOption(
      value: json['value']?.toString() ?? '',
      label: json['label']?.toString() ?? '',
    );
  }
}

class VendorOrderDetail {
  final int id;
  final int? orderId;
  final String orderNumber;
  final String status;
  final String statusLabel;
  final String? trackingNumber;
  final String? paymentMethod;
  final double subtotal;
  final double shipping;
  final double tax;
  final double total;
  final String currency;
  final String? shippedAt;
  final String? deliveredAt;
  final String? createdAt;
  final String customerName;
  final String? customerEmail;
  final String? customerPhone;
  final ShippingAddressData? shippingAddress;
  final List<OrderItemData> items;
  final List<NextStatusOption> nextStatuses;

  const VendorOrderDetail({
    required this.id,
    this.orderId,
    required this.orderNumber,
    required this.status,
    required this.statusLabel,
    this.trackingNumber,
    this.paymentMethod,
    required this.subtotal,
    required this.shipping,
    required this.tax,
    required this.total,
    this.currency = 'QAR',
    this.shippedAt,
    this.deliveredAt,
    this.createdAt,
    required this.customerName,
    this.customerEmail,
    this.customerPhone,
    this.shippingAddress,
    required this.items,
    required this.nextStatuses,
  });

  factory VendorOrderDetail.fromJson(Map<String, dynamic> json) {
    final customer = json['customer'] as Map<String, dynamic>?;
    final shipping = json['shipping_address'] as Map<String, dynamic>?;
    final itemsList = (json['items'] as List?)
            ?.map((e) => OrderItemData.fromJson(e as Map<String, dynamic>))
            .toList() ??
        [];
    final nextList = (json['next_statuses'] as List?)
            ?.map((e) => NextStatusOption.fromJson(e as Map<String, dynamic>))
            .toList() ??
        [];

    return VendorOrderDetail(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      orderId: json['order_id'] as int?,
      orderNumber: json['order_number']?.toString() ?? '#${json['id']}',
      status: json['status']?.toString() ?? 'pending',
      statusLabel: json['status_label']?.toString() ?? 'Pending',
      trackingNumber: json['tracking_number']?.toString(),
      paymentMethod: json['payment_method']?.toString(),
      subtotal: (json['subtotal'] is num) ? (json['subtotal'] as num).toDouble() : 0.0,
      shipping: (json['shipping'] is num) ? (json['shipping'] as num).toDouble() : 0.0,
      tax: (json['tax'] is num) ? (json['tax'] as num).toDouble() : 0.0,
      total: (json['total'] is num) ? (json['total'] as num).toDouble() : 0.0,
      currency: json['currency']?.toString() ?? 'QAR',
      shippedAt: json['shipped_at']?.toString(),
      deliveredAt: json['delivered_at']?.toString(),
      createdAt: json['created_at']?.toString(),
      customerName: customer?['name']?.toString() ?? 'Customer',
      customerEmail: customer?['email']?.toString(),
      customerPhone: customer?['phone']?.toString(),
      shippingAddress: shipping != null ? ShippingAddressData.fromJson(shipping) : null,
      items: itemsList,
      nextStatuses: nextList,
    );
  }
}
