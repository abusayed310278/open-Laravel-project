import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/custom_text_field.dart';
import '../../providers/seller_auth_provider.dart';
import '../navigation/main_navigation_screen.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  bool _obscurePassword = true;
  bool _obscureConfirmPassword = true;
  bool _agreedToTerms = false;

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  Future<void> _handleRegister() async {
    if (!_formKey.currentState!.validate()) return;

    if (!_agreedToTerms) {
      AppSnackBar.warning(context, 'Please agree to the Terms of Service and Privacy Policy');
      return;
    }

    final authProvider = context.read<SellerAuthProvider>();
    final success = await authProvider.register(
      name: _nameController.text,
      email: _emailController.text,
      phone: _phoneController.text,
      storeName: _nameController.text,
      password: _passwordController.text,
      passwordConfirmation: _confirmPasswordController.text,
    );

    if (!mounted) return;

    if (success) {
      AppSnackBar.success(context, 'Account created! Welcome to Openbox.');
      Navigator.of(context).pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const MainNavigationScreen()),
        (route) => false,
      );
    } else {
      final error = authProvider.errorMessage ?? 'Registration failed. Please try again.';
      AppSnackBar.error(context, error);
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<SellerAuthProvider>();
    final isLoading = authProvider.state == AuthState.loading;

    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        backgroundColor: AppColors.slate50,
        elevation: 0,
        title: const Text(
          'Create seller account',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.ink),
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: AppSpacing.xxl, vertical: AppSpacing.lg),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const Text(
                  'Set up your store',
                  style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: AppColors.ink, letterSpacing: -0.5),
                ),
                const SizedBox(height: AppSpacing.xs),
                const Text(
                  'Start selling refurbished & new tech on Openbox.',
                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.w500, color: AppColors.slate500),
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
                          if (!val.contains('@')) {
                            return 'Please enter a valid email';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: AppSpacing.lg),
                      CustomTextField(
                        controller: _phoneController,
                        label: 'Phone number',
                        hint: '+974 5555 1234',
                        keyboardType: TextInputType.phone,
                        prefixIcon: const Icon(Icons.phone_outlined, size: 20, color: AppColors.slate400),
                      ),
                      const SizedBox(height: AppSpacing.lg),
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
                      const SizedBox(height: AppSpacing.lg),
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
                          if (val != _passwordController.text) {
                            return 'Passwords do not match';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: AppSpacing.lg),
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          SizedBox(
                            width: 24,
                            height: 24,
                            child: Checkbox(
                              value: _agreedToTerms,
                              onChanged: (v) => setState(() => _agreedToTerms = v ?? false),
                              activeColor: AppColors.brand500,
                            ),
                          ),
                          const SizedBox(width: AppSpacing.sm),
                          Expanded(
                            child: Padding(
                              padding: const EdgeInsets.only(top: AppSpacing.xs),
                              child: Text.rich(
                                TextSpan(
                                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.slate500),
                                  children: const [
                                    TextSpan(text: 'I agree to the '),
                                    TextSpan(
                                      text: 'Terms of Service',
                                      style: TextStyle(color: AppColors.brand700, fontWeight: FontWeight.w700),
                                    ),
                                    TextSpan(text: ' and '),
                                    TextSpan(
                                      text: 'Privacy Policy',
                                      style: TextStyle(color: AppColors.brand700, fontWeight: FontWeight.w700),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: AppSpacing.xl),
                      CustomButton(
                        text: 'Create account',
                        isLoading: isLoading,
                        onPressed: _handleRegister,
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: AppSpacing.xxl),
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
                        child: const Text('Sign in', style: TextStyle(fontWeight: FontWeight.w700)),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
