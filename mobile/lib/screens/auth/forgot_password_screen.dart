import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_logo.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../providers/auth_provider.dart';
import 'verify_otp_screen.dart';

class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final _emailController = TextEditingController();

  @override
  void dispose() {
    _emailController.dispose();
    super.dispose();
  }

  Future<void> _sendResetOtp() async {
    final email = _emailController.text.trim();
    if (email.isEmpty) {
      AppSnackbar.showError(context, 'Please enter your email address');
      return;
    }

    final authProvider = context.read<AuthProvider>();
    final result = await authProvider.forgotPassword(email);

    if (!mounted) return;

    if (result['success'] == true) {
      final msg = result['message']?.toString() ?? 'OTP sent to your email';
      AppSnackbar.showSuccess(context, msg);

      Navigator.of(context).pushReplacement(
        MaterialPageRoute(
          builder: (_) => VerifyOtpScreen(
            email: email,
            isPasswordReset: true,
          ),
        ),
      );
    } else {
      AppSnackbar.showError(
        context,
        result['message']?.toString() ?? 'Failed to request password reset',
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<AuthProvider>();
    final isLoading = authProvider.state == AuthState.loading;

    return Scaffold(
      appBar: AppBar(title: const Text('Forgot Password')),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppSpacing.xxl),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const AppLogo(size: 56, borderRadius: 16),
              const SizedBox(height: AppSpacing.xl),
              const Text(
                'Forgot Password?',
                style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: AppColors.ink),
              ),
              const SizedBox(height: AppSpacing.xs),
              const Text(
                'Don\'t worry! Enter your email address below to receive password reset instructions & OTP.',
                style: TextStyle(fontSize: 13, color: AppColors.slate500, height: 1.4),
              ),
              const SizedBox(height: AppSpacing.xxl),
              const Text(
                'Email Address',
                style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.slate700),
              ),
              const SizedBox(height: AppSpacing.xs),
              TextField(
                controller: _emailController,
                keyboardType: TextInputType.emailAddress,
                decoration: const InputDecoration(
                  hintText: 'you@example.com',
                  prefixIcon: Icon(Icons.mail_outline_rounded),
                ),
              ),
              const SizedBox(height: AppSpacing.xxl),
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton(
                  onPressed: isLoading ? null : _sendResetOtp,
                  child: isLoading
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                        )
                      : const Text('Send Reset OTP'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
