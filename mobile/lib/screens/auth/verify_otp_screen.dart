import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_logo.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../providers/auth_provider.dart';
import 'login_screen.dart';
import 'reset_password_screen.dart';

class VerifyOtpScreen extends StatefulWidget {
  const VerifyOtpScreen({
    super.key,
    required this.email,
    this.initialOtp = '',
    this.isPasswordReset = false,
  });

  final String email;
  final String initialOtp;
  final bool isPasswordReset;

  @override
  State<VerifyOtpScreen> createState() => _VerifyOtpScreenState();
}

class _VerifyOtpScreenState extends State<VerifyOtpScreen> {
  late final TextEditingController _otpController;

  @override
  void initState() {
    super.initState();
    _otpController = TextEditingController();
  }

  @override
  void dispose() {
    _otpController.dispose();
    super.dispose();
  }

  Future<void> _submitVerification() async {
    final otp = _otpController.text.trim();
    if (otp.isEmpty) {
      AppSnackbar.showError(context, 'Please enter the 6-digit OTP code');
      return;
    }

    final authProvider = context.read<AuthProvider>();

    if (widget.isPasswordReset) {
      final success = await authProvider.verifyResetOtp(widget.email, otp);
      if (!mounted) return;

      if (success) {
        AppSnackbar.showSuccess(context, 'OTP verified! Set your new password.');
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(
            builder: (_) => ResetPasswordScreen(
              email: widget.email,
              token: otp,
            ),
          ),
        );
      } else {
        AppSnackbar.showError(
          context,
          authProvider.errorMessage ?? 'Invalid OTP code. Please check and try again.',
        );
      }
    } else {
      final success = await authProvider.verifyEmailOtp(widget.email, otp);
      if (!mounted) return;

      if (success) {
        AppSnackbar.showSuccess(context, 'Email verified successfully! Please sign in to your account.');
        Navigator.of(context).pushAndRemoveUntil(
          MaterialPageRoute(builder: (_) => const LoginScreen()),
          (route) => false,
        );
      } else {
        AppSnackbar.showError(
          context,
          authProvider.errorMessage ?? 'Invalid verification code. Please check and try again.',
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<AuthProvider>();
    final isLoading = authProvider.state == AuthState.loading;

    return Scaffold(
      appBar: AppBar(title: Text(widget.isPasswordReset ? 'Verify Reset Code' : 'Verify Email')),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppSpacing.xxl),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const AppLogo(size: 56, borderRadius: 16),
              const SizedBox(height: AppSpacing.xl),
              Text(
                widget.isPasswordReset ? 'Verify Reset OTP' : 'Verify Your Email',
                style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: AppColors.ink),
              ),
              const SizedBox(height: AppSpacing.xs),
              Text(
                'Enter the 6-digit OTP code sent to ${widget.email}.',
                style: const TextStyle(fontSize: 13, color: AppColors.slate500, height: 1.4),
              ),
              const SizedBox(height: AppSpacing.xxl),
              const Text(
                '6-Digit OTP Code',
                style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.slate700),
              ),
              const SizedBox(height: AppSpacing.xs),
              TextField(
                controller: _otpController,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(
                  hintText: 'e.g. 123456',
                  prefixIcon: Icon(Icons.mark_email_read_outlined),
                ),
              ),
              const SizedBox(height: AppSpacing.xxl),
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton(
                  onPressed: isLoading ? null : _submitVerification,
                  child: isLoading
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                        )
                      : Text(widget.isPasswordReset ? 'Verify Code & Continue' : 'Verify Email & Continue'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

