import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_logo.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/custom_text_field.dart';
import '../../providers/seller_auth_provider.dart';
import '../navigation/main_navigation_screen.dart';
import 'register_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _obscurePassword = true;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _handleLogin() async {
    if (!_formKey.currentState!.validate()) return;

    final authProvider = context.read<SellerAuthProvider>();
    final success = await authProvider.login(
      _emailController.text,
      _passwordController.text,
    );

    if (!mounted) return;

    if (success) {
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const MainNavigationScreen()),
      );
    } else {
      final error = authProvider.errorMessage ?? 'Login failed. Please check credentials.';
      AppSnackBar.error(context, error);
    }
  }

  void _showForgotPasswordNotice() {
    AppSnackBar.info(
      context,
      'Please contact Openbox support to reset your seller account password.',
      title: 'Forgot password',
    );
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<SellerAuthProvider>();

    return Scaffold(
      backgroundColor: AppColors.slate50,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: AppSpacing.xxl, vertical: AppSpacing.xl),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const SizedBox(height: AppSpacing.xl),
                Center(
                  child: Container(
                    width: 64,
                    height: 64,
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: AppColors.brand50,
                      borderRadius: BorderRadius.circular(AppRadius.lg),
                    ),
                    child: const AppLogo(size: 44, borderRadius: 10),
                  ),
                ),
                const SizedBox(height: AppSpacing.xl),
                const Text(
                  'Welcome back',
                  style: TextStyle(
                    fontSize: 26,
                    fontWeight: FontWeight.w800,
                    color: AppColors.ink,
                    letterSpacing: -0.5,
                  ),
                ),
                const SizedBox(height: AppSpacing.xs),
                const Text(
                  'Sign in to manage your orders, listings & sales analytics.',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w500,
                    color: AppColors.slate500,
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
                        controller: _emailController,
                        label: 'Email or phone',
                        hint: 'name@store.com',
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
                        controller: _passwordController,
                        label: 'Password',
                        hint: '••••••••',
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
                            return 'Please enter your password';
                          }
                          return null;
                        },
                      ),
                      Align(
                        alignment: Alignment.centerRight,
                        child: TextButton(
                          onPressed: _showForgotPasswordNotice,
                          child: const Text('Forgot password?', style: TextStyle(fontWeight: FontWeight.w600)),
                        ),
                      ),
                      const SizedBox(height: AppSpacing.sm),
                      CustomButton(
                        text: 'Sign in',
                        isLoading: authProvider.state == AuthState.loading,
                        onPressed: _handleLogin,
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
                        "Don't have a seller account?",
                        style: TextStyle(fontSize: 13, fontWeight: FontWeight.w500, color: AppColors.slate500),
                      ),
                      TextButton(
                        onPressed: () => Navigator.of(context).push(
                          MaterialPageRoute(builder: (_) => const RegisterScreen()),
                        ),
                        child: const Text('Sign up', style: TextStyle(fontWeight: FontWeight.w700)),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: AppSpacing.lg),
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.shield_outlined, size: 16, color: AppColors.slate400),
                    const SizedBox(width: 6),
                    const Text(
                      'Authorized Seller & Store Owner Access Only',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: AppColors.slate400,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
