import 'package:flutter/foundation.dart';
import '../data/models/app_notification.dart';

class NotificationProvider extends ChangeNotifier {
  List<AppNotification> _notifications = [
    const AppNotification(
      id: 'n1',
      title: 'Order Confirmed',
      body: 'Your order #ORD-8821 for iPhone 14 Pro has been confirmed by seller.',
      time: '10m ago',
      category: NotificationCategory.order,
    ),
    const AppNotification(
      id: 'n2',
      title: 'Price Drop Alert!',
      body: 'MacBook Air M2 13" is now 15% off for a limited time.',
      time: '1h ago',
      category: NotificationCategory.promo,
    ),
    const AppNotification(
      id: 'n3',
      title: 'Payment Received',
      body: 'Payment of QAR 2,699 was verified for Order #ORD-8821.',
      time: '2h ago',
      category: NotificationCategory.payment,
      isRead: true,
    ),
    const AppNotification(
      id: 'n4',
      title: 'Support Ticket Update',
      body: 'Agent Sarah replied to your inquiry regarding warranty coverage.',
      time: '1d ago',
      category: NotificationCategory.support,
      isRead: true,
    ),
  ];

  List<AppNotification> get notifications => List.unmodifiable(_notifications);

  int get unreadCount => _notifications.where((n) => !n.isRead).length;

  void markAllAsRead() {
    _notifications = _notifications.map((n) => AppNotification(
      id: n.id,
      title: n.title,
      body: n.body,
      time: n.time,
      category: n.category,
      isRead: true,
    )).toList();
    notifyListeners();
  }

  void markAsRead(String id) {
    final index = _notifications.indexWhere((n) => n.id == id);
    if (index >= 0) {
      final n = _notifications[index];
      _notifications[index] = AppNotification(
        id: n.id,
        title: n.title,
        body: n.body,
        time: n.time,
        category: n.category,
        isRead: true,
      );
      notifyListeners();
    }
  }

  void addNotification(AppNotification notification) {
    _notifications.insert(0, notification);
    notifyListeners();
  }

  void clearAll() {
    _notifications.clear();
    notifyListeners();
  }
}
