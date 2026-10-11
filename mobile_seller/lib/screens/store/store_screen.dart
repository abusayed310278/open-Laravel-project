import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../providers/seller_auth_provider.dart';
import '../auth/login_screen.dart';
import 'seller_help_screen.dart';
import 'vendor_terms_screen.dart';

class StoreScreen extends StatelessWidget {
  const StoreScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<SellerAuthProvider>();
    final user = authProvider.user;

    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        title: const Text('Store Profile & Settings'),
      ),
      body: SingleChildScrollView(
        child: Column(
          children: [
            // 1. Cover Banner & Logo Stack
            Stack(
              clipBehavior: Clip.none,
              alignment: Alignment.bottomLeft,
              children: [
                Container(
                  height: 120,
                  width: double.infinity,
                  decoration: const BoxDecoration(
                    color: AppColors.slate800,
                  ),
                  child: user?.coverUrl != null
                      ? Image.network(
                          user!.coverUrl!,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => Container(color: AppColors.slate800),
                        )
                      : Container(color: AppColors.slate800),
                ),
                Positioned(
                  bottom: -32,
                  left: 20,
                  child: Container(
                    width: 76,
                    height: 76,
                    decoration: BoxDecoration(
                      color: AppColors.surface,
                      borderRadius: BorderRadius.circular(AppRadius.lg),
                      border: Border.all(color: AppColors.surface, width: 3),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.08),
                          blurRadius: 8,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    clipBehavior: Clip.antiAlias,
                    child: user?.logoUrl != null
                        ? Image.network(
                            user!.logoUrl!,
                            fit: BoxFit.cover,
                            errorBuilder: (_, __, ___) => const Icon(Icons.storefront, size: 36, color: AppColors.brand500),
                          )
                        : const Icon(Icons.storefront, size: 36, color: AppColors.brand500),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 44),

            // 2. Store Header Details
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Text(
                          user?.storeName ?? 'My Store',
                          style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: AppColors.ink),
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: user?.isKycApproved == true ? AppColors.successBg : AppColors.brand50,
                          borderRadius: BorderRadius.circular(AppRadius.pill),
                          border: Border.all(
                            color: user?.isKycApproved == true ? AppColors.success.withOpacity(0.4) : AppColors.brand200,
                          ),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              user?.isKycApproved == true ? Icons.verified_rounded : Icons.pending_outlined,
                              size: 14,
                              color: user?.isKycApproved == true ? AppColors.success : AppColors.brand600,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              user?.isKycApproved == true ? 'Verified Seller' : 'Under Review',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w700,
                                color: user?.isKycApproved == true ? AppColors.success : AppColors.brand700,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text(
                    user?.email ?? '',
                    style: const TextStyle(fontSize: 13, color: AppColors.slate500),
                  ),
                  if (user?.description != null && user!.description!.isNotEmpty) ...[
                    const SizedBox(height: 8),
                    Text(
                      user.description!,
                      style: const TextStyle(fontSize: 13, color: AppColors.slate600, height: 1.4),
                    ),
                  ],
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.xl),

            // 3. Subscription & Credits Card
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
              child: Container(
                padding: const EdgeInsets.all(AppSpacing.lg),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(AppRadius.lg),
                  border: Border.all(color: AppColors.slate200.withOpacity(0.8)),
                ),
                child: Row(
                  children: [
                    Container(
                      width: 44,
                      height: 44,
                      decoration: BoxDecoration(
                        color: AppColors.brand50,
                        borderRadius: BorderRadius.circular(AppRadius.md),
                      ),
                      child: const Icon(Icons.bolt_rounded, color: AppColors.brand600, size: 24),
                    ),
                    const SizedBox(width: AppSpacing.md),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            user?.subscriptionPlan ?? 'Active Plan',
                            style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.ink),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            '${user?.listingCredits ?? 0} Listing credits remaining',
                            style: const TextStyle(fontSize: 12, color: AppColors.slate500),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: AppSpacing.lg),

            // 4. Contact & Address Card
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
              child: Container(
                padding: const EdgeInsets.all(AppSpacing.lg),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(AppRadius.lg),
                  border: Border.all(color: AppColors.slate200.withOpacity(0.8)),
                ),
                child: Column(
                  children: [
                    _buildSettingsTile(
                      icon: Icons.phone_outlined,
                      title: 'Phone Number',
                      subtitle: user?.phone ?? 'Not set',
                    ),
                    const Divider(height: 20, color: AppColors.slate100),
                    _buildSettingsTile(
                      icon: Icons.location_on_outlined,
                      title: 'Store Location',
                      subtitle: [user?.address, user?.city, user?.country].where((e) => e != null && e.isNotEmpty).join(', ').isEmpty
                          ? 'Not provided'
                          : [user?.address, user?.city, user?.country].where((e) => e != null && e.isNotEmpty).join(', '),
                    ),
                    const Divider(height: 20, color: AppColors.slate100),
                    _buildSettingsTile(
                      icon: Icons.badge_outlined,
                      title: 'Account Role',
                      subtitle: user?.roleLabel ?? 'Seller',
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: AppSpacing.lg),

            // 5. Account Actions Card
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
              child: Container(
                padding: const EdgeInsets.all(AppSpacing.md),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(AppRadius.lg),
                  border: Border.all(color: AppColors.slate200.withOpacity(0.8)),
                ),
                child: Column(
                  children: [
                    ListTile(
                      leading: const Icon(Icons.help_outline_rounded, color: AppColors.slate500),
                      title: const Text('Seller Help & Guidelines', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                      trailing: const Icon(Icons.chevron_right, size: 20, color: AppColors.slate400),
                      onTap: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(builder: (_) => const SellerHelpScreen()),
                        );
                      },
                    ),
                    const Divider(height: 1, color: AppColors.slate100),
                    ListTile(
                      leading: const Icon(Icons.description_outlined, color: AppColors.slate500),
                      title: const Text('Vendor Terms of Service', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                      trailing: const Icon(Icons.chevron_right, size: 20, color: AppColors.slate400),
                      onTap: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(builder: (_) => const VendorTermsScreen()),
                        );
                      },
                    ),
                    const Divider(height: 1, color: AppColors.slate100),
                    ListTile(
                      leading: const Icon(Icons.logout_rounded, color: AppColors.danger),
                      title: const Text('Log Out', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.danger)),
                      onTap: () => _confirmLogout(context, authProvider),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: AppSpacing.xxxl),
          ],
        ),
      ),
    );
  }

  Widget _buildSettingsTile({required IconData icon, required String title, required String subtitle}) {
    return Row(
      children: [
        Icon(icon, size: 20, color: AppColors.slate400),
        const SizedBox(width: AppSpacing.md),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title, style: const TextStyle(fontSize: 12, color: AppColors.slate400, fontWeight: FontWeight.w500)),
              const SizedBox(height: 2),
              Text(subtitle, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: AppColors.ink)),
            ],
          ),
        ),
      ],
    );
  }

  void _confirmLogout(BuildContext context, SellerAuthProvider authProvider) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Log Out'),
        content: const Text('Are you sure you want to log out of the seller portal?'),
        actions: [
          TextButton(onPressed: () => Navigator.of(ctx).pop(), child: const Text('Cancel')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.danger),
            onPressed: () async {
              Navigator.of(ctx).pop();
              await authProvider.logout();
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
}
