import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../../core/network/api_client.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../core/widgets/loading_view.dart';
import '../orders/orders_screen.dart';
import '../settings/subscreens/change_password_screen.dart';
import '../settings/subscreens/notification_settings_screen.dart';
import '../settings/subscreens/seller_center_screen.dart';
import '../settings/subscreens/verification_status_screen.dart';

class SellerNotificationItem {
  final String id;
  final String title;
  final String message;
  final String type; // 'order', 'inspection', 'kyc', 'security', 'system'
  final String category;
  bool isRead;
  final DateTime createdAt;

  SellerNotificationItem({
    required this.id,
    required this.title,
    required this.message,
    required this.type,
    required this.category,
    required this.isRead,
    required this.createdAt,
  });

  factory SellerNotificationItem.fromJson(Map<String, dynamic> json) {
    DateTime parsedDate;
    try {
      parsedDate = json['created_at'] != null
          ? DateTime.parse(json['created_at'].toString())
          : DateTime.now();
    } catch (_) {
      parsedDate = DateTime.now();
    }

    return SellerNotificationItem(
      id: json['id']?.toString() ?? '',
      title: json['title']?.toString() ?? 'Notification',
      message: json['message']?.toString() ?? '',
      type: json['type']?.toString() ?? 'system',
      category: json['category']?.toString() ?? 'system',
      isRead: json['is_read'] == true,
      createdAt: parsedDate,
    );
  }
}

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  final ApiClient _apiClient = ApiClient();
  bool _isLoading = true;
  String _selectedFilter = 'all'; // 'all', 'unread', 'order', 'operations', 'account'
  List<SellerNotificationItem> _notifications = [];
  int _unreadCount = 0;

  @override
  void initState() {
    super.initState();
    _fetchNotifications();
  }

  Future<void> _fetchNotifications() async {
    setState(() => _isLoading = true);
    try {
      final res = await _apiClient.get('/notifications');
      if (res is Map<String, dynamic>) {
        final list = (res['notifications'] as List?) ?? [];
        final items = list
            .whereType<Map<String, dynamic>>()
            .map((e) => SellerNotificationItem.fromJson(e))
            .toList();

        setState(() {
          _notifications = items;
          _unreadCount = (res['unread_count'] as int?) ??
              items.where((n) => !n.isRead).length;
          _isLoading = false;
        });
        return;
      }
    } catch (_) {
      // Fallback defaults if offline
    }

    if (mounted) {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _markAsRead(SellerNotificationItem item) async {
    if (item.isRead) return;

    setState(() {
      item.isRead = true;
      _unreadCount = (_unreadCount - 1).clamp(0, 999);
    });

    try {
      await _apiClient.post('/notifications/${item.id}/read');
    } catch (_) {}
  }

  Future<void> _markAllAsRead() async {
    if (_notifications.every((n) => n.isRead)) {
      AppSnackBar.info(context, 'All notifications are already marked as read.');
      return;
    }

    setState(() {
      for (final n in _notifications) {
        n.isRead = true;
      }
      _unreadCount = 0;
    });

    try {
      await _apiClient.post('/notifications/all/read');
      if (mounted) {
        AppSnackBar.success(context, 'Marked all notifications as read.');
      }
    } catch (_) {}
  }

  List<SellerNotificationItem> get _filteredNotifications {
    switch (_selectedFilter) {
      case 'unread':
        return _notifications.where((n) => !n.isRead).toList();
      case 'order':
        return _notifications.where((n) => n.type == 'order').toList();
      case 'operations':
        return _notifications
            .where((n) => n.type == 'inspection' || n.category == 'operations')
            .toList();
      case 'account':
        return _notifications
            .where((n) =>
                n.type == 'kyc' ||
                n.type == 'security' ||
                n.category == 'account')
            .toList();
      default:
        return _notifications;
    }
  }

  void _handleNotificationTap(SellerNotificationItem notif) {
    _markAsRead(notif);

    // Deep-link according to notification category
    if (notif.type == 'order') {
      Navigator.of(context).push(
        MaterialPageRoute(builder: (_) => const OrdersScreen()),
      );
    } else if (notif.type == 'inspection') {
      Navigator.of(context).push(
        MaterialPageRoute(builder: (_) => const SellerCenterScreen()),
      );
    } else if (notif.type == 'kyc') {
      Navigator.of(context).push(
        MaterialPageRoute(builder: (_) => const VerificationStatusScreen()),
      );
    } else if (notif.type == 'security') {
      Navigator.of(context).push(
        MaterialPageRoute(builder: (_) => const ChangePasswordScreen()),
      );
    }
  }

  String _formatTimeAgo(DateTime dt) {
    final now = DateTime.now();
    final diff = now.difference(dt);

    if (diff.inMinutes < 1) return 'Just now';
    if (diff.inMinutes < 60) return '${diff.inMinutes}m ago';
    if (diff.inHours < 24) return '${diff.inHours}h ago';
    if (diff.inDays == 1) return 'Yesterday';
    if (diff.inDays < 7) return '${diff.inDays}d ago';
    return DateFormat('MMM d').format(dt);
  }

  @override
  Widget build(BuildContext context) {
    final filtered = _filteredNotifications;

    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        title: Row(
          children: [
            const Text(
              'Notifications',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w800,
                color: AppColors.ink,
              ),
            ),
            if (_unreadCount > 0) ...[
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                decoration: BoxDecoration(
                  color: AppColors.brand500,
                  borderRadius: BorderRadius.circular(AppRadius.pill),
                ),
                child: Text(
                  '$_unreadCount new',
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                  ),
                ),
              ),
            ],
          ],
        ),
        actions: [
          IconButton(
            tooltip: 'Mark All Read',
            icon: const Icon(Icons.done_all_rounded, color: AppColors.brand600),
            onPressed: _markAllAsRead,
          ),
          IconButton(
            tooltip: 'Alert Preferences',
            icon: const Icon(Icons.tune_rounded, color: AppColors.slate600),
            onPressed: () {
              Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (_) => const NotificationSettingsScreen(),
                ),
              );
            },
          ),
        ],
      ),
      body: _isLoading
          ? const LoadingView(message: 'Loading notifications...')
          : Column(
              children: [
                // Filter chips row
                _buildFilterChips(),

                // Notifications List
                Expanded(
                  child: RefreshIndicator(
                    onRefresh: _fetchNotifications,
                    color: AppColors.brand500,
                    child: filtered.isEmpty
                        ? _buildEmptyView()
                        : ListView.separated(
                            padding: const EdgeInsets.all(AppSpacing.md),
                            itemCount: filtered.length,
                            separatorBuilder: (context, index) =>
                                const SizedBox(height: 10),
                            itemBuilder: (context, index) {
                              final item = filtered[index];
                              return _buildNotificationCard(item);
                            },
                          ),
                  ),
                ),
              ],
            ),
    );
  }

  Widget _buildFilterChips() {
    return Container(
      color: AppColors.surface,
      padding: const EdgeInsets.symmetric(
        horizontal: AppSpacing.md,
        vertical: AppSpacing.sm,
      ),
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: Row(
          children: [
            _buildChip('all', 'All (${_notifications.length})'),
            const SizedBox(width: 6),
            if (_unreadCount > 0) ...[
              _buildChip('unread', 'Unread ($_unreadCount)'),
              const SizedBox(width: 6),
            ],
            _buildChip('order', 'Orders'),
            const SizedBox(width: 6),
            _buildChip('operations', 'Hubs & Operations'),
            const SizedBox(width: 6),
            _buildChip('account', 'Account & KYC'),
          ],
        ),
      ),
    );
  }

  Widget _buildChip(String key, String label) {
    final isSelected = _selectedFilter == key;
    return InkWell(
      onTap: () => setState(() => _selectedFilter = key),
      borderRadius: BorderRadius.circular(AppRadius.pill),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.brand500 : AppColors.slate100,
          borderRadius: BorderRadius.circular(AppRadius.pill),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 12,
            fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
            color: isSelected ? Colors.white : AppColors.slate700,
          ),
        ),
      ),
    );
  }

  Widget _buildNotificationCard(SellerNotificationItem item) {
    Color iconBg;
    Color iconColor;
    IconData icon;

    switch (item.type) {
      case 'order':
        iconBg = AppColors.infoBg;
        iconColor = AppColors.info;
        icon = Icons.shopping_bag_outlined;
        break;
      case 'inspection':
        iconBg = AppColors.warningBg;
        iconColor = AppColors.brand700;
        icon = Icons.verified_outlined;
        break;
      case 'kyc':
        iconBg = AppColors.brand100;
        iconColor = AppColors.brand700;
        icon = Icons.shield_outlined;
        break;
      case 'security':
        iconBg = AppColors.purpleBg;
        iconColor = AppColors.purple;
        icon = Icons.lock_outline_rounded;
        break;
      default:
        iconBg = AppColors.slate100;
        iconColor = AppColors.slate700;
        icon = Icons.notifications_outlined;
    }

    return InkWell(
      onTap: () => _handleNotificationTap(item),
      borderRadius: BorderRadius.circular(AppRadius.lg),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.all(AppSpacing.md),
        decoration: BoxDecoration(
          color: item.isRead
              ? AppColors.surface
              : AppColors.brand50.withValues(alpha: 0.35),
          borderRadius: BorderRadius.circular(AppRadius.lg),
          border: Border.all(
            color: item.isRead
                ? AppColors.slate200.withValues(alpha: 0.7)
                : AppColors.brand300,
            width: item.isRead ? 1 : 1.3,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.02),
              blurRadius: 6,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Icon
            Container(
              width: 38,
              height: 38,
              decoration: BoxDecoration(
                color: iconBg,
                borderRadius: BorderRadius.circular(AppRadius.md),
              ),
              child: Icon(icon, color: iconColor, size: 20),
            ),
            const SizedBox(width: AppSpacing.md),

            // Content
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Text(
                          item.title,
                          style: TextStyle(
                            fontSize: 13.5,
                            fontWeight:
                                item.isRead ? FontWeight.w600 : FontWeight.w800,
                            color: AppColors.ink,
                          ),
                        ),
                      ),
                      const SizedBox(width: 6),
                      Text(
                        _formatTimeAgo(item.createdAt),
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: item.isRead
                              ? FontWeight.w400
                              : FontWeight.w600,
                          color: item.isRead
                              ? AppColors.slate400
                              : AppColors.brand700,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text(
                    item.message,
                    style: const TextStyle(
                      fontSize: 12,
                      color: AppColors.slate600,
                      height: 1.35,
                    ),
                  ),
                  if (!item.isRead) ...[
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        Container(
                          width: 6,
                          height: 6,
                          decoration: const BoxDecoration(
                            color: AppColors.brand500,
                            shape: BoxShape.circle,
                          ),
                        ),
                        const SizedBox(width: 4),
                        const Text(
                          'Tap to view details',
                          style: TextStyle(
                            fontSize: 10.5,
                            fontWeight: FontWeight.w600,
                            color: AppColors.brand700,
                          ),
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
    );
  }

  Widget _buildEmptyView() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppSpacing.xxl),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 64,
              height: 64,
              decoration: const BoxDecoration(
                color: AppColors.brand50,
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.notifications_off_outlined,
                size: 30,
                color: AppColors.brand600,
              ),
            ),
            const SizedBox(height: AppSpacing.md),
            const Text(
              'No Notifications',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w700,
                color: AppColors.ink,
              ),
            ),
            const SizedBox(height: 6),
            const Text(
              'You are all caught up! Order alerts, inspection updates, and account notices will appear here.',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 12.5,
                color: AppColors.slate500,
                height: 1.4,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
