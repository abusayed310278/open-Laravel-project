import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/custom_text_field.dart';
import '../../providers/seller_auth_provider.dart';
import 'login_screen.dart';

class ResetPasswordScreen extends StatefulWidget {
  final String email;
  final String? initialOtp;

  const ResetPasswordScreen({
    super.key,
    required this.email,
    this.initialOtp,
  });

  @override
  State<ResetPasswordScreen> createState() => _ResetPasswordScreenState();
}

class _ResetPasswordScreenState extends State<ResetPasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _otpController;
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  bool _obscurePassword = true;
  bool _obscureConfirmPassword = true;

  int _resendCooldown = 60;
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _otpController = TextEditingController(text: widget.initialOtp ?? '');
    _startCooldownTimer();
  }

  @override
  void dispose() {
    _timer?.cancel();
    _otpController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  void _startCooldownTimer() {
    _timer?.cancel();
    setState(() => _resendCooldown = 60);
    _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (!mounted) {
        timer.cancel();
        return;
      }
      if (_resendCooldown > 0) {
        setState(() => _resendCooldown--);
      } else {
        timer.cancel();
      }
    });
  }

  Future<void> _handleResendOtp() async {
    if (_resendCooldown > 0) return;

    final authProvider = context.read<SellerAuthProvider>();
    final result = await authProvider.forgotPassword(widget.email);

    if (!mounted) return;

    if (result != null) {
      final String? devOtp = result['otp']?.toString();
      if (devOtp != null && devOtp.isNotEmpty) {
        _otpController.text = devOtp;
      }
      AppSnackBar.success(context, 'A new verification code was sent to ${widget.email}.');
      _startCooldownTimer();
    } else {
      final error = authProvider.errorMessage ?? 'Failed to resend verification code.';
      AppSnackBar.error(context, error);
    }
  }

  Future<void> _handleResetPassword() async {
    if (!_formKey.currentState!.validate()) return;

    final authProvider = context.read<SellerAuthProvider>();
    final otp = _otpController.text.trim();
    final password = _passwordController.text;
    final passwordConfirmation = _confirmPasswordController.text;

    final success = await authProvider.resetPassword(
      email: widget.email,
      otp: otp,
      password: password,
      passwordConfirmation: passwordConfirmation,
    );

    if (!mounted) return;

    if (success) {
      AppSnackBar.success(
        context,
        'Password reset successfully! Please sign in with your new password.',
        title: 'Success',
      );

      Navigator.of(context).pushAndRemoveUntil(
        MaterialPageRoute(
          builder: (_) => LoginScreen(prefilledEmail: widget.email),
        ),
        (route) => false,
      );
    } else {
      final error = authProvider.errorMessage ?? 'Password reset failed. Please check the code and try again.';
      AppSnackBar.error(context, error, title: 'Reset Failed');
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<SellerAuthProvider>();
    final isLoading = authProvider.state == AuthState.loading;

    return Scaffold(
      backgroundColor: AppColors.slate50,
      resizeToAvoidBottomInset: true,
      appBar: AppBar(
        backgroundColor: AppColors.slate50,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, size: 20, color: AppColors.ink),
          onPressed: () => Navigator.of(context).pop(),
        ),
        title: const Text(
          'Create New Password',
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w700,
            color: AppColors.ink,
          ),
        ),
      ),
      body: GestureDetector(
        behavior: HitTestBehavior.opaque,
        onTap: () => FocusScope.of(context).unfocus(),
        child: SafeArea(
          child: SingleChildScrollView(
            keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.onDrag,
            padding: const EdgeInsets.symmetric(horizontal: AppSpacing.xxl, vertical: AppSpacing.lg),
            child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const SizedBox(height: AppSpacing.md),
                Center(
                  child: Container(
                    width: 72,
                    height: 72,
                    decoration: BoxDecoration(
                      color: AppColors.successBg,
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const Icon(
                      Icons.verified_user_outlined,
                      size: 38,
                      color: AppColors.success,
                    ),
                  ),
                ),
                const SizedBox(height: AppSpacing.xxl),
                const Text(
                  'Set new password',
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.w800,
                    color: AppColors.ink,
                    letterSpacing: -0.5,
                  ),
                ),
                const SizedBox(height: AppSpacing.xs),
                Text.rich(
                  TextSpan(
                    style: const TextStyle(fontSize: 13, height: 1.4, color: AppColors.slate500),
                    children: [
                      const TextSpan(text: 'We sent a 6-digit OTP code to '),
                      TextSpan(
                        text: widget.email,
                        style: const TextStyle(fontWeight: FontWeight.w700, color: AppColors.ink),
                      ),
                      const TextSpan(text: '. Enter the code and your new password below.'),
                    ],
                  ),
                ),
                const SizedBox(height: AppSpacing.xxl),
                Container(
                  padding: const EdgeInsets.all(AppSpacing.xl),
                  decoration: BoxDecoration(
                    color: AppColors.surface,
                    borderRadius: BorderRadius.circular(AppRadius.lg),
                    border: Border.all(color: AppColors.slate200.withValues(alpha: 0.8)),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.02),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      CustomTextField(
                        controller: _otpController,
                        label: '6-Digit OTP Code',
                        hint: '123456',
                        keyboardType: TextInputType.number,
                        prefixIcon: const Icon(Icons.pin_outlined, size: 20, color: AppColors.slate400),
                        validator: (val) {
                          if (val == null || val.trim().isEmpty) {
                            return 'Please enter the 6-digit verification code';
                          }
                          if (val.trim().length < 6) {
                            return 'Code must be 6 digits';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: AppSpacing.lg),
                      CustomTextField(
                        controller: _passwordController,
                        label: 'New Password',
                        hint: 'At least 8 characters',
                        obscureText: _obscurePassword,
                        prefixIcon: const Icon(Icons.lock_outline_rounded, size: 20, color: AppColors.slate400),
                        suffixIcon: IconButton(
                          icon: Icon(
                            _obscurePassword ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                            size: 20,
                            color: AppColors.slate400,
                          ),
                          onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                        ),
                        validator: (val) {
                          if (val == null || val.isEmpty) {
                            return 'Please enter your new password';
                          }
                          if (val.length < 8) {
                            return 'Password must be at least 8 characters';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: AppSpacing.lg),
                      CustomTextField(
                        controller: _confirmPasswordController,
                        label: 'Confirm New Password',
                        hint: 'Re-enter your new password',
                        obscureText: _obscureConfirmPassword,
                        prefixIcon: const Icon(Icons.lock_outline_rounded, size: 20, color: AppColors.slate400),
                        suffixIcon: IconButton(
                          icon: Icon(
                            _obscureConfirmPassword ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                            size: 20,
                            color: AppColors.slate400,
                          ),
                          onPressed: () => setState(() => _obscureConfirmPassword = !_obscureConfirmPassword),
                        ),
                        validator: (val) {
                          if (val != _passwordController.text) {
                            return 'Passwords do not match';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: AppSpacing.md),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            "Didn't receive code?",
                            style: TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.slate500),
                          ),
                          TextButton(
                            onPressed: _resendCooldown == 0 && !isLoading ? _handleResendOtp : null,
                            child: Text(
                              _resendCooldown > 0 ? 'Resend in ${_resendCooldown}s' : 'Resend Code',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w700,
                                color: _resendCooldown > 0 ? AppColors.slate400 : AppColors.brand600,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: AppSpacing.lg),
                      CustomButton(
                        text: 'Reset Password',
                        isLoading: isLoading,
                        onPressed: _handleResetPassword,
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
      ),
    );
  }
}
