import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../providers/seller_auth_provider.dart';
import '../theme/app_colors.dart';
import '../theme/app_spacing.dart';
import '../widgets/app_snackbar.dart';
import '../../screens/navigation/main_navigation_screen.dart';

class SocialAuthService {
  SocialAuthService._();

  /// Handle Google Sign-In flow
  static Future<void> signInWithGoogle(
    BuildContext context, {
    String? role,
    String? storeName,
    bool isSignUp = false,
  }) async {
    final account = await _showSocialAccountPicker(
      context,
      provider: 'Google',
      defaultEmail: 'seller.google@example.com',
      defaultName: 'Google Merchant',
    );

    if (account == null || !context.mounted) return;

    final authProvider = context.read<SellerAuthProvider>();

    final success = await authProvider.socialLogin(
      provider: 'google',
      providerId: 'google_${DateTime.now().millisecondsSinceEpoch}',
      email: account['email'],
      name: account['name'],
      avatar: 'https://lh3.googleusercontent.com/a/default-user=s96-c',
      role: role ?? 'business',
      storeName: storeName ?? account['name'],
    );

    if (!context.mounted) return;

    if (success) {
      final name = authProvider.user?.name ?? 'Seller';
      AppSnackBar.success(
        context,
        'Signed in with Google as $name',
        title: 'Welcome',
      );

      Navigator.of(context).pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const MainNavigationScreen()),
        (route) => false,
      );
    } else {
      final error = authProvider.errorMessage ?? 'Google authentication failed.';
      AppSnackBar.error(context, error, title: 'Sign-in Failed');
    }
  }

  /// Handle Facebook Sign-In flow
  static Future<void> signInWithFacebook(
    BuildContext context, {
    String? role,
    String? storeName,
    bool isSignUp = false,
  }) async {
    final account = await _showSocialAccountPicker(
      context,
      provider: 'Facebook',
      defaultEmail: 'seller.facebook@example.com',
      defaultName: 'Facebook Merchant',
    );

    if (account == null || !context.mounted) return;

    final authProvider = context.read<SellerAuthProvider>();

    final success = await authProvider.socialLogin(
      provider: 'facebook',
      providerId: 'fb_${DateTime.now().millisecondsSinceEpoch}',
      email: account['email'],
      name: account['name'],
      avatar: 'https://graph.facebook.com/v12.0/default/picture',
      role: role ?? 'business',
      storeName: storeName ?? account['name'],
    );

    if (!context.mounted) return;

    if (success) {
      final name = authProvider.user?.name ?? 'Seller';
      AppSnackBar.success(
        context,
        'Signed in with Facebook as $name',
        title: 'Welcome',
      );

      Navigator.of(context).pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const MainNavigationScreen()),
        (route) => false,
      );
    } else {
      final error = authProvider.errorMessage ?? 'Facebook authentication failed.';
      AppSnackBar.error(context, error, title: 'Sign-in Failed');
    }
  }

  /// Interactive account confirmation dialog
  static Future<Map<String, String>?> _showSocialAccountPicker(
    BuildContext context, {
    required String provider,
    required String defaultEmail,
    required String defaultName,
  }) async {
    final emailController = TextEditingController(text: defaultEmail);
    final nameController = TextEditingController(text: defaultName);

    return showModalBottomSheet<Map<String, String>>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return Container(
          decoration: const BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.vertical(top: Radius.circular(AppRadius.xl)),
          ),
          padding: EdgeInsets.only(
            left: AppSpacing.xxl,
            right: AppSpacing.xxl,
            top: AppSpacing.xl,
            bottom: MediaQuery.of(ctx).viewInsets.bottom + AppSpacing.xxl,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
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
              const SizedBox(height: AppSpacing.lg),
              Row(
                children: [
                  Container(
                    width: 36,
                    height: 36,
                    decoration: BoxDecoration(
                      color: provider == 'Google' ? AppColors.brand50 : const Color(0xFFE7F3FF),
                      shape: BoxShape.circle,
                    ),
                    alignment: Alignment.center,
                    child: Text(
                      provider[0],
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w900,
                        color: provider == 'Google' ? AppColors.brand600 : const Color(0xFF1877F2),
                      ),
                    ),
                  ),
                  const SizedBox(width: AppSpacing.md),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Continue with $provider',
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w800,
                            color: AppColors.ink,
                          ),
                        ),
                        const SizedBox(height: 2),
                        const Text(
                          'Openbox Seller & Store Platform',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w500,
                            color: AppColors.slate500,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: AppSpacing.xl),
              TextField(
                controller: nameController,
                decoration: InputDecoration(
                  labelText: 'Profile name',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
                  prefixIcon: const Icon(Icons.person_outline, size: 20),
                ),
              ),
              const SizedBox(height: AppSpacing.md),
              TextField(
                controller: emailController,
                keyboardType: TextInputType.emailAddress,
                decoration: InputDecoration(
                  labelText: '$provider account email',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
                  prefixIcon: const Icon(Icons.email_outlined, size: 20),
                ),
              ),
              const SizedBox(height: AppSpacing.xl),
              ElevatedButton(
                onPressed: () {
                  final email = emailController.text.trim();
                  final name = nameController.text.trim();
                  if (email.isEmpty) return;
                  Navigator.of(ctx).pop({
                    'email': email,
                    'name': name.isEmpty ? 'User' : name,
                  });
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: provider == 'Google' ? AppColors.brand500 : const Color(0xFF1877F2),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
                ),
                child: Text(
                  'Authorize & Sign In with $provider',
                  style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14),
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}
