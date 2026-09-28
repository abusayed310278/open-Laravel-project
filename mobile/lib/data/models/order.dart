enum OrderStatus { confirmed, processing, packed, shipped, outForDelivery, delivered, cancelled }

extension OrderStatusX on OrderStatus {
  String get label => switch (this) {
        OrderStatus.confirmed => 'Confirmed',
        OrderStatus.processing => 'Processing',
        OrderStatus.packed => 'Packed',
        OrderStatus.shipped => 'Shipped',
        OrderStatus.outForDelivery => 'Out for delivery',
        OrderStatus.delivered => 'Delivered',
        OrderStatus.cancelled => 'Cancelled',
      };
}

class OrderItem {
  const OrderItem({
    required this.productTitle,
    required this.imageUrl,
    required this.price,
    required this.quantity,
    required this.sellerName,
  });

  final String productTitle;
  final String imageUrl;
  final double price;
  final int quantity;
  final String sellerName;
}

class Order {
  const Order({
    required this.id,
    required this.placedAt,
    required this.status,
    required this.total,
    required this.items,
    this.trackingNumber,
  });

  final String id;
  final DateTime placedAt;
  final OrderStatus status;
  final double total;
  final List<OrderItem> items;
  final String? trackingNumber;

  static const List<OrderStatus> pipeline = [
    OrderStatus.confirmed,
    OrderStatus.processing,
    OrderStatus.packed,
    OrderStatus.shipped,
    OrderStatus.outForDelivery,
    OrderStatus.delivered,
  ];
}
