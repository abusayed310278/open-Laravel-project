enum NotificationCategory { order, payment, product, support, promo }

class AppNotification {
  const AppNotification({
    required this.id,
    required this.title,
    required this.body,
    required this.time,
    required this.category,
    this.isRead = false,
  });

  final String id;
  final String title;
  final String body;
  final String time;
  final NotificationCategory category;
  final bool isRead;
}
