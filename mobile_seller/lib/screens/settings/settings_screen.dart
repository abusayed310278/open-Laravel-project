import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../../core/network/api_client.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../data/models/seller_user.dart';
import '../../providers/seller_auth_provider.dart';
import '../auth/login_screen.dart';
import '../auth/register_screen.dart';
import '../catalog/catalog_screen.dart';
import '../dashboard/dashboard_screen.dart';
import '../messages/messages_screen.dart';
import '../orders/orders_screen.dart';
import '../store/seller_help_screen.dart';
import '../store/vendor_terms_screen.dart';
import '../wallet/wallet_screen.dart';
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
import 'subscreens/seller_center_screen.dart';
import 'subscreens/change_password_screen.dart';

class SettingsScreen extends StatefulWidget {
  final void Function(int index)? onNavigateTab;
  const SettingsScreen({super.key, this.onNavigateTab});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  String _currentLanguage = 'English';
  String? _kycStatus;
  bool? _isKycApproved;

  @override
  void initState() {
    super.initState();
    _loadLanguagePreference();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        final auth = context.read<SellerAuthProvider>();
        if (auth.isAuthenticated) {
          auth.refreshProfile();
          _fetchKycStatus();
        }
      }
    });
  }

  Future<void> _fetchKycStatus() async {
    try {
      final res = await ApiClient().get('/verification');
      if (res != null && mounted) {
        setState(() {
          if (res['kyc_status'] != null) {
            _kycStatus = res['kyc_status'].toString();
          }
          if (res['is_kyc_approved'] != null) {
            _isKycApproved = res['is_kyc_approved'] == true || res['is_kyc_approved'] == 1;
          }
        });
      }
    } catch (_) {
      // Fallback silently to user model
    }
  }

  ({String label, Color color, Color bgColor, IconData icon}) _resolveKycDisplay(SellerUser? user) {
    final raw = (_kycStatus ?? user?.effectiveKycStatus ?? 'pending').toLowerCase().trim();
    final isApproved = _isKycApproved ?? (user?.isKycApproved == true || raw == 'approved' || raw == 'verified');

    if (isApproved) {
      return (
        label: 'Verified',
        color: AppColors.success,
        bgColor: AppColors.successBg,
        icon: Icons.verified_rounded,
      );
    }

    if (raw == 'rejected' || raw == 'declined') {
      return (
        label: 'Rejected',
        color: AppColors.danger,
        bgColor: AppColors.dangerBg,
        icon: Icons.cancel_rounded,
      );
    }

    if (raw == 'under_review' || raw == 'in_review' || raw == 'review') {
      return (
        label: 'Under Review',
        color: AppColors.info,
        bgColor: AppColors.infoBg,
        icon: Icons.hourglass_top_rounded,
      );
    }

    if (raw == 'submitted') {
      return (
        label: 'Submitted',
        color: AppColors.brand700,
        bgColor: AppColors.brand50,
        icon: Icons.mark_email_read_rounded,
      );
    }

    if (raw == 'unverified' || raw == 'draft' || raw == 'none') {
      return (
        label: 'Unverified',
        color: AppColors.slate600,
        bgColor: AppColors.slate100,
        icon: Icons.shield_outlined,
      );
    }

    return (
      label: 'Pending',
      color: AppColors.brand700,
      bgColor: AppColors.brand50,
      icon: Icons.pending_actions_rounded,
    );
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
                        color: (user?.isStoreActive ?? false) ? AppColors.success : AppColors.danger,
                        borderRadius: BorderRadius.circular(AppRadius.pill),
                      ),
                      child: Text(
                        (user?.isStoreActive ?? false) ? 'Active' : 'Inactive',
                        style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: Colors.white),
                      ),
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
    final isAuthenticated = authProvider.isAuthenticated;
    final user = authProvider.user;

    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        title: Text(isAuthenticated ? 'Settings & Merchant Portal' : 'Settings'),
      ),
      body: RefreshIndicator(
        color: AppColors.brand500,
        onRefresh: () async {
          if (isAuthenticated) {
            await Future.wait([
              authProvider.refreshProfile(),
              _fetchKycStatus(),
            ]);
          }
        },
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: isAuthenticated
              ? _buildAuthenticatedContent(context, authProvider, user)
              : _buildGuestContent(context),
        ),
      ),
    );
  }

  Widget _buildGuestContent(BuildContext context) {
    return Column(
      children: [
        // 1. Guest Account Card
        Container(
          margin: const EdgeInsets.all(AppSpacing.lg),
          padding: const EdgeInsets.all(AppSpacing.xl),
          decoration: BoxDecoration(
            color: AppColors.brand500,
            borderRadius: BorderRadius.circular(AppRadius.lg),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.08),
                blurRadius: 10,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Column(
            children: [
              Row(
                children: [
                  Container(
                    width: 52,
                    height: 52,
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.22),
                      shape: BoxShape.circle,
                      border: Border.all(color: AppColors.ink, width: 1.5),
                    ),
                    child: const Icon(Icons.person_outline_rounded, color: AppColors.ink, size: 28),
                  ),
                  const SizedBox(width: AppSpacing.md),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: const [
                        Text(
                          'Guest User',
                          style: TextStyle(
                            fontSize: 17,
                            fontWeight: FontWeight.w800,
                            color: AppColors.ink,
                          ),
                        ),
                        SizedBox(height: 3),
                        Text(
                          'Sign in to manage orders, wishlist & merchant store',
                          style: TextStyle(
                            fontSize: 12,
                            color: AppColors.brand900,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: AppSpacing.lg),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppColors.ink,
                        side: const BorderSide(color: AppColors.ink, width: 1.5),
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
                      ),
                      onPressed: () => Navigator.of(context).push(
                        MaterialPageRoute(builder: (_) => const LoginScreen()),
                      ),
                      icon: const Icon(Icons.login_rounded, size: 18),
                      label: const Text('Sign In', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                    ),
                  ),
                  const SizedBox(width: AppSpacing.md),
                  Expanded(
                    child: OutlinedButton.icon(
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppColors.ink,
                        side: const BorderSide(color: AppColors.ink, width: 1.5),
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
                      ),
                      onPressed: () => Navigator.of(context).push(
                        MaterialPageRoute(builder: (_) => const RegisterScreen()),
                      ),
                      icon: const Icon(Icons.person_add_outlined, size: 18),
                      label: const Text('Register', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),

        // 2. Become a Seller Promo
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
          child: Container(
            padding: const EdgeInsets.all(AppSpacing.md),
            decoration: BoxDecoration(
              color: AppColors.brand50,
              borderRadius: BorderRadius.circular(AppRadius.lg),
              border: Border.all(color: AppColors.brand200),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: const BoxDecoration(
                    color: AppColors.brand500,
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(Icons.storefront_rounded, color: Colors.white, size: 20),
                ),
                const SizedBox(width: AppSpacing.md),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: const [
                      Text(
                        'Become a Seller',
                        style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.brand800),
                      ),
                      SizedBox(height: 2),
                      Text(
                        'Sell verified refurbished & new devices',
                        style: TextStyle(fontSize: 11, color: AppColors.slate600),
                      ),
                    ],
                  ),
                ),
                TextButton(
                  onPressed: () => Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => const RegisterScreen()),
                  ),
                  child: const Text('Join', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13)),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: AppSpacing.lg),

        // 3. Basic Preferences Section
        _buildSectionHeader('Preferences'),
        _buildMenuCard(context, [
          _MenuItem(
            icon: Icons.language_rounded,
            title: 'Language',
            subtitle: 'Change app language',
            badgeText: _currentLanguage,
            badgeColor: AppColors.brand500,
            onTap: _showLanguageBottomSheet,
          ),
        ]),
        const SizedBox(height: AppSpacing.lg),

        // 4. Help & Support Section
        _buildSectionHeader('Help & Support'),
        _buildMenuCard(context, [
          _MenuItem(
            icon: Icons.help_center_outlined,
            title: 'Help Center',
            subtitle: 'Guides, FAQs & resolution center',
            iconColor: AppColors.brand500,
            destination: const HelpCenterScreen(),
          ),
          _MenuItem(
            icon: Icons.feedback_outlined,
            title: 'Feedback',
            subtitle: 'Send suggestions or report an issue',
            iconColor: AppColors.brand500,
            onTap: () => _showFeedbackBottomSheet(context),
          ),
        ]),
        const SizedBox(height: AppSpacing.lg),

        // 5. About & Legal Section
        _buildSectionHeader('About & Legal'),
        _buildMenuCard(context, [
          _MenuItem(
            icon: Icons.info_outline_rounded,
            title: 'About Openbox',
            subtitle: 'App version, mission & release information',
            iconColor: AppColors.brand500,
            destination: const AboutScreen(),
          ),
          _MenuItem(
            icon: Icons.help_outline_rounded,
            title: 'Seller Guidelines',
            subtitle: 'Refurbished grading standards & FAQs',
            iconColor: AppColors.brand500,
            destination: const SellerHelpScreen(),
          ),
          _MenuItem(
            icon: Icons.description_outlined,
            title: 'Terms of Service',
            subtitle: 'Platform user terms & policy',
            iconColor: AppColors.brand500,
            destination: const VendorTermsScreen(),
          ),
        ]),
        const SizedBox(height: AppSpacing.xxl),
        _buildFooter(),
        const SizedBox(height: AppSpacing.xxl),
      ],
    );
  }

  Widget _buildAuthenticatedContent(BuildContext context, SellerAuthProvider authProvider, SellerUser? user) {
    final kycDisplay = _resolveKycDisplay(user);

    return Column(
      children: [
        // 1. Cover Banner & Logo Header
            Stack(
              clipBehavior: Clip.none,
              alignment: Alignment.bottomLeft,
              children: [
                Container(
                  height: 124,
                  width: double.infinity,
                  decoration: const BoxDecoration(
                    gradient: LinearGradient(
                      colors: [AppColors.slate900, AppColors.slate700],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                  ),
                  child: Stack(
                    fit: StackFit.expand,
                    children: [
                      if (user?.coverUrl != null)
                        Image.network(
                          user!.coverUrl!,
                          fit: BoxFit.cover,
                          errorBuilder: (_, _, _) => const SizedBox.shrink(),
                        ),
                      DecoratedBox(
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            colors: [
                              Colors.black.withValues(alpha: 0.05),
                              Colors.black.withValues(alpha: 0.35),
                            ],
                            begin: Alignment.topCenter,
                            end: Alignment.bottomCenter,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                Positioned(
                  bottom: -28,
                  left: 20,
                  child: Stack(
                    clipBehavior: Clip.none,
                    children: [
                      Container(
                        width: 68,
                        height: 68,
                        decoration: BoxDecoration(
                          color: AppColors.surface,
                          borderRadius: BorderRadius.circular(AppRadius.lg),
                          border: Border.all(color: AppColors.surface, width: 3),
                          boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.1), blurRadius: 10, offset: const Offset(0, 3))],
                        ),
                        clipBehavior: Clip.antiAlias,
                        child: user?.logoUrl != null
                            ? Image.network(user!.logoUrl!, fit: BoxFit.cover, errorBuilder: (_, _, _) => const Icon(Icons.storefront, size: 32, color: AppColors.brand500))
                            : const Icon(Icons.storefront, size: 32, color: AppColors.brand500),
                      ),
                      Positioned(
                        bottom: -4,
                        right: -4,
                        child: GestureDetector(
                          onTap: () => Navigator.of(context).push(
                            MaterialPageRoute(builder: (_) => const UpdateProfileScreen()),
                          ),
                          child: Container(
                            width: 24,
                            height: 24,
                            decoration: BoxDecoration(
                              color: AppColors.brand500,
                              shape: BoxShape.circle,
                              border: Border.all(color: AppColors.surface, width: 2),
                              boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.12), blurRadius: 4, offset: const Offset(0, 1))],
                            ),
                            child: const Icon(Icons.edit_rounded, size: 12, color: Colors.white),
                          ),
                        ),
                      ),
                    ],
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
                        Text(
                          user?.storeName ?? 'My Store',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.ink),
                        ),
                        const SizedBox(height: 2),
                        Text(user?.email ?? '', style: const TextStyle(fontSize: 12, color: AppColors.slate500)),
                      ],
                    ),
                  ),
                  const SizedBox(width: AppSpacing.sm),
                  GestureDetector(
                    onTap: () async {
                      await Navigator.of(context).push(
                        MaterialPageRoute(builder: (_) => const VerificationStatusScreen()),
                      );
                      _fetchKycStatus();
                      if (context.mounted) {
                        context.read<SellerAuthProvider>().refreshProfile();
                      }
                    },
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                      decoration: BoxDecoration(
                        color: kycDisplay.bgColor,
                        borderRadius: BorderRadius.circular(AppRadius.pill),
                        border: Border.all(color: kycDisplay.color.withValues(alpha: 0.3)),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(kycDisplay.icon, size: 14, color: kycDisplay.color),
                          const SizedBox(width: 4),
                          Text(
                            kycDisplay.label,
                            style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: kycDisplay.color),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.md),

            // 3. Quick Access Commerce Bar: Wallet, Credits, Catalog
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(AppRadius.lg),
                  border: Border.all(color: AppColors.slate200.withValues(alpha: 0.8)),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.02),
                      blurRadius: 8,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: _buildQuickActionTile(
                        icon: Icons.account_balance_wallet_outlined,
                        iconColor: AppColors.brand500,
                        label: 'Wallet',
                        caption: 'Earnings & Payouts',
                        onTap: () => Navigator.of(context).push(
                          MaterialPageRoute(builder: (_) => const WalletScreen()),
                        ),
                      ),
                    ),
                    Container(height: 36, width: 1, color: AppColors.slate200),
                    Expanded(
                      child: _buildQuickActionTile(
                        icon: Icons.stars_rounded,
                        iconColor: AppColors.brand500,
                        label: 'Credits',
                        caption: '${user?.listingCredits ?? 0} Available',
                        onTap: () => Navigator.of(context).push(
                          MaterialPageRoute(builder: (_) => const SubscriptionPlanScreen()),
                        ),
                      ),
                    ),
                    Container(height: 36, width: 1, color: AppColors.slate200),
                    Expanded(
                      child: _buildQuickActionTile(
                        icon: Icons.inventory_2_outlined,
                        iconColor: AppColors.brand500,
                        label: 'Catalog',
                        caption: 'Items & Stock',
                        onTap: () => Navigator.of(context).push(
                          MaterialPageRoute(builder: (_) => const CatalogScreen(initialTab: 0)),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: AppSpacing.lg),

            // 4. SELLER HUB & METRICS
            _buildSectionHeader('Merchant Hub & Analytics'),
            _buildMenuCard(context, [
              _MenuItem(
                icon: Icons.dashboard_outlined,
                title: 'Seller Dashboard',
                subtitle: 'Store performance, sales metrics, and listing stats',
                iconColor: AppColors.brand500,
                onTap: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const DashboardScreen()),
                ),
              ),
            ]),
            const SizedBox(height: AppSpacing.sm),

            // 5. FINANCES, WALLET & CREDITS
            _buildSectionHeader('Finances & Credits'),
            _buildMenuCard(context, [
              _MenuItem(
                icon: Icons.account_balance_wallet_outlined,
                title: 'Wallet & Payouts',
                subtitle: 'Available earnings balance, withdrawal requests & statement',
                iconColor: AppColors.brand500,
                destination: const WalletScreen(),
              ),
              _MenuItem(
                icon: Icons.card_membership_outlined,
                title: 'Subscription Plan',
                subtitle: 'Plan tiers, merchant benefits & credit quotas',
                iconColor: AppColors.brand500,
                destination: const SubscriptionPlanScreen(),
              ),
              _MenuItem(
                icon: Icons.account_balance_outlined,
                title: 'Payment Settings',
                subtitle: 'Bank accounts, IBAN & payout gateways',
                iconColor: AppColors.brand500,
                destination: const PaymentSettingsScreen(),
              ),
            ]),
            const SizedBox(height: AppSpacing.lg),

            // 5. CATALOG & PRODUCTS
            _buildSectionHeader('Catalog & Products'),
            _buildMenuCard(context, [
              _MenuItem(
                icon: Icons.inventory_2_outlined,
                title: 'Product Catalog',
                subtitle: 'Browse all products, active listings, categories & drafts',
                iconColor: AppColors.brand500,
                destination: const CatalogScreen(initialTab: 0),
              ),
              _MenuItem(
                icon: Icons.tune_rounded,
                title: 'Manage Inventory',
                subtitle: 'Stock counts, low stock alerts & quick pricing',
                iconColor: AppColors.brand500,
                destination: const CatalogScreen(initialTab: 1),
              ),
            ]),
            const SizedBox(height: AppSpacing.lg),

            // 6. STORE & PROFILE MANAGEMENT
            _buildSectionHeader('Store & Profile Management'),
            _buildMenuCard(context, [
              _MenuItem(
                icon: Icons.store_outlined,
                title: 'Store Setting',
                subtitle: 'Store name, handle, description & address',
                badgeText: (user?.isStoreActive ?? false) ? 'Active' : 'Inactive',
                badgeColor: (user?.isStoreActive ?? false) ? AppColors.success : AppColors.danger,
                destination: const StoreSettingsScreen(),
              ),
              _MenuItem(
                icon: Icons.person_outline_rounded,
                title: 'Update Profile',
                subtitle: 'Merchant owner details, phone & email',
                iconColor: AppColors.brand500,
                destination: const UpdateProfileScreen(),
              ),
              _MenuItem(
                icon: Icons.lock_outline_rounded,
                title: 'Change Password',
                subtitle: 'Update account password & security credentials',
                iconColor: AppColors.brand500,
                destination: const ChangePasswordScreen(),
              ),
              _MenuItem(
                icon: Icons.verified_user_outlined,
                title: 'Verification Status',
                subtitle: 'KYC identity compliance & status badge',
                badgeText: kycDisplay.label,
                badgeColor: kycDisplay.color,
                onTap: () async {
                  await Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => const VerificationStatusScreen()),
                  );
                  _fetchKycStatus();
                  if (context.mounted) {
                    context.read<SellerAuthProvider>().refreshProfile();
                  }
                },
              ),
            ]),
            const SizedBox(height: AppSpacing.lg),

            // 7. PERFORMANCE & ACCOUNT HEALTH
            _buildSectionHeader('Performance & Analytics'),
            _buildMenuCard(context, [
              _MenuItem(
                icon: Icons.health_and_safety_outlined,
                title: 'Account Health',
                subtitle: 'Order defect rate, SLA scores & policy standing',
                iconColor: AppColors.brand500,
                badgeText: 'Good',
                badgeColor: AppColors.success,
                destination: const AccountHealthScreen(),
              ),
            ]),
            const SizedBox(height: AppSpacing.lg),

            // 8. OPERATIONS & LOGISTICS
            _buildSectionHeader('Operations & Logistics'),
            _buildMenuCard(context, [
              _MenuItem(
                icon: Icons.receipt_long_outlined,
                title: 'Orders',
                subtitle: 'Manage client orders, fulfillment & tracking',
                iconColor: AppColors.brand500,
                destination: const OrdersScreen(),
              ),
              _MenuItem(
                icon: Icons.fact_check_outlined,
                title: 'Inspection Queue',
                subtitle: 'Product verification appointments & station visits',
                iconColor: AppColors.brand500,
                destination: const InspectionQueueScreen(),
              ),
              _MenuItem(
                icon: Icons.warehouse_outlined,
                title: 'Warehouse Deposit',
                subtitle: 'Consignment deposits to fulfillment hubs',
                iconColor: AppColors.brand500,
                destination: const WarehouseDepositScreen(),
              ),
              _MenuItem(
                icon: Icons.map_outlined,
                title: 'Seller Centers & Hubs',
                subtitle: 'Drop-off stations, warehouses & map pins',
                iconColor: AppColors.brand500,
                destination: const SellerCenterScreen(),
              ),
            ]),
            const SizedBox(height: AppSpacing.lg),

            // 7. PREFERENCES & APP SETTINGS (Notifications, Language, Other Account)
            _buildSectionHeader('Preferences & Accounts'),
            _buildMenuCard(context, [
              _MenuItem(
                icon: Icons.notifications_none_rounded,
                title: 'Setting',
                subtitle: 'Order alerts, chat notifications & sound preferences',
                iconColor: AppColors.brand500,
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
                iconColor: AppColors.brand500,
                onTap: () => _showOtherAccountBottomSheet(context, authProvider),
              ),
            ]),
            const SizedBox(height: AppSpacing.lg),

            // 8. SUPPORT & FEEDBACK (Help Center, Messages, Tickets, Feedback)
            _buildSectionHeader('Support & Communications'),
            _buildMenuCard(context, [
              _MenuItem(
                icon: Icons.storefront_outlined,
                title: 'Seller Center',
                subtitle: 'Drop-off hubs, inspection points & Google Map locations',
                iconColor: AppColors.brand500,
                destination: const SellerCenterScreen(),
              ),
              _MenuItem(
                icon: Icons.help_center_outlined,
                title: 'Help Center',
                subtitle: 'Guides, FAQs, seller rules & resolution center',
                iconColor: AppColors.brand500,
                destination: const HelpCenterScreen(),
              ),
              _MenuItem(
                icon: Icons.chat_bubble_outline_rounded,
                title: 'Messages',
                subtitle: 'Direct customer messages & chat with support',
                iconColor: AppColors.brand500,
                destination: const MessagesScreen(),
              ),
              _MenuItem(
                icon: Icons.support_agent_rounded,
                title: 'Support Tickets',
                subtitle: 'Customer care tickets & inquiry thread',
                iconColor: AppColors.brand500,
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
                iconColor: AppColors.brand500,
                destination: const AboutScreen(),
              ),
              _MenuItem(
                icon: Icons.help_outline_rounded,
                title: 'Seller Help & Guidelines',
                subtitle: 'Standards, fulfillment SLAs & FAQ answers',
                iconColor: AppColors.brand500,
                destination: const SellerHelpScreen(),
              ),
              _MenuItem(
                icon: Icons.description_outlined,
                title: 'Vendor Terms of Service',
                subtitle: 'Platform contractual merchant terms',
                iconColor: AppColors.brand500,
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
            const SizedBox(height: AppSpacing.xxl),
            _buildFooter(),
            const SizedBox(height: AppSpacing.xxxl),
          ],
        );
  }

  Widget _buildFooter() {
    return Column(
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 22,
              height: 22,
              decoration: BoxDecoration(
                color: AppColors.brand500,
                borderRadius: BorderRadius.circular(6),
              ),
              child: const Icon(Icons.storefront_rounded, size: 13, color: Colors.white),
            ),
            const SizedBox(width: 6),
            const Text(
              'Openbox Seller for Merchants',
              style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.slate400),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildQuickActionTile({
    required IconData icon,
    required Color iconColor,
    required String label,
    required String caption,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(AppRadius.md),
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 4, horizontal: 4),
        child: Column(
          children: [
            Container(
              width: 38,
              height: 38,
              decoration: BoxDecoration(
                color: iconColor.withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, size: 20, color: iconColor),
            ),
            const SizedBox(height: 6),
            Text(
              label,
              style: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w700,
                color: AppColors.ink,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              caption,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(
                fontSize: 10,
                color: AppColors.slate500,
                fontWeight: FontWeight.w500,
              ),
            ),
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
          border: Border.all(color: AppColors.slate200.withValues(alpha: 0.8)),
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
                      color: (item.iconColor ?? AppColors.brand500).withValues(alpha: 0.08),
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
                            color: (item.badgeColor ?? AppColors.brand500).withValues(alpha: 0.12),
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
