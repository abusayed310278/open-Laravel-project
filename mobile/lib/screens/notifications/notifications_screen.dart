import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../core/widgets/empty_state.dart';
import '../../data/models/app_notification.dart';
import '../../providers/notification_provider.dart';

class NotificationsScreen extends StatelessWidget {
  const NotificationsScreen({super.key});

  IconData _iconFor(NotificationCategory category) => switch (category) {
        NotificationCategory.order => Icons.local_shipping_outlined,
        NotificationCategory.payment => Icons.payments_outlined,
        NotificationCategory.product => Icons.local_offer_outlined,
        NotificationCategory.support => Icons.support_agent_outlined,
        NotificationCategory.promo => Icons.campaign_outlined,
      };

  Color _colorFor(NotificationCategory category) => switch (category) {
        NotificationCategory.order => AppColors.info,
        NotificationCategory.payment => AppColors.success,
        NotificationCategory.product => AppColors.brand600,
        NotificationCategory.support => AppColors.slate700,
        NotificationCategory.promo => AppColors.danger,
      };

  @override
  Widget build(BuildContext context) {
    final notificationProvider = context.watch<NotificationProvider>();
    final notifications = notificationProvider.notifications;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifications'),
        actions: [
          if (notifications.isNotEmpty)
            TextButton(
              onPressed: () {
                notificationProvider.markAllAsRead();
                AppSnackbar.showSuccess(context, 'All notifications marked as read');
              },
              child: const Text('Mark all read'),
            ),
        ],
      ),
      body: notifications.isEmpty
          ? const EmptyState(
              icon: Icons.notifications_none_rounded,
              title: 'No notifications yet',
              message: 'You have no notifications at this time. Check back later!',
            )
          : ListView.separated(
              itemCount: notifications.length,
              separatorBuilder: (_, _) => const Divider(height: 1, indent: AppSpacing.lg, endIndent: AppSpacing.lg),
              itemBuilder: (context, i) {
                final n = notifications[i];
                return InkWell(
                  onTap: () => notificationProvider.markAsRead(n.id),
                  child: Container(
                    color: n.isRead ? Colors.transparent : AppColors.brand50.withOpacity(0.4),
                    child: ListTile(
                      contentPadding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.xs),
                      leading: CircleAvatar(
                        radius: 20,
                        backgroundColor: _colorFor(n.category).withOpacity(0.12),
                        child: Icon(_iconFor(n.category), color: _colorFor(n.category), size: 18),
                      ),
                      title: Text(n.title, style: TextStyle(fontWeight: n.isRead ? FontWeight.w600 : FontWeight.w800, fontSize: 13)),
                      subtitle: Text(n.body, style: const TextStyle(fontSize: 12, color: AppColors.slate500), maxLines: 2, overflow: TextOverflow.ellipsis),
                      trailing: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text(n.time, style: const TextStyle(fontSize: 11, color: AppColors.slate400)),
                          if (!n.isRead) ...[
                            const SizedBox(height: 4),
                            Container(
                              width: 8,
                              height: 8,
                              decoration: const BoxDecoration(color: AppColors.brand600, shape: BoxShape.circle),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ),
                );
              },
            ),
    );
  }
}

