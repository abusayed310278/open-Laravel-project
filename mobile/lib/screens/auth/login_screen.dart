import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/widgets/app_logo.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../navigation/app_shell.dart';
import '../../providers/auth_provider.dart';
import 'forgot_password_screen.dart';
import 'register_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  bool _obscure = true;
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _submitLogin() async {
    final email = _emailController.text.trim();
    final password = _passwordController.text.trim();

    if (email.isEmpty || password.isEmpty) {
      AppSnackbar.showError(context, 'Please enter email and password');
      return;
    }

    final authProvider = context.read<AuthProvider>();
    final success = await authProvider.login(email, password);

    if (!mounted) return;

    if (success) {
      AppSnackbar.showSuccess(context, 'Welcome back!');
      if (Navigator.of(context).canPop()) {
        Navigator.of(context).pop();
      } else {
        Navigator.of(context).pushAndRemoveUntil(
          MaterialPageRoute(builder: (_) => const AppShell()),
          (route) => false,
        );
      }
    } else {
      AppSnackbar.showError(context, authProvider.errorMessage ?? 'Login failed. Check your credentials.');
    }
  }

  void _continueGuest() {
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const AppShell()),
      (route) => false,
    );
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<AuthProvider>();
    final isLoading = authProvider.state == AuthState.loading;

    return Scaffold(
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppSpacing.xxl),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: AppSpacing.xxl),
              const AppLogo(size: 56, borderRadius: 16),
              const SizedBox(height: AppSpacing.xl),
              const Text('Welcome back', style: TextStyle(fontSize: 26, fontWeight: FontWeight.w800, color: AppColors.ink)),
              const SizedBox(height: AppSpacing.xs),
              const Text(
                'Sign in to continue shopping verified refurbished & new tech.',
                style: TextStyle(fontSize: 13, color: AppColors.slate500),
              ),
              const SizedBox(height: AppSpacing.xxl),
              const _FieldLabel('Email or phone'),
              TextField(
                controller: _emailController,
                keyboardType: TextInputType.emailAddress,
                decoration: const InputDecoration(hintText: 'you@example.com', prefixIcon: Icon(Icons.mail_outline_rounded)),
              ),
              const SizedBox(height: AppSpacing.lg),
              const _FieldLabel('Password'),
              TextField(
                controller: _passwordController,
                obscureText: _obscure,
                decoration: InputDecoration(
                  hintText: '••••••••',
                  prefixIcon: const Icon(Icons.lock_outline_rounded),
                  suffixIcon: IconButton(
                    icon: Icon(_obscure ? Icons.visibility_off_outlined : Icons.visibility_outlined),
                    onPressed: () => setState(() => _obscure = !_obscure),
                  ),
                ),
              ),
              Align(
                alignment: Alignment.centerRight,
                child: TextButton(
                  onPressed: () => Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => const ForgotPasswordScreen()),
                  ),
                  child: const Text('Forgot password?'),
                ),
              ),
              const SizedBox(height: AppSpacing.sm),
              ElevatedButton(
                onPressed: isLoading ? null : _submitLogin,
                child: isLoading
                    ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : const Text('Sign in'),
              ),
              const SizedBox(height: AppSpacing.lg),
              Row(
                children: const [
                  Expanded(child: Divider()),
                  Padding(
                    padding: EdgeInsets.symmetric(horizontal: AppSpacing.md),
                    child: Text('or continue with', style: TextStyle(fontSize: 12, color: AppColors.slate400)),
                  ),
                  Expanded(child: Divider()),
                ],
              ),
              const SizedBox(height: AppSpacing.lg),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: _continueGuest,
                      icon: const Icon(Icons.g_mobiledata_rounded, size: 24),
                      label: const Text('Google'),
                    ),
                  ),
                  const SizedBox(width: AppSpacing.md),
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: _continueGuest,
                      icon: const Icon(Icons.apple_rounded, size: 20),
                      label: const Text('Apple'),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: AppSpacing.xxl),
              Center(
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Text("Don't have an account?", style: TextStyle(fontSize: 13, color: AppColors.slate500)),
                    TextButton(
                      onPressed: () => Navigator.of(context).push(
                        MaterialPageRoute(builder: (_) => const RegisterScreen()),
                      ),
                      child: const Text('Sign up'),
                    ),
                  ],
                ),
              ),
              Center(
                child: TextButton(
                  onPressed: _continueGuest,
                  child: const Text('Continue as guest', style: TextStyle(color: AppColors.slate500)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _FieldLabel extends StatelessWidget {
  const _FieldLabel(this.text);
  final String text;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: AppSpacing.xs, top: AppSpacing.xs),
      child: Text(text, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.slate700)),
    );
  }
}
