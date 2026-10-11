import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../providers/seller_auth_provider.dart';
import '../auth/login_screen.dart';
import '../catalog/catalog_screen.dart';
import '../messages/messages_screen.dart';
import '../orders/orders_screen.dart';
import '../store/seller_help_screen.dart';
import '../store/vendor_terms_screen.dart';
import 'subscreens/about_screen.dart';
import 'subscreens/account_health_screen.dart';
import 'subscreens/help_center_screen.dart';
import 'subscreens/inspection_queue_screen.dart';
import 'subscreens/notification_settings_screen.dart';
import 'subscreens/payment_settings_screen.dart';
import 'subscreens/store_settings_screen.dart';
import 'subscreens/subscription_plan_screen.dart';
import 'subscreens/support_tickets_screen.dart';
import 'subscreens/update_profile_screen.dart';
import 'subscreens/verification_status_screen.dart';
import 'subscreens/warehouse_deposit_screen.dart';

class SettingsScreen extends StatefulWidget {
  final void Function(int index)? onNavigateTab;
  const SettingsScreen({super.key, this.onNavigateTab});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  String _currentLanguage = 'English';

  @override
  void initState() {
    super.initState();
    _loadLanguagePreference();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        context.read<SellerAuthProvider>().refreshProfile();
      }
    });
  }

  Future<void> _loadLanguagePreference() async {
    final prefs = await SharedPreferences.getInstance();
    final lang = prefs.getString('app_language') ?? 'English';
    if (mounted) {
      setState(() => _currentLanguage = lang);
    }
  }

  void _confirmLogout(BuildContext context, SellerAuthProvider auth) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Log Out'),
        content: const Text('Are you sure you want to end your current seller session?'),
        actions: [
          TextButton(onPressed: () => Navigator.of(ctx).pop(), child: const Text('Cancel')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.danger),
            onPressed: () async {
              Navigator.of(ctx).pop();
              await auth.logout();
              if (context.mounted) {
                Navigator.of(context).pushAndRemoveUntil(
                  MaterialPageRoute(builder: (_) => const LoginScreen()),
                  (route) => false,
                );
              }
            },
            child: const Text('Log Out'),
          ),
        ],
      ),
    );
  }

  void _showLanguageBottomSheet() {
    final languages = [
      {'name': 'English', 'native': 'English (United States / Global)', 'code': 'en'},
      {'name': 'العربية', 'native': 'Arabic (Qatar & GCC)', 'code': 'ar'},
      {'name': 'Français', 'native': 'French', 'code': 'fr'},
      {'name': 'हिन्दी', 'native': 'Hindi', 'code': 'hi'},
    ];

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (ctx) => Container(
        padding: const EdgeInsets.all(AppSpacing.lg),
        decoration: const BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.vertical(top: Radius.circular(AppRadius.xl)),
        ),
        child: SafeArea(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: AppColors.slate300,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: AppSpacing.md),
              const Text(
                'Select App Language',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.ink),
              ),
              const SizedBox(height: 4),
              const Text(
                'Choose your preferred language for the merchant portal',
                style: TextStyle(fontSize: 12, color: AppColors.slate500),
              ),
              const SizedBox(height: AppSpacing.md),
              ...languages.map((lang) {
                final isSelected = _currentLanguage == lang['name'];
                return Container(
                  margin: const EdgeInsets.only(bottom: 8),
                  decoration: BoxDecoration(
                    color: isSelected ? AppColors.brand50 : AppColors.slate50,
                    borderRadius: BorderRadius.circular(AppRadius.md),
                    border: Border.all(
                      color: isSelected ? AppColors.brand500 : AppColors.slate200,
                      width: isSelected ? 1.5 : 1,
                    ),
                  ),
                  child: ListTile(
                    title: Text(
                      lang['name']!,
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w700,
                        color: isSelected ? AppColors.brand600 : AppColors.ink,
                      ),
                    ),
                    subtitle: Text(
                      lang['native']!,
                      style: const TextStyle(fontSize: 11, color: AppColors.slate500),
                    ),
                    trailing: isSelected
                        ? const Icon(Icons.check_circle_rounded, color: AppColors.brand500, size: 20)
                        : null,
                    onTap: () async {
                      final prefs = await SharedPreferences.getInstance();
                      await prefs.setString('app_language', lang['name']!);
                      if (!mounted) return;
                      setState(() => _currentLanguage = lang['name']!);
                      if (ctx.mounted) Navigator.of(ctx).pop();
                      AppSnackBar.success(context, 'Language switched to ${lang['name']}');
                    },
                  ),
                );
              }),
            ],
          ),
        ),
      ),
    );
  }

  void _showOtherAccountBottomSheet(BuildContext context, SellerAuthProvider auth) {
    final user = auth.user;

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (ctx) => Container(
        padding: const EdgeInsets.all(AppSpacing.lg),
        decoration: const BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.vertical(top: Radius.circular(AppRadius.xl)),
        ),
        child: SafeArea(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: AppColors.slate300,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: AppSpacing.md),
              const Text(
                'Switch Account',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.ink),
              ),
              const SizedBox(height: 4),
              const Text(
                'Manage multiple seller profiles or switch merchant portals',
                style: TextStyle(fontSize: 12, color: AppColors.slate500),
              ),
              const SizedBox(height: AppSpacing.md),

              // Current Active Account Card
              Container(
                padding: const EdgeInsets.all(AppSpacing.md),
                decoration: BoxDecoration(
                  color: AppColors.brand50,
                  borderRadius: BorderRadius.circular(AppRadius.lg),
                  border: Border.all(color: AppColors.brand500, width: 1.5),
                ),
                child: Row(
                  children: [
                    Container(
                      width: 46,
                      height: 46,
                      decoration: BoxDecoration(
                        color: AppColors.brand500,
                        borderRadius: BorderRadius.circular(AppRadius.md),
                      ),
                      clipBehavior: Clip.antiAlias,
                      child: user?.logoUrl != null
                          ? Image.network(user!.logoUrl!, fit: BoxFit.cover, errorBuilder: (_, _, _) => const Icon(Icons.storefront, color: Colors.white))
                          : const Icon(Icons.storefront, color: Colors.white),
                    ),
                    const SizedBox(width: AppSpacing.md),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            user?.storeName ?? 'Current Store',
                            style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: AppColors.ink),
                          ),
                          Text(
                            user?.email ?? '',
                            style: const TextStyle(fontSize: 11, color: AppColors.slate500),
                          ),
                        ],
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: AppColors.success,
                        borderRadius: BorderRadius.circular(AppRadius.pill),
                      ),
                      child: const Text('Active', style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: Colors.white)),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.lg),

              // Add Another Account Button
              OutlinedButton.icon(
                style: OutlinedButton.styleFrom(
                  minimumSize: const Size.fromHeight(48),
                  side: const BorderSide(color: AppColors.slate300),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
                ),
                icon: const Icon(Icons.add_circle_outline_rounded, size: 20, color: AppColors.brand600),
                label: const Text('Add or Log In with Another Account', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.brand600)),
                onPressed: () {
                  Navigator.of(ctx).pop();
                  Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => const LoginScreen()),
                  );
                },
              ),
              const SizedBox(height: AppSpacing.sm),
            ],
          ),
        ),
      ),
    );
  }

  void _showFeedbackBottomSheet(BuildContext context) {
    int rating = 5;
    String category = 'App Experience';
    final textController = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setSheetState) => Padding(
          padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewInsets.bottom),
          child: Container(
            padding: const EdgeInsets.all(AppSpacing.lg),
            decoration: const BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.vertical(top: Radius.circular(AppRadius.xl)),
            ),
            child: SafeArea(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      decoration: BoxDecoration(
                        color: AppColors.slate300,
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  const SizedBox(height: AppSpacing.md),
                  const Text(
                    'Share Your Feedback',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.ink),
                  ),
                  const SizedBox(height: 4),
                  const Text(
                    'Help us improve your selling and store management experience',
                    style: TextStyle(fontSize: 12, color: AppColors.slate500),
                  ),
                  const SizedBox(height: AppSpacing.lg),

                  // Rating Stars
                  Center(
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: List.generate(5, (index) {
                        final starIndex = index + 1;
                        return IconButton(
                          iconSize: 32,
                          icon: Icon(
                            starIndex <= rating ? Icons.star_rounded : Icons.star_outline_rounded,
                            color: Colors.amber,
                          ),
                          onPressed: () => setSheetState(() => rating = starIndex),
                        );
                      }),
                    ),
                  ),
                  const SizedBox(height: AppSpacing.md),

                  // Feedback Category Tags
                  Wrap(
                    spacing: 8,
                    children: ['App Experience', 'Feature Request', 'Bug Report', 'General'].map((cat) {
                      final isSelected = category == cat;
                      return ChoiceChip(
                        label: Text(cat),
                        selected: isSelected,
                        selectedColor: AppColors.brand500,
                        labelStyle: TextStyle(
                          color: isSelected ? Colors.white : AppColors.slate700,
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                        ),
                        onSelected: (_) => setSheetState(() => category = cat),
                      );
                    }).toList(),
                  ),
                  const SizedBox(height: AppSpacing.md),

                  // Text Field
                  TextField(
                    controller: textController,
                    maxLines: 4,
                    decoration: InputDecoration(
                      hintText: 'Tell us what you liked or how we can improve...',
                      hintStyle: const TextStyle(fontSize: 13, color: AppColors.slate400),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
                      contentPadding: const EdgeInsets.all(12),
                    ),
                  ),
                  const SizedBox(height: AppSpacing.lg),

                  // Submit Button
                  ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.brand500,
                      foregroundColor: Colors.white,
                      minimumSize: const Size.fromHeight(48),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
                    ),
                    onPressed: () {
                      Navigator.of(ctx).pop();
                      AppSnackBar.success(context, 'Thank you! Your feedback has been received.');
                    },
                    child: const Text('Submit Feedback', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 14)),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<SellerAuthProvider>();
    final user = authProvider.user;

    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        title: const Text('Settings & Merchant Portal'),
      ),
      body: SingleChildScrollView(
        child: Column(
          children: [
            // 1. Cover Banner & Logo Header
            Stack(
              clipBehavior: Clip.none,
              alignment: Alignment.bottomLeft,
              children: [
                Container(
                  height: 110,
                  width: double.infinity,
                  decoration: const BoxDecoration(color: AppColors.slate800),
                  child: user?.coverUrl != null
                      ? Image.network(user!.coverUrl!, fit: BoxFit.cover, errorBuilder: (_, _, _) => Container(color: AppColors.slate800))
                      : Container(color: AppColors.slate800),
                ),
                Positioned(
                  bottom: -28,
                  left: 20,
                  child: Container(
                    width: 68,
                    height: 68,
                    decoration: BoxDecoration(
                      color: AppColors.surface,
                      borderRadius: BorderRadius.circular(AppRadius.lg),
                      border: Border.all(color: AppColors.surface, width: 3),
                      boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.08), blurRadius: 8, offset: const Offset(0, 2))],
                    ),
                    clipBehavior: Clip.antiAlias,
                    child: user?.logoUrl != null
                        ? Image.network(user!.logoUrl!, fit: BoxFit.cover, errorBuilder: (_, _, _) => const Icon(Icons.storefront, size: 32, color: AppColors.brand500))
                        : const Icon(Icons.storefront, size: 32, color: AppColors.brand500),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 38),

            // 2. Merchant Info Title
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(user?.storeName ?? 'My Store', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.ink)),
                        Text(user?.email ?? '', style: const TextStyle(fontSize: 12, color: AppColors.slate500)),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: user?.isKycApproved == true ? AppColors.successBg : AppColors.brand50,
                      borderRadius: BorderRadius.circular(AppRadius.pill),
                      border: Border.all(color: user?.isKycApproved == true ? AppColors.success.withOpacity(0.3) : AppColors.brand200),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(user?.isKycApproved == true ? Icons.verified_rounded : Icons.pending_actions_rounded, size: 14, color: user?.isKycApproved == true ? AppColors.success : AppColors.brand700),
                        const SizedBox(width: 4),
                        Text(user?.isKycApproved == true ? 'Verified' : 'Pending', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: user?.isKycApproved == true ? AppColors.success : AppColors.brand700)),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.lg),

            // 3. STORE & PROFILE MANAGEMENT
            _buildSectionHeader('Store & Profile Management'),
            _buildMenuCard(context, [
              _MenuItem(
                icon: Icons.store_outlined,
                title: 'Store Setting',
                subtitle: 'Store name, handle, description & address',
                destination: const StoreSettingsScreen(),
              ),
              _MenuItem(
                icon: Icons.person_outline_rounded,
                title: 'Update Profile',
                subtitle: 'Merchant owner details, phone & email',
                destination: const UpdateProfileScreen(),
              ),
              _MenuItem(
                icon: Icons.verified_user_outlined,
                title: 'Verification Status',
                subtitle: 'KYC identity compliance & status badge',
                badgeText: user?.isKycApproved == true ? 'Verified' : 'Pending',
                badgeColor: user?.isKycApproved == true ? AppColors.success : AppColors.warning,
                destination: const VerificationStatusScreen(),
              ),
              _MenuItem(
                icon: Icons.card_membership_outlined,
                title: 'Subscription Plan',
                subtitle: 'Plan tiers & available listing credits',
                destination: const SubscriptionPlanScreen(),
              ),
            ]),
            const SizedBox(height: AppSpacing.lg),

            // 4. PERFORMANCE & ACCOUNT HEALTH
            _buildSectionHeader('Performance & Analytics'),
            _buildMenuCard(context, [
              _MenuItem(
                icon: Icons.health_and_safety_outlined,
                title: 'Account Health',
                subtitle: 'Order defect rate, SLA scores & policy standing',
                badgeText: 'Good',
                badgeColor: AppColors.success,
                destination: const AccountHealthScreen(),
              ),
            ]),
            const SizedBox(height: AppSpacing.lg),

            // 5. OPERATIONS & LOGISTICS
            _buildSectionHeader('Operations & Logistics'),
            _buildMenuCard(context, [
              _MenuItem(
                icon: Icons.receipt_long_outlined,
                title: 'Orders',
                subtitle: 'Manage client orders, fulfillment & tracking',
                destination: const OrdersScreen(),
              ),
              _MenuItem(
                icon: Icons.account_balance_outlined,
                title: 'Payment Settings',
                subtitle: 'Bank account, IBAN & payout gateways',
                destination: const PaymentSettingsScreen(),
              ),
              _MenuItem(
                icon: Icons.fact_check_outlined,
                title: 'Inspection Queue',
                subtitle: 'Product verification appointments & station visits',
                destination: const InspectionQueueScreen(),
              ),
              _MenuItem(
                icon: Icons.warehouse_outlined,
                title: 'Warehouse Deposit',
                subtitle: 'Consignment deposits to fulfillment hubs',
                destination: const WarehouseDepositScreen(),
              ),
            ]),
            const SizedBox(height: AppSpacing.lg),

            // 6. CATALOG SHORTCUTS
            _buildSectionHeader('Catalog & Inventory Management'),
            _buildMenuCard(context, [
              _MenuItem(
                icon: Icons.inventory_2_outlined,
                title: 'Product Catalog',
                subtitle: 'Active listings, publish status & drafts',
                destination: const CatalogScreen(initialTab: 0),
              ),
              _MenuItem(
                icon: Icons.tune_rounded,
                title: 'Manage Inventory',
                subtitle: 'Stock counts, low stock alerts & quick pricing',
                destination: const CatalogScreen(initialTab: 1),
              ),
            ]),
            const SizedBox(height: AppSpacing.lg),

            // 7. PREFERENCES & APP SETTINGS (Notifications, Language, Other Account)
            _buildSectionHeader('Preferences & Accounts'),
            _buildMenuCard(context, [
              _MenuItem(
                icon: Icons.notifications_none_rounded,
                title: 'Notifications',
                subtitle: 'Order alerts, chat notifications & sound preferences',
                destination: const NotificationSettingsScreen(),
              ),
              _MenuItem(
                icon: Icons.language_rounded,
                title: 'Language',
                subtitle: 'Change app language',
                badgeText: _currentLanguage,
                badgeColor: AppColors.brand500,
                onTap: _showLanguageBottomSheet,
              ),
              _MenuItem(
                icon: Icons.switch_account_outlined,
                title: 'Other Account',
                subtitle: 'Switch or link another merchant account',
                onTap: () => _showOtherAccountBottomSheet(context, authProvider),
              ),
            ]),
            const SizedBox(height: AppSpacing.lg),

            // 8. SUPPORT & FEEDBACK (Help Center, Messages, Tickets, Feedback)
            _buildSectionHeader('Support & Communications'),
            _buildMenuCard(context, [
              _MenuItem(
                icon: Icons.help_center_outlined,
                title: 'Help Center',
                subtitle: 'Guides, FAQs, seller rules & resolution center',
                destination: const HelpCenterScreen(),
              ),
              _MenuItem(
                icon: Icons.chat_bubble_outline_rounded,
                title: 'Messages',
                subtitle: 'Direct customer messages & chat with support',
                destination: const MessagesScreen(),
              ),
              _MenuItem(
                icon: Icons.support_agent_rounded,
                title: 'Support Tickets',
                subtitle: 'Customer care tickets & inquiry thread',
                destination: const SupportTicketsScreen(),
              ),
              _MenuItem(
                icon: Icons.feedback_outlined,
                title: 'Feedback',
                subtitle: 'Send suggestions or report an issue',
                onTap: () => _showFeedbackBottomSheet(context),
              ),
            ]),
            const SizedBox(height: AppSpacing.lg),

            // 9. ABOUT & LEGAL (About, Guidelines, Terms, Logout)
            _buildSectionHeader('About & Legal'),
            _buildMenuCard(context, [
              _MenuItem(
                icon: Icons.info_outline_rounded,
                title: 'About',
                subtitle: 'App version, openbox mission & release info',
                destination: const AboutScreen(),
              ),
              _MenuItem(
                icon: Icons.help_outline_rounded,
                title: 'Seller Help & Guidelines',
                subtitle: 'Standards, fulfillment SLAs & FAQ answers',
                destination: const SellerHelpScreen(),
              ),
              _MenuItem(
                icon: Icons.description_outlined,
                title: 'Vendor Terms of Service',
                subtitle: 'Platform contractual merchant terms',
                destination: const VendorTermsScreen(),
              ),
              _MenuItem(
                icon: Icons.logout_rounded,
                title: 'Log Out',
                subtitle: 'Sign out of your seller session',
                iconColor: AppColors.danger,
                titleColor: AppColors.danger,
                onTap: () => _confirmLogout(context, authProvider),
              ),
            ]),
            const SizedBox(height: AppSpacing.xxxl),
          ],
        ),
      ),
    );
  }

  Widget _buildSectionHeader(String title) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: 6),
      child: Align(
        alignment: Alignment.centerLeft,
        child: Text(
          title,
          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.slate500, letterSpacing: 0.2),
        ),
      ),
    );
  }

  Widget _buildMenuCard(BuildContext context, List<_MenuItem> items) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
      child: Container(
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(AppRadius.lg),
          border: Border.all(color: AppColors.slate200.withOpacity(0.8)),
        ),
        child: Column(
          children: items.asMap().entries.map((entry) {
            final idx = entry.key;
            final item = entry.value;
            final isLast = idx == items.length - 1;

            return Column(
              children: [
                ListTile(
                  leading: Container(
                    padding: const EdgeInsets.all(7),
                    decoration: BoxDecoration(
                      color: (item.iconColor ?? AppColors.brand500).withOpacity(0.08),
                      borderRadius: BorderRadius.circular(AppRadius.sm),
                    ),
                    child: Icon(item.icon, size: 20, color: item.iconColor ?? AppColors.brand600),
                  ),
                  title: Text(
                    item.title,
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: item.titleColor ?? AppColors.ink),
                  ),
                  subtitle: Text(
                    item.subtitle,
                    style: const TextStyle(fontSize: 11, color: AppColors.slate500),
                  ),
                  trailing: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      if (item.badgeText != null) ...[
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: (item.badgeColor ?? AppColors.brand500).withOpacity(0.12),
                            borderRadius: BorderRadius.circular(AppRadius.pill),
                          ),
                          child: Text(
                            item.badgeText!,
                            style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: item.badgeColor ?? AppColors.brand600),
                          ),
                        ),
                        const SizedBox(width: 4),
                      ],
                      const Icon(Icons.chevron_right, size: 20, color: AppColors.slate400),
                    ],
                  ),
                  onTap: () {
                    if (item.onTap != null) {
                      item.onTap!();
                    } else if (item.destination != null) {
                      Navigator.of(context).push(
                        MaterialPageRoute(builder: (_) => item.destination!),
                      );
                    }
                  },
                ),
                if (!isLast) const Divider(height: 1, indent: 56, color: AppColors.slate100),
              ],
            );
          }).toList(),
        ),
      ),
    );
  }
}

class _MenuItem {
  final IconData icon;
  final String title;
  final String subtitle;
  final Widget? destination;
  final VoidCallback? onTap;
  final String? badgeText;
  final Color? badgeColor;
  final Color? iconColor;
  final Color? titleColor;

  const _MenuItem({
    required this.icon,
    required this.title,
    required this.subtitle,
    this.destination,
    this.onTap,
    this.badgeText,
    this.badgeColor,
    this.iconColor,
    this.titleColor,
  });
}
