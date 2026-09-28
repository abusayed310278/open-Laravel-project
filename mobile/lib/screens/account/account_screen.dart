import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_logo.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../core/widgets/user_avatar.dart';
import '../../data/mock/app_state.dart';
import '../../providers/auth_provider.dart';
import '../../providers/order_provider.dart';
import '../auth/login_screen.dart';
import '../chat/chat_list_screen.dart';
import '../notifications/notifications_screen.dart';
import '../orders/orders_list_screen.dart';
import '../wishlist/wishlist_screen.dart';
import 'addresses_screen.dart';
import 'profile_settings_screen.dart';
import 'returns_screen.dart';
import 'security_screen.dart';

class AccountScreen extends StatelessWidget {
  const AccountScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<AuthProvider>();
    final orderProvider = context.watch<OrderProvider>();
    final isLoggedIn = authProvider.isAuthenticated;
    final user = authProvider.user;

    return Scaffold(
      appBar: AppBar(title: const Text('My Account')),
      body: ListView(
        padding: const EdgeInsets.all(AppSpacing.lg),
        children: [
          if (isLoggedIn) ...[
            // User Profile Header
            Container(
              padding: const EdgeInsets.all(AppSpacing.lg),
              decoration: BoxDecoration(
                color: AppColors.surface,
                border: Border.all(color: AppColors.slate200),
                borderRadius: BorderRadius.circular(AppRadius.lg),
              ),
              child: Row(
                children: [
                  UserAvatar(
                    avatarUrlOrPath: user?.avatar,
                    name: user?.name.isNotEmpty == true ? user!.name : (user?.email ?? 'User'),
                    radius: 28,
                    fontSize: 18,
                  ),
                  const SizedBox(width: AppSpacing.md),
                  Expanded(
                    child: Text(
                      user?.name.isNotEmpty == true ? user!.name : (user?.email ?? 'Account User'),
                      style: const TextStyle(
                        fontWeight: FontWeight.w700,
                        fontSize: 16,
                        color: AppColors.ink,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.lg),
            // User Stats Row
            Row(
              children: [
                Expanded(child: _StatTile(label: 'Orders', value: '${orderProvider.orders.length}')),
                const SizedBox(width: AppSpacing.sm),
                Expanded(child: const _StatTile(label: 'Wishlist', value: '3')),
                const SizedBox(width: AppSpacing.sm),
                Expanded(
                  child: ListenableBuilder(
                    listenable: ChatStore.instance,
                    builder: (_, __) => _StatTile(label: 'Messages', value: '${ChatStore.instance.conversations.length}'),
                  ),
                ),
                const SizedBox(width: AppSpacing.sm),
                Expanded(child: const _StatTile(label: 'Reviews', value: '4')),
              ],
            ),
            const SizedBox(height: AppSpacing.xl),
            // Authenticated Activity Section
            _MenuSection(
              title: 'My Activity',
              items: [
                _MenuItem(icon: Icons.receipt_long_outlined, label: 'My Orders', onTap: () => _push(context, const OrdersListScreen())),
                _MenuItem(icon: Icons.favorite_border_rounded, label: 'Wishlist', onTap: () => _push(context, const WishlistScreen())),
                _MenuItem(icon: Icons.chat_bubble_outline_rounded, label: 'Messages', onTap: () => _push(context, const ChatListScreen())),
                _MenuItem(icon: Icons.notifications_none_rounded, label: 'Notifications', onTap: () => _push(context, const NotificationsScreen())),
                _MenuItem(icon: Icons.undo_rounded, label: 'Returns & Refunds', onTap: () => _push(context, const ReturnsScreen())),
              ],
            ),
            const SizedBox(height: AppSpacing.lg),
            // Authenticated Settings Section
            _MenuSection(
              title: 'Account Settings',
              items: [
                _MenuItem(icon: Icons.location_on_outlined, label: 'Addresses', onTap: () => _push(context, const AddressesScreen())),
                _MenuItem(icon: Icons.person_outline_rounded, label: 'Profile Settings', onTap: () => _push(context, const ProfileSettingsScreen())),
                _MenuItem(icon: Icons.shield_outlined, label: 'Security', onTap: () => _push(context, const SecurityScreen())),
              ],
            ),
            const SizedBox(height: AppSpacing.lg),
          ] else ...[
            // Unauthenticated Guest Card with Sign In Button
            Container(
              padding: const EdgeInsets.all(AppSpacing.xl),
              decoration: BoxDecoration(
                color: AppColors.surface,
                border: Border.all(color: AppColors.slate200),
                borderRadius: BorderRadius.circular(AppRadius.lg),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.03),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Column(
                children: [
                  const AppLogo(size: 64, borderRadius: 16),
                  const SizedBox(height: AppSpacing.md),
                  const Text(
                    'Welcome to Openbox',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: AppColors.ink),
                  ),
                  const SizedBox(height: AppSpacing.xs),
                  const Text(
                    'Sign in to view your orders, track shipments, and manage your account.',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 13, color: AppColors.slate500, height: 1.4),
                  ),
                  const SizedBox(height: AppSpacing.lg),
                  SizedBox(
                    width: double.infinity,
                    height: 48,
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.brand600,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
                        elevation: 0,
                      ),
                      onPressed: () => _push(context, const LoginScreen()),
                      child: const Text(
                        'Sign In / Register',
                        style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.xl),
          ],

          // Public Basic Information & Support Section (Visible to both logged in and guest users)
          _MenuSection(
            title: 'Information & Support',
            items: [
              _MenuItem(
                icon: Icons.help_outline_rounded,
                label: 'Help Center',
                onTap: () => AppSnackbar.info(context, 'Help Center coming soon'),
              ),
              _MenuItem(
                icon: Icons.support_agent_outlined,
                label: 'Contact Support',
                onTap: () => AppSnackbar.info(context, 'Contact Support coming soon'),
              ),
              _MenuItem(
                icon: Icons.info_outline_rounded,
                label: 'About Openbox',
                onTap: () => AppSnackbar.info(context, 'About Openbox coming soon'),
              ),
              _MenuItem(
                icon: Icons.gavel_outlined,
                label: 'Terms & Privacy Policy',
                onTap: () => AppSnackbar.info(context, 'Privacy Policy coming soon'),
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.xl),

          // Sign Out Button (Only shown when logged in)
          if (isLoggedIn) ...[
            OutlinedButton.icon(
              style: OutlinedButton.styleFrom(
                foregroundColor: AppColors.danger,
                side: const BorderSide(color: AppColors.dangerBg),
                padding: const EdgeInsets.symmetric(vertical: AppSpacing.md),
              ),
              onPressed: () async {
                await context.read<AuthProvider>().logout();
                if (context.mounted) {
                  AppSnackbar.info(context, 'Logged out successfully');
                }
              },
              icon: const Icon(Icons.logout_rounded, size: 18),
              label: const Text('Sign Out', style: TextStyle(fontWeight: FontWeight.w600)),
            ),
            const SizedBox(height: AppSpacing.xl),
          ],
        ],
      ),
    );
  }


  void _push(BuildContext context, Widget screen) {
    Navigator.of(context).push(MaterialPageRoute(builder: (_) => screen));
  }
}

class _StatTile extends StatelessWidget {
  const _StatTile({required this.label, required this.value});
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: AppSpacing.md),
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border.all(color: AppColors.slate200),
        borderRadius: BorderRadius.circular(AppRadius.md),
      ),
      child: Column(
        children: [
          Text(value, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16, color: AppColors.ink)),
          const SizedBox(height: 2),
          Text(label, style: const TextStyle(fontSize: 10, color: AppColors.slate500)),
        ],
      ),
    );
  }
}

class _MenuItem {
  const _MenuItem({required this.icon, required this.label, required this.onTap});
  final IconData icon;
  final String label;
  final VoidCallback onTap;
}

class _MenuSection extends StatelessWidget {
  const _MenuSection({required this.title, required this.items});
  final String title;
  final List<_MenuItem> items;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.only(bottom: AppSpacing.sm, left: 4),
          child: Text(title, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.slate500)),
        ),
        Container(
          decoration: BoxDecoration(
            color: AppColors.surface,
            border: Border.all(color: AppColors.slate200),
            borderRadius: BorderRadius.circular(AppRadius.md),
          ),
          child: Column(
            children: List.generate(items.length, (i) {
              final item = items[i];
              return Column(
                children: [
                  ListTile(
                    leading: Icon(item.icon, size: 20, color: AppColors.slate700),
                    title: Text(item.label, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                    trailing: const Icon(Icons.chevron_right_rounded, color: AppColors.slate400),
                    onTap: item.onTap,
                  ),
                  if (i < items.length - 1) const Divider(height: 1, indent: AppSpacing.lg, endIndent: AppSpacing.lg),
                ],
              );
            }),
          ),
        ),
      ],
    );
  }
}

