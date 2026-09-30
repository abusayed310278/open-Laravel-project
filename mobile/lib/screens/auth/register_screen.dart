import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../providers/auth_provider.dart';

import 'verify_otp_screen.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  bool _obscure = true;
  bool _agreed = false;

  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  Future<void> _register() async {
    final name = _nameController.text.trim();
    final email = _emailController.text.trim();
    final phone = _phoneController.text.trim();
    final password = _passwordController.text.trim();
    final confirmPassword = _confirmPasswordController.text.trim();

    if (name.isEmpty || email.isEmpty || password.isEmpty || confirmPassword.isEmpty) {
      AppSnackbar.showError(context, 'Please fill in all required fields');
      return;
    }

    if (password.length < 8) {
      AppSnackbar.showError(context, 'Password must be at least 8 characters');
      return;
    }

    if (password != confirmPassword) {
      AppSnackbar.showError(context, 'Passwords do not match');
      return;
    }

    final authProvider = context.read<AuthProvider>();
    final result = await authProvider.register(name, email, password, phone: phone);

    if (!mounted) return;

    if (result['success'] == true) {
      final msg = result['message']?.toString() ?? 'Registration successful!';
      final otp = result['otp']?.toString() ?? '';
      final regEmail = result['email']?.toString() ?? email;

      AppSnackbar.showSuccess(context, msg);
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(
          builder: (_) => VerifyOtpScreen(
            email: regEmail,
            initialOtp: otp,
          ),
        ),
      );
    } else {
      AppSnackbar.showError(context, result['message']?.toString() ?? 'Registration failed.');
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<AuthProvider>();
    final isLoading = authProvider.state == AuthState.loading;

    return Scaffold(
      appBar: AppBar(title: const Text('Create account')),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppSpacing.xxl),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: AppSpacing.sm),
              const _FieldLabel('Full name'),
              TextField(
                controller: _nameController,
                decoration: const InputDecoration(hintText: 'Sarah Ahmed', prefixIcon: Icon(Icons.person_outline_rounded)),
              ),
              const SizedBox(height: AppSpacing.lg),
              const _FieldLabel('Email'),
              TextField(
                controller: _emailController,
                keyboardType: TextInputType.emailAddress,
                decoration: const InputDecoration(hintText: 'you@example.com', prefixIcon: Icon(Icons.mail_outline_rounded)),
              ),
              const SizedBox(height: AppSpacing.lg),
              const _FieldLabel('Phone number'),
              TextField(
                controller: _phoneController,
                keyboardType: TextInputType.phone,
                decoration: const InputDecoration(hintText: '+974 5555 1234', prefixIcon: Icon(Icons.phone_outlined)),
              ),
              const SizedBox(height: AppSpacing.lg),
              const _FieldLabel('Password'),
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
              const _FieldLabel('Confirm Password'),
              TextField(
                controller: _confirmPasswordController,
                obscureText: _obscure,
                decoration: const InputDecoration(
                  hintText: 'Re-enter your password',
                  prefixIcon: Icon(Icons.lock_outline_rounded),
                ),
              ),
              const SizedBox(height: AppSpacing.lg),
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Checkbox(value: _agreed, onChanged: (v) => setState(() => _agreed = v ?? false)),
                  const Expanded(
                    child: Padding(
                      padding: EdgeInsets.only(top: AppSpacing.sm),
                      child: Text.rich(
                        TextSpan(
                          style: TextStyle(fontSize: 12, color: AppColors.slate500),
                          children: [
                            TextSpan(text: 'I agree to the '),
                            TextSpan(text: 'Terms of Service', style: TextStyle(color: AppColors.brand700, fontWeight: FontWeight.w600)),
                            TextSpan(text: ' and '),
                            TextSpan(text: 'Privacy Policy', style: TextStyle(color: AppColors.brand700, fontWeight: FontWeight.w600)),
                          ],
                        ),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: AppSpacing.md),
              ElevatedButton(
                onPressed: (_agreed && !isLoading) ? _register : null,
                child: isLoading
                    ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : const Text('Create account'),
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
      padding: const EdgeInsets.only(bottom: AppSpacing.xs),
      child: Text(text, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.slate700)),
    );
  }
}
