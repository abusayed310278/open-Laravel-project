import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/network/social_auth_service.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/custom_text_field.dart';
import '../../core/widgets/social_login_button.dart';
import '../../providers/seller_auth_provider.dart';
import '../store/vendor_terms_screen.dart';
import 'onboarding_address_screen.dart';

/// Registration screen matching the Openbox web version flow & design.
/// Features dual-tab account selection (Personal vs Store Owner/Business),
/// conditional business storefront fields, real-time password strength,
/// password confirmation matching, and social OAuth login.
class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();

  // Form controllers
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  bool _obscurePassword = true;
  bool _obscureConfirmPassword = true;
  bool _agreedToTerms = false;
  bool _isGoogleLoading = false;
  bool _isFacebookLoading = false;

  // Password strength calculation (0 to 4)
  int _passwordStrength = 0;
  bool _passwordsMatch = false;

  @override
  void initState() {
    super.initState();
    _passwordController.addListener(_onPasswordChanged);
    _confirmPasswordController.addListener(_onConfirmPasswordChanged);
  }

  @override
  void dispose() {
    _passwordController.removeListener(_onPasswordChanged);
    _confirmPasswordController.removeListener(_onConfirmPasswordChanged);
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  void _onPasswordChanged() {
    final pwd = _passwordController.text;
    int strength = 0;
    if (pwd.length >= 8) strength++;
    if (pwd.length >= 10) strength++;
    if (RegExp(r'[A-Z]').hasMatch(pwd) && RegExp(r'[a-z]').hasMatch(pwd)) strength++;
    if (RegExp(r'[0-9]').hasMatch(pwd) || RegExp(r'[!@#\$%^&*(),.?":{}|<>]').hasMatch(pwd)) strength++;

    setState(() {
      _passwordStrength = strength;
      _passwordsMatch = pwd.isNotEmpty && pwd == _confirmPasswordController.text;
    });
  }

  void _onConfirmPasswordChanged() {
    final pwd = _passwordController.text;
    final confirm = _confirmPasswordController.text;
    setState(() {
      _passwordsMatch = pwd.isNotEmpty && pwd == confirm;
    });
  }

  Color get _strengthColor {
    if (_passwordStrength <= 1) return AppColors.danger;
    if (_passwordStrength <= 2) return AppColors.warning;
    return AppColors.success;
  }

  String get _strengthLabel {
    if (_passwordController.text.isEmpty) return 'Min. 8 characters';
    if (_passwordStrength <= 1) return 'Weak password';
    if (_passwordStrength <= 2) return 'Medium strength';
    if (_passwordStrength <= 3) return 'Strong password';
    return 'Very strong password';
  }

  Future<void> _handleRegister() async {
    if (!_formKey.currentState!.validate()) return;

    if (!_agreedToTerms) {
      AppSnackBar.warning(context, 'Please agree to the Terms of Service and Privacy Policy');
      return;
    }

    final authProvider = context.read<SellerAuthProvider>();

    final success = await authProvider.register(
      name: _nameController.text.trim(),
      email: _emailController.text.trim(),
      phone: _phoneController.text.trim().isEmpty ? null : _phoneController.text.trim(),
      storeName: _nameController.text.trim(),
      role: 'customer',
      password: _passwordController.text,
      passwordConfirmation: _confirmPasswordController.text,
    );

    if (!mounted) return;

    if (success) {
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(
          builder: (_) => OnboardingAddressScreen(
            initialName: _nameController.text.trim(),
            initialPhone: _phoneController.text.trim().isEmpty ? null : _phoneController.text.trim(),
            userEmail: _emailController.text.trim(),
          ),
        ),
      );
    } else {
      final error = authProvider.errorMessage ?? 'Registration failed. Please try again.';
      AppSnackBar.error(context, error);
    }
  }

  Future<void> _handleGoogleSignIn() async {
    setState(() => _isGoogleLoading = true);
    await SocialAuthService.signInWithGoogle(context, role: 'customer', isSignUp: true);
    if (mounted) {
      setState(() => _isGoogleLoading = false);
    }
  }

  Future<void> _handleFacebookSignIn() async {
    setState(() => _isFacebookLoading = true);
    await SocialAuthService.signInWithFacebook(context, role: 'customer', isSignUp: true);
    if (mounted) {
      setState(() => _isFacebookLoading = false);
    }
  }

  void _openTerms() {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => const VendorTermsScreen(),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<SellerAuthProvider>();
    final isLoading = authProvider.state == AuthState.loading;

    // Primary navy color from Openbox web design (#000080)
    const brandNavy = Color(0xFF000080);

    return Scaffold(
      backgroundColor: AppColors.slate50,
      resizeToAvoidBottomInset: true,
      appBar: AppBar(
        backgroundColor: AppColors.slate50,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, size: 18, color: AppColors.ink),
          onPressed: () => Navigator.of(context).maybePop(),
        ),
        title: const Text(
          'Create your account',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.ink),
        ),
      ),
      body: GestureDetector(
        behavior: HitTestBehavior.opaque,
        onTap: () => FocusScope.of(context).unfocus(),
        child: SafeArea(
          child: SingleChildScrollView(
            keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.onDrag,
            padding: const EdgeInsets.symmetric(horizontal: AppSpacing.xxl, vertical: AppSpacing.md),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Heading matching web register.blade.php
                  const Text(
                    'Create your account',
                    style: TextStyle(
                      fontSize: 22,
                      fontWeight: FontWeight.w800,
                      color: AppColors.ink,
                      letterSpacing: -0.5,
                    ),
                  ),
                  const SizedBox(height: 4),

                  // Subtitle
                  const Text(
                    'Shop, buy, or sell products as an individual member.',
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w500,
                      color: AppColors.slate500,
                      height: 1.4,
                    ),
                  ),
                  const SizedBox(height: AppSpacing.lg),

                  // Account Type Informational Badge
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    decoration: BoxDecoration(
                      color: brandNavy.withValues(alpha: 0.04),
                      borderRadius: BorderRadius.circular(AppRadius.md),
                      border: Border.all(
                        color: brandNavy.withValues(alpha: 0.15),
                      ),
                    ),
                    child: const Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Icon(
                          Icons.person_outline_rounded,
                          size: 18,
                          color: brandNavy,
                        ),
                        SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            'Personal account: Buy products and sell individual pre-owned or new items on the marketplace.',
                            style: TextStyle(
                              fontSize: 12,
                              height: 1.4,
                              fontWeight: FontWeight.w500,
                              color: AppColors.slate700,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: AppSpacing.lg),

                  // Main Form Card
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
                        // Full Name
                        CustomTextField(
                          controller: _nameController,
                          label: 'Full name',
                          hint: 'Sarah Ahmed',
                          prefixIcon: const Icon(Icons.person_outline_rounded, size: 20, color: AppColors.slate400),
                          validator: (val) {
                            if (val == null || val.trim().isEmpty) {
                              return 'Please enter your full name';
                            }
                            return null;
                          },
                        ),
                        const SizedBox(height: AppSpacing.lg),

                        // Email
                        CustomTextField(
                          controller: _emailController,
                          label: 'Email',
                          hint: 'you@example.com',
                          keyboardType: TextInputType.emailAddress,
                          prefixIcon: const Icon(Icons.email_outlined, size: 20, color: AppColors.slate400),
                          validator: (val) {
                            if (val == null || val.trim().isEmpty) {
                              return 'Please enter your email';
                            }
                            if (!val.contains('@') || !val.contains('.')) {
                              return 'Please enter a valid email address';
                            }
                            return null;
                          },
                        ),
                        const SizedBox(height: AppSpacing.lg),

                        // Phone (optional)
                        CustomTextField(
                          controller: _phoneController,
                          label: 'Phone (optional)',
                          hint: '+974 5555 1234',
                          keyboardType: TextInputType.phone,
                          prefixIcon: const Icon(Icons.phone_outlined, size: 20, color: AppColors.slate400),
                        ),
                        const SizedBox(height: AppSpacing.lg),

                        // Password Field
                        CustomTextField(
                          controller: _passwordController,
                          label: 'Password',
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
                              return 'Please enter a password';
                            }
                            if (val.length < 8) {
                              return 'Password must be at least 8 characters';
                            }
                            return null;
                          },
                        ),

                        // Real-time Password Strength Meter matching web
                        if (_passwordController.text.isNotEmpty) ...[
                          const SizedBox(height: 8),
                          Row(
                            children: [
                              for (int i = 0; i < 4; i++)
                                Expanded(
                                  child: Container(
                                    height: 3,
                                    margin: EdgeInsets.only(right: i < 3 ? 4 : 0),
                                    decoration: BoxDecoration(
                                      color: i < _passwordStrength ? _strengthColor : AppColors.slate200,
                                      borderRadius: BorderRadius.circular(2),
                                    ),
                                  ),
                                ),
                            ],
                          ),
                          const SizedBox(height: 4),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                _strengthLabel,
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w600,
                                  color: _strengthColor,
                                ),
                              ),
                              const Text(
                                'Min. 8 characters',
                                style: TextStyle(fontSize: 11, color: AppColors.slate400),
                              ),
                            ],
                          ),
                        ],
                        const SizedBox(height: AppSpacing.lg),

                        // Confirm Password Field
                        CustomTextField(
                          controller: _confirmPasswordController,
                          label: 'Confirm password',
                          hint: 'Re-enter your password',
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
                            if (val == null || val.isEmpty) {
                              return 'Please confirm your password';
                            }
                            if (val != _passwordController.text) {
                              return 'Passwords do not match';
                            }
                            return null;
                          },
                        ),

                        // Password Match Indicator
                        if (_confirmPasswordController.text.isNotEmpty) ...[
                          const SizedBox(height: 6),
                          Row(
                            children: [
                              Icon(
                                _passwordsMatch ? Icons.check_circle_rounded : Icons.cancel_rounded,
                                size: 14,
                                color: _passwordsMatch ? AppColors.success : AppColors.danger,
                              ),
                              const SizedBox(width: 4),
                              Text(
                                _passwordsMatch ? 'Passwords match' : 'Passwords do not match',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w600,
                                  color: _passwordsMatch ? AppColors.success : AppColors.danger,
                                ),
                              ),
                            ],
                          ),
                        ],
                        const SizedBox(height: AppSpacing.lg),

                        // Terms & Privacy Checkbox
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            SizedBox(
                              width: 22,
                              height: 22,
                              child: Checkbox(
                                value: _agreedToTerms,
                                onChanged: (v) => setState(() => _agreedToTerms = v ?? false),
                                activeColor: AppColors.brand500,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
                              ),
                            ),
                            const SizedBox(width: AppSpacing.sm),
                            Expanded(
                              child: Padding(
                                padding: const EdgeInsets.only(top: 2),
                                child: Text.rich(
                                  TextSpan(
                                    style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.slate600),
                                    children: [
                                      const TextSpan(text: 'I agree to the Openbox '),
                                      WidgetSpan(
                                        alignment: PlaceholderAlignment.middle,
                                        child: GestureDetector(
                                          onTap: _openTerms,
                                          child: const Text(
                                            'Terms of Service',
                                            style: TextStyle(
                                              fontSize: 12,
                                              color: brandNavy,
                                              fontWeight: FontWeight.w700,
                                              decoration: TextDecoration.underline,
                                            ),
                                          ),
                                        ),
                                      ),
                                      const TextSpan(text: ' and '),
                                      WidgetSpan(
                                        alignment: PlaceholderAlignment.middle,
                                        child: GestureDetector(
                                          onTap: _openTerms,
                                          child: const Text(
                                            'Privacy Policy',
                                            style: TextStyle(
                                              fontSize: 12,
                                              color: brandNavy,
                                              fontWeight: FontWeight.w700,
                                              decoration: TextDecoration.underline,
                                            ),
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: AppSpacing.xl),

                        // Submit Button
                        CustomButton(
                          text: 'Create account',
                          isLoading: isLoading,
                          onPressed: _handleRegister,
                        ),
                        const SizedBox(height: AppSpacing.lg),

                        // Social Login Divider
                        const Row(
                          children: [
                            Expanded(child: Divider(color: AppColors.slate200, thickness: 1)),
                            Padding(
                              padding: EdgeInsets.symmetric(horizontal: AppSpacing.md),
                              child: Text(
                                'OR CONTINUE WITH',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w700,
                                  color: AppColors.slate400,
                                  letterSpacing: 0.8,
                                ),
                              ),
                            ),
                            Expanded(child: Divider(color: AppColors.slate200, thickness: 1)),
                          ],
                        ),
                        const SizedBox(height: AppSpacing.lg),

                        // Social Logins in One Line
                        Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            SocialLoginButton(
                              provider: SocialProvider.google,
                              isIconOnly: true,
                              isSignUp: true,
                              isLoading: _isGoogleLoading,
                              onPressed: _handleGoogleSignIn,
                            ),
                            const SizedBox(width: AppSpacing.md),
                            SocialLoginButton(
                              provider: SocialProvider.facebook,
                              isIconOnly: true,
                              isSignUp: true,
                              isLoading: _isFacebookLoading,
                              onPressed: _handleFacebookSignIn,
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: AppSpacing.xl),

                  // Bottom Log in Navigation
                  Center(
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Text(
                          'Already have an account?',
                          style: TextStyle(fontSize: 13, fontWeight: FontWeight.w500, color: AppColors.slate500),
                        ),
                        TextButton(
                          onPressed: () => Navigator.of(context).pop(),
                          child: const Text(
                            'Log in',
                            style: TextStyle(
                              fontWeight: FontWeight.w700,
                              color: brandNavy,
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

