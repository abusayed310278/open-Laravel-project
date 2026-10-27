import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';

import '../../core/network/api_client.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../data/models/seller_user.dart';
import '../../providers/seller_auth_provider.dart';
import '../auth/login_screen.dart';
import '../settings/subscreens/store_settings_screen.dart';
import 'seller_help_screen.dart';
import 'vendor_terms_screen.dart';

class StoreScreen extends StatefulWidget {
  const StoreScreen({super.key});

  @override
  State<StoreScreen> createState() => _StoreScreenState();
}

class _StoreScreenState extends State<StoreScreen> {
  final ApiClient _apiClient = ApiClient();
  final ImagePicker _picker = ImagePicker();

  bool _isUploadingBanner = false;
  bool _isUploadingLogo = false;

  Future<void> _pickAndUploadBanner() async {
    try {
      final picked = await _picker.pickImage(
        source: ImageSource.gallery,
        maxWidth: 1920,
        maxHeight: 1080,
        imageQuality: 85,
      );
      if (picked == null) return;

      setState(() => _isUploadingBanner = true);

      final file = await http.MultipartFile.fromPath('cover_image', picked.path);
      final response = await _apiClient.multipart(
        '/profile',
        files: [file],
      );

      if (!mounted) return;

      final dynamic profileMap = (response is Map<String, dynamic>)
          ? (response['profile'] ?? response['user'])
          : null;
      if (profileMap is Map<String, dynamic> && mounted) {
        final freshUser = SellerUser.fromJson(profileMap);
        await context.read<SellerAuthProvider>().setUser(freshUser);
      }
      if (mounted) {
        await context.read<SellerAuthProvider>().refreshProfile();
      }

      if (mounted) {
        AppSnackBar.success(context, 'Store banner updated successfully!');
      }
    } catch (e) {
      if (mounted) {
        AppSnackBar.error(context, 'Failed to update banner: $e');
      }
    } finally {
      if (mounted) setState(() => _isUploadingBanner = false);
    }
  }

  Future<void> _pickAndUploadLogo() async {
    try {
      final picked = await _picker.pickImage(
        source: ImageSource.gallery,
        maxWidth: 1024,
        maxHeight: 1024,
        imageQuality: 85,
      );
      if (picked == null) return;

      setState(() => _isUploadingLogo = true);

      final file = await http.MultipartFile.fromPath('logo', picked.path);
      final response = await _apiClient.multipart(
        '/profile',
        files: [file],
      );

      if (!mounted) return;

      final dynamic profileMap = (response is Map<String, dynamic>)
          ? (response['profile'] ?? response['user'])
          : null;
      if (profileMap is Map<String, dynamic> && mounted) {
        final freshUser = SellerUser.fromJson(profileMap);
        await context.read<SellerAuthProvider>().setUser(freshUser);
      }
      if (mounted) {
        await context.read<SellerAuthProvider>().refreshProfile();
      }

      if (mounted) {
        AppSnackBar.success(context, 'Store logo updated successfully!');
      }
    } catch (e) {
      if (mounted) {
        AppSnackBar.error(context, 'Failed to update logo: $e');
      }
    } finally {
      if (mounted) setState(() => _isUploadingLogo = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<SellerAuthProvider>();
    final user = authProvider.user;

    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded),
          onPressed: () => Navigator.of(context).maybePop(),
        ),
        title: const Text('Store Profile & Settings'),
        actions: [
          IconButton(
            icon: const Icon(Icons.edit_outlined),
            tooltip: 'Store Settings',
            onPressed: () async {
              await Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const StoreSettingsScreen()),
              );
              if (context.mounted) {
                await context.read<SellerAuthProvider>().refreshProfile();
              }
            },
          ),
        ],
      ),
      body: RefreshIndicator(
        color: AppColors.brand500,
        onRefresh: () => context.read<SellerAuthProvider>().refreshProfile(),
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: Column(
            children: [
              // 1. Cover Banner & Logo Stack
              Stack(
                clipBehavior: Clip.none,
                alignment: Alignment.bottomLeft,
                children: [
                  Container(
                    height: 130,
                    width: double.infinity,
                    decoration: const BoxDecoration(
                      color: AppColors.slate800,
                    ),
                    child: Stack(
                      fit: StackFit.expand,
                      children: [
                        if (user?.coverUrl != null && user!.coverUrl!.isNotEmpty)
                          Image.network(
                            user.coverUrl!,
                            fit: BoxFit.cover,
                            errorBuilder: (_, _, _) => Container(color: AppColors.slate800),
                          )
                        else
                          Container(color: AppColors.slate800),
                        if (_isUploadingBanner)
                          Container(
                            color: Colors.black.withValues(alpha: 0.5),
                            alignment: Alignment.center,
                            child: const Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                SizedBox(
                                  width: 24,
                                  height: 24,
                                  child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white),
                                ),
                                SizedBox(height: 6),
                                Text(
                                  'Saving banner...',
                                  style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600),
                                ),
                              ],
                            ),
                          ),
                      ],
                    ),
                  ),

                  // Edit Banner Button overlay
                  Positioned(
                    top: 10,
                    right: 12,
                    child: Material(
                      color: Colors.transparent,
                      child: InkWell(
                        onTap: _isUploadingBanner ? null : _pickAndUploadBanner,
                        borderRadius: BorderRadius.circular(AppRadius.pill),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: Colors.black.withValues(alpha: 0.65),
                            borderRadius: BorderRadius.circular(AppRadius.pill),
                            border: Border.all(color: Colors.white.withValues(alpha: 0.3)),
                          ),
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.camera_alt_outlined, size: 14, color: Colors.white),
                              SizedBox(width: 4),
                              Text(
                                'Change Banner',
                                style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),

                  // Store Logo Container
                  Positioned(
                    bottom: -32,
                    left: 20,
                    child: Stack(
                      clipBehavior: Clip.none,
                      children: [
                        Container(
                          width: 80,
                          height: 80,
                          decoration: BoxDecoration(
                            color: AppColors.surface,
                            borderRadius: BorderRadius.circular(AppRadius.lg),
                            border: Border.all(color: AppColors.surface, width: 3),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withValues(alpha: 0.08),
                                blurRadius: 8,
                                offset: const Offset(0, 2),
                              ),
                            ],
                          ),
                          clipBehavior: Clip.antiAlias,
                          child: Stack(
                            fit: StackFit.expand,
                            children: [
                              if (user?.logoUrl != null && user!.logoUrl!.isNotEmpty)
                                Image.network(
                                  user.logoUrl!,
                                  fit: BoxFit.cover,
                                  errorBuilder: (_, _, _) => const Icon(Icons.storefront, size: 36, color: AppColors.brand500),
                                )
                              else
                                const Icon(Icons.storefront, size: 36, color: AppColors.brand500),
                              if (_isUploadingLogo)
                                Container(
                                  color: Colors.black.withValues(alpha: 0.5),
                                  alignment: Alignment.center,
                                  child: const SizedBox(
                                    width: 20,
                                    height: 20,
                                    child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                                  ),
                                ),
                            ],
                          ),
                        ),
                        Positioned(
                          bottom: -2,
                          right: -2,
                          child: Material(
                            color: Colors.transparent,
                            child: InkWell(
                              onTap: _isUploadingLogo ? null : _pickAndUploadLogo,
                              borderRadius: BorderRadius.circular(20),
                              child: Container(
                                padding: const EdgeInsets.all(6),
                                decoration: BoxDecoration(
                                  color: AppColors.brand500,
                                  shape: BoxShape.circle,
                                  border: Border.all(color: Colors.white, width: 2),
                                  boxShadow: [
                                    BoxShadow(
                                      color: Colors.black.withValues(alpha: 0.15),
                                      blurRadius: 4,
                                      offset: const Offset(0, 1),
                                    ),
                                  ],
                                ),
                                child: const Icon(Icons.camera_alt, size: 13, color: Colors.white),
                              ),
                            ),
                          ),
                        ),
                      ],
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
                              color: user?.isKycApproved == true ? AppColors.success.withValues(alpha: 0.4) : AppColors.brand200,
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
                    border: Border.all(color: AppColors.slate200.withValues(alpha: 0.8)),
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
                    border: Border.all(color: AppColors.slate200.withValues(alpha: 0.8)),
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
                    border: Border.all(color: AppColors.slate200.withValues(alpha: 0.8)),
                  ),
                  child: Column(
                    children: [
                      ListTile(
                        leading: const Icon(Icons.store_outlined, color: AppColors.brand600),
                        title: const Text('Store Settings & Details', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                        trailing: const Icon(Icons.chevron_right, size: 20, color: AppColors.slate400),
                        onTap: () async {
                          await Navigator.of(context).push(
                            MaterialPageRoute(builder: (_) => const StoreSettingsScreen()),
                          );
                          if (context.mounted) {
                            await context.read<SellerAuthProvider>().refreshProfile();
                          }
                        },
                      ),
                      const Divider(height: 1, color: AppColors.slate100),
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
