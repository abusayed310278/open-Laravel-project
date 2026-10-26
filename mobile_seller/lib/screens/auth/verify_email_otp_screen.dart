import 'dart:async';
import 'package:flutter/material.dart';

import '../../core/network/api_client.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/custom_text_field.dart';
import '../navigation/main_navigation_screen.dart';
import 'login_screen.dart';

/// Screen representing Step 2 of Onboarding: "Verify Your Email".
/// Matches the high-polish design with 6-digit OTP entry, resend cooldown,
/// and direct transition to the home screen upon verification.
class VerifyEmailOtpScreen extends StatefulWidget {
  final String email;

  const VerifyEmailOtpScreen({
    super.key,
    required this.email,
  });

  @override
  State<VerifyEmailOtpScreen> createState() => _VerifyEmailOtpScreenState();
}

class _VerifyEmailOtpScreenState extends State<VerifyEmailOtpScreen> {
  final _formKey = GlobalKey<FormState>();
  final _apiClient = ApiClient();
  late final TextEditingController _otpController;

  bool _isLoading = false;
  bool _isResending = false;
  int _resendCooldown = 60;
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _otpController = TextEditingController();
    _startCooldownTimer();
  }

  @override
  void dispose() {
    _timer?.cancel();
    _otpController.dispose();
    super.dispose();
  }

  void _startCooldownTimer() {
    _resendCooldown = 60;
    _timer?.cancel();
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

  Future<void> _handleVerify() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    try {
      await _apiClient.post(
        '/auth/verify-email',
        body: {
          'email': widget.email,
          'otp': _otpController.text.trim(),
        },
      );

      if (!mounted) return;

      AppSnackBar.success(context, 'Email verified successfully! Welcome aboard.');

      // Onboarding complete, go straight to the home screen
      Navigator.of(context).pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const MainNavigationScreen()),
        (route) => false,
      );
    } catch (e) {
      if (!mounted) return;
      AppSnackBar.error(context, e.toString());
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _handleResendCode() async {
    if (_resendCooldown > 0 || _isResending) return;

    setState(() => _isResending = true);

    try {
      await _apiClient.post(
        '/auth/resend-email-otp',
        body: {'email': widget.email},
      );

      if (!mounted) return;

      AppSnackBar.success(context, 'Verification code resent to your email.');
      _startCooldownTimer();
    } catch (e) {
      if (!mounted) return;
      AppSnackBar.error(context, e.toString());
    } finally {
      if (mounted) setState(() => _isResending = false);
    }
  }

  void _navigateToLogin() {
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(
        builder: (_) => LoginScreen(prefilledEmail: widget.email),
      ),
      (route) => false,
    );
  }

  @override
  Widget build(BuildContext context) {
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
          'Email Verification',
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
                  // Green Shield Icon Badge matching screenshot
                  Center(
                    child: Container(
                      width: 64,
                      height: 64,
                      decoration: BoxDecoration(
                        color: const Color(0xFFE8F5E9),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: const Icon(
                        Icons.verified_user_outlined,
                        size: 34,
                        color: Color(0xFF2E7D32),
                      ),
                    ),
                  ),
                  const SizedBox(height: AppSpacing.xxl),
                  // Title
                  const Text(
                    'Verify your email',
                    style: TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.w700,
                      color: AppColors.ink,
                      letterSpacing: -0.5,
                    ),
                  ),
                  const SizedBox(height: AppSpacing.xs),
                  // Subtitle
                  Text.rich(
                    TextSpan(
                      style: const TextStyle(fontSize: 13, height: 1.4, color: AppColors.slate500),
                      children: [
                        const TextSpan(text: 'We sent a 6-digit OTP code to\n'),
                        TextSpan(
                          text: widget.email,
                          style: const TextStyle(fontWeight: FontWeight.w700, color: AppColors.ink),
                        ),
                        const TextSpan(text: '. Enter the code below to verify your email and sign in.'),
                      ],
                    ),
                  ),
                  const SizedBox(height: AppSpacing.xxl),

                  // Main Card
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
                          hint: 'Enter 6-digit code',
                          keyboardType: TextInputType.number,
                          prefixIcon: const Icon(Icons.pin_outlined, size: 20, color: AppColors.slate400),
                          validator: (val) {
                            if (val == null || val.trim().isEmpty) {
                              return 'Please enter the 6-digit OTP code';
                            }
                            if (val.trim().length != 6) {
                              return 'OTP must be exactly 6 digits';
                            }
                            return null;
                          },
                        ),
                        const SizedBox(height: AppSpacing.lg),

                        // Resend Countdown Row
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text(
                              "Didn't receive code?",
                              style: TextStyle(fontSize: 13, fontWeight: FontWeight.w500, color: AppColors.slate500),
                            ),
                            TextButton(
                              onPressed: _resendCooldown > 0 || _isResending ? null : _handleResendCode,
                              style: TextButton.styleFrom(
                                padding: EdgeInsets.zero,
                                minimumSize: Size.zero,
                                tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                              ),
                              child: Text(
                                _resendCooldown > 0 ? 'Resend in ${_resendCooldown}s' : 'Resend Code',
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w600,
                                  color: _resendCooldown > 0 ? AppColors.slate400 : const Color(0xFFF59E0B),
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: AppSpacing.xl),

                        // Verify & Proceed Button with amber color matching screenshot
                        CustomButton(
                          text: 'Verify & Sign In',
                          isLoading: _isLoading,
                          backgroundColor: const Color(0xFFF59E0B),
                          onPressed: _handleVerify,
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: AppSpacing.xl),

                  // Alternative: Verify via email link option
                  Center(
                    child: Column(
                      children: [
                        const Text(
                          'Already verified using the email link?',
                          style: TextStyle(fontSize: 13, fontWeight: FontWeight.w500, color: AppColors.slate500),
                        ),
                        const SizedBox(height: 4),
                        TextButton(
                          onPressed: _navigateToLogin,
                          child: const Text(
                            'Proceed to Sign in',
                            style: TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF000080),
                            ),
                          ),
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
