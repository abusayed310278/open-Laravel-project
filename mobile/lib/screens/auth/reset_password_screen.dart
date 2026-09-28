import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_logo.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../providers/auth_provider.dart';
import 'login_screen.dart';

class ResetPasswordScreen extends StatefulWidget {
  const ResetPasswordScreen({
    super.key,
    required this.email,
    required this.token,
  });

  final String email;
  final String token;

  @override
  State<ResetPasswordScreen> createState() => _ResetPasswordScreenState();
}

class _ResetPasswordScreenState extends State<ResetPasswordScreen> {
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  bool _obscure = true;

  @override
  void dispose() {
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  Future<void> _submitReset() async {
    final password = _passwordController.text.trim();
    final confirmPassword = _confirmPasswordController.text.trim();

    if (password.isEmpty || confirmPassword.isEmpty) {
      AppSnackbar.showError(context, 'Please enter and confirm your new password');
      return;
    }

    if (password.length < 8) {
      AppSnackbar.showError(context, 'New password must be at least 8 characters');
      return;
    }

    if (password != confirmPassword) {
      AppSnackbar.showError(context, 'Passwords do not match');
      return;
    }

    final authProvider = context.read<AuthProvider>();
    final success = await authProvider.resetPassword(widget.email, widget.token, password);

    if (!mounted) return;

    if (success) {
      AppSnackbar.showSuccess(context, 'Password reset successfully! Please sign in.');
      Navigator.of(context).pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const LoginScreen()),
        (route) => false,
      );
    } else {
      AppSnackbar.showError(
        context,
        authProvider.errorMessage ?? 'Failed to reset password. Please try again.',
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<AuthProvider>();
    final isLoading = authProvider.state == AuthState.loading;

    return Scaffold(
      appBar: AppBar(title: const Text('Reset Password')),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppSpacing.xxl),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const AppLogo(size: 56, borderRadius: 16),
              const SizedBox(height: AppSpacing.xl),
              const Text(
                'Create New Password',
                style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: AppColors.ink),
              ),
              const SizedBox(height: AppSpacing.xs),
              Text(
                'Set a new password for account ${widget.email}.',
                style: const TextStyle(fontSize: 13, color: AppColors.slate500, height: 1.4),
              ),
              const SizedBox(height: AppSpacing.xxl),
              const Text(
                'New Password',
                style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.slate700),
              ),
              const SizedBox(height: AppSpacing.xs),
              TextField(
                controller: _passwordController,
                obscureText: _obscure,
                decoration: InputDecoration(
                  hintText: 'At least 8 characters',
                  prefixIcon: const Icon(Icons.lock_outline_rounded),
                  suffixIcon: IconButton(
                    icon: Icon(_obscure ? Icons.visibility_off_outlined : Icons.visibility_outlined),
                    onPressed: () => setState(() => _obscure = !_obscure),
                  ),
                ),
              ),
              const SizedBox(height: AppSpacing.lg),
              const Text(
                'Confirm New Password',
                style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.slate700),
              ),
              const SizedBox(height: AppSpacing.xs),
              TextField(
                controller: _confirmPasswordController,
                obscureText: _obscure,
                decoration: const InputDecoration(
                  hintText: 'Re-enter your new password',
                  prefixIcon: Icon(Icons.lock_outline_rounded),
                ),
              ),
              const SizedBox(height: AppSpacing.xxl),
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton(
                  onPressed: isLoading ? null : _submitReset,
                  child: isLoading
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                        )
                      : const Text('Reset Password'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

