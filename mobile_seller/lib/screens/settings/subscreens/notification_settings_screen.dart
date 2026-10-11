import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_spacing.dart';
import '../../../core/widgets/app_snackbar.dart';

class NotificationSettingsScreen extends StatefulWidget {
  const NotificationSettingsScreen({super.key});

  @override
  State<NotificationSettingsScreen> createState() => _NotificationSettingsScreenState();
}

class _NotificationSettingsScreenState extends State<NotificationSettingsScreen> {
  bool _masterPush = true;
  bool _newOrders = true;
  bool _customerMessages = true;
  bool _lowStock = true;
  bool _payouts = true;
  bool _weeklyDigest = false;
  bool _marketing = false;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadPreferences();
  }

  Future<void> _loadPreferences() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _masterPush = prefs.getBool('notif_master') ?? true;
      _newOrders = prefs.getBool('notif_orders') ?? true;
      _customerMessages = prefs.getBool('notif_messages') ?? true;
      _lowStock = prefs.getBool('notif_stock') ?? true;
      _payouts = prefs.getBool('notif_payouts') ?? true;
      _weeklyDigest = prefs.getBool('notif_digest') ?? false;
      _marketing = prefs.getBool('notif_marketing') ?? false;
      _isLoading = false;
    });
  }

  Future<void> _savePreference(String key, bool value) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(key, value);
    if (mounted) {
      AppSnackBar.success(context, 'Notification preferences updated');
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        backgroundColor: AppColors.slate50,
        appBar: AppBar(title: const Text('Notifications')),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        title: const Text('Notifications'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppSpacing.lg),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Master Switch
            Container(
              padding: const EdgeInsets.all(AppSpacing.md),
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(AppRadius.lg),
                border: Border.all(color: AppColors.slate200),
              ),
              child: SwitchListTile.adaptive(
                contentPadding: EdgeInsets.zero,
                activeColor: AppColors.brand500,
                title: const Text(
                  'Allow Push Notifications',
                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.ink),
                ),
                subtitle: const Text(
                  'Receive instant alerts on this device',
                  style: TextStyle(fontSize: 12, color: AppColors.slate500),
                ),
                value: _masterPush,
                onChanged: (val) {
                  setState(() => _masterPush = val);
                  _savePreference('notif_master', val);
                },
              ),
            ),
            const SizedBox(height: AppSpacing.lg),

            // Order & Customer Activity
            _buildSectionHeader('Orders & Customer Activity'),
            Container(
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(AppRadius.lg),
                border: Border.all(color: AppColors.slate200),
              ),
              child: Column(
                children: [
                  _buildToggleTile(
                    title: 'New Orders & Purchases',
                    subtitle: 'Instant sound alert when a customer buys a listing',
                    icon: Icons.shopping_bag_outlined,
                    value: _newOrders && _masterPush,
                    enabled: _masterPush,
                    onChanged: (val) {
                      setState(() => _newOrders = val);
                      _savePreference('notif_orders', val);
                    },
                  ),
                  const Divider(height: 1, indent: 56, color: AppColors.slate100),
                  _buildToggleTile(
                    title: 'Customer Direct Messages',
                    subtitle: 'Inquiries, questions, and offer negotiations',
                    icon: Icons.chat_outlined,
                    value: _customerMessages && _masterPush,
                    enabled: _masterPush,
                    onChanged: (val) {
                      setState(() => _customerMessages = val);
                      _savePreference('notif_messages', val);
                    },
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.lg),

            // Inventory & Financial
            _buildSectionHeader('Inventory & Settlements'),
            Container(
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(AppRadius.lg),
                border: Border.all(color: AppColors.slate200),
              ),
              child: Column(
                children: [
                  _buildToggleTile(
                    title: 'Low Stock Warnings',
                    subtitle: 'Alerts when item inventory falls to 2 or fewer units',
                    icon: Icons.warning_amber_rounded,
                    value: _lowStock && _masterPush,
                    enabled: _masterPush,
                    onChanged: (val) {
                      setState(() => _lowStock = val);
                      _savePreference('notif_stock', val);
                    },
                  ),
                  const Divider(height: 1, indent: 56, color: AppColors.slate100),
                  _buildToggleTile(
                    title: 'Payout & Wallet Transfers',
                    subtitle: 'Notifications when funds settle or transfer to your bank',
                    icon: Icons.account_balance_wallet_outlined,
                    value: _payouts && _masterPush,
                    enabled: _masterPush,
                    onChanged: (val) {
                      setState(() => _payouts = val);
                      _savePreference('notif_payouts', val);
                    },
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.lg),

            // Reports & Updates
            _buildSectionHeader('Reports & Platform News'),
            Container(
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(AppRadius.lg),
                border: Border.all(color: AppColors.slate200),
              ),
              child: Column(
                children: [
                  _buildToggleTile(
                    title: 'Weekly Performance Digest',
                    subtitle: 'Summary of weekly revenue, views, and order volume',
                    icon: Icons.insights_outlined,
                    value: _weeklyDigest && _masterPush,
                    enabled: _masterPush,
                    onChanged: (val) {
                      setState(() => _weeklyDigest = val);
                      _savePreference('notif_digest', val);
                    },
                  ),
                  const Divider(height: 1, indent: 56, color: AppColors.slate100),
                  _buildToggleTile(
                    title: 'Feature Updates & Merchant Tips',
                    subtitle: 'News about seller tools, promos and fee updates',
                    icon: Icons.campaign_outlined,
                    value: _marketing && _masterPush,
                    enabled: _masterPush,
                    onChanged: (val) {
                      setState(() => _marketing = val);
                      _savePreference('notif_marketing', val);
                    },
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.xxl),
          ],
        ),
      ),
    );
  }

  Widget _buildSectionHeader(String title) {
    return Padding(
      padding: const EdgeInsets.only(left: 4, bottom: 8),
      child: Text(
        title,
        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.slate500),
      ),
    );
  }

  Widget _buildToggleTile({
    required String title,
    required String subtitle,
    required IconData icon,
    required bool value,
    required bool enabled,
    required ValueChanged<bool> onChanged,
  }) {
    return ListTile(
      leading: Container(
        padding: const EdgeInsets.all(7),
        decoration: BoxDecoration(
          color: AppColors.brand50,
          borderRadius: BorderRadius.circular(AppRadius.sm),
        ),
        child: Icon(icon, size: 20, color: AppColors.brand600),
      ),
      title: Text(
        title,
        style: TextStyle(
          fontSize: 14,
          fontWeight: FontWeight.w600,
          color: enabled ? AppColors.ink : AppColors.slate400,
        ),
      ),
      subtitle: Text(
        subtitle,
        style: const TextStyle(fontSize: 11, color: AppColors.slate500),
      ),
      trailing: Switch.adaptive(
        value: value,
        activeColor: AppColors.brand500,
        onChanged: enabled ? onChanged : null,
      ),
    );
  }
}
