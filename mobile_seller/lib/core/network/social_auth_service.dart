import 'package:flutter/material.dart';
import 'package:google_sign_in/google_sign_in.dart';
import 'package:provider/provider.dart';

import '../../providers/seller_auth_provider.dart';
import '../theme/app_colors.dart';
import '../theme/app_spacing.dart';
import '../widgets/app_snackbar.dart';
import '../../screens/navigation/main_navigation_screen.dart';

class SocialAuthService {
  SocialAuthService._();

  /// Google Web OAuth Client ID used by the backend to verify the returned ID token.
  static const String serverClientId =
      '6007104583-m3eldhp5nlf3qf82s3rq996rrgqi8abr.apps.googleusercontent.com';

  static final GoogleSignIn _googleSignIn = GoogleSignIn(
    scopes: const [
      'email',
      'profile',
    ],
    serverClientId: serverClientId,
  );

  /// Handle real native Google Sign-In flow
  static Future<void> signInWithGoogle(
    BuildContext context, {
    String? role,
    String? storeName,
    bool isSignUp = false,
  }) async {
    try {
      // Disconnect/sign-out existing session to prompt account chooser
      try {
        await _googleSignIn.signOut();
      } catch (_) {
        // Safe to ignore if not signed in
      }

      final GoogleSignInAccount? account = await _googleSignIn.signIn();

      // If user dismissed the Google prompt, abort silently
      if (account == null || !context.mounted) {
        return;
      }

      final GoogleSignInAuthentication auth = await account.authentication;
      if (!context.mounted) return;

      final authProvider = context.read<SellerAuthProvider>();

      final tokenToUse = (auth.idToken != null && auth.idToken!.isNotEmpty)
          ? auth.idToken
          : auth.accessToken;

      final success = await authProvider.socialLogin(
        provider: 'google',
        providerId: account.id,
        email: account.email,
        name: account.displayName ?? 'Google User',
        avatar: account.photoUrl,
        accessToken: tokenToUse,
        role: role ?? 'business',
        storeName: storeName ?? (account.displayName ?? 'My Store'),
      );

      if (!context.mounted) return;

      if (success) {
        final name = authProvider.user?.name ?? account.displayName ?? 'Seller';
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
    } catch (e) {
      debugPrint('Google Sign-In Error: $e');
      if (!context.mounted) return;

      String errorMessage = 'Google sign-in could not be completed. Please try again.';
      final errString = e.toString();
      if (errString.contains('network_error') || errString.contains('SocketException')) {
        errorMessage = 'Network error connecting to Google services.';
      } else if (errString.contains('10') || errString.contains('DEVELOPER_ERROR')) {
        errorMessage = 'Google OAuth setup mismatch (check SHA-1 fingerprint or Client ID).';
      }

      AppSnackBar.error(context, errorMessage, title: 'Google Sign-In');
    }
  }

  /// Sign out from Google session
  static Future<void> signOut() async {
    try {
      await _googleSignIn.signOut();
    } catch (_) {}
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

  /// Interactive account confirmation dialog (used for Facebook or fallback)
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
