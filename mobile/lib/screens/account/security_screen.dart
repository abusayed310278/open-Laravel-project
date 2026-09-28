import 'package:flutter/material.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';

class SecurityScreen extends StatefulWidget {
  const SecurityScreen({super.key});

  @override
  State<SecurityScreen> createState() => _SecurityScreenState();
}

class _SecurityScreenState extends State<SecurityScreen> {
  bool _obscureCurrent = true;
  bool _obscureNew = true;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Security')),
      body: ListView(
        padding: const EdgeInsets.all(AppSpacing.xxl),
        children: [
          const Text('Change Password', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.ink)),
          const SizedBox(height: AppSpacing.lg),
          const _FieldLabel('Current password'),
          TextField(
            obscureText: _obscureCurrent,
            decoration: InputDecoration(
              prefixIcon: const Icon(Icons.lock_outline_rounded),
              suffixIcon: IconButton(
                icon: Icon(_obscureCurrent ? Icons.visibility_off_outlined : Icons.visibility_outlined),
                onPressed: () => setState(() => _obscureCurrent = !_obscureCurrent),
              ),
            ),
          ),
          const SizedBox(height: AppSpacing.lg),
          const _FieldLabel('New password'),
          TextField(
            obscureText: _obscureNew,
            decoration: InputDecoration(
              hintText: 'At least 8 characters',
              prefixIcon: const Icon(Icons.lock_outline_rounded),
              suffixIcon: IconButton(
                icon: Icon(_obscureNew ? Icons.visibility_off_outlined : Icons.visibility_outlined),
                onPressed: () => setState(() => _obscureNew = !_obscureNew),
              ),
            ),
          ),
          const SizedBox(height: AppSpacing.lg),
          const _FieldLabel('Confirm new password'),
          const TextField(obscureText: true, decoration: InputDecoration(prefixIcon: Icon(Icons.lock_outline_rounded))),
          const SizedBox(height: AppSpacing.xl),
          ElevatedButton(
            onPressed: () => AppSnackbar.success(context, 'Password updated'),
            child: const Text('Update Password'),
          ),
          const SizedBox(height: AppSpacing.xxxl),
          const Divider(),
          const SizedBox(height: AppSpacing.lg),
          const Text('Login Sessions', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.ink)),
          const SizedBox(height: AppSpacing.md),
          Container(
            padding: const EdgeInsets.all(AppSpacing.md),
            decoration: BoxDecoration(color: AppColors.surface, border: Border.all(color: AppColors.slate200), borderRadius: BorderRadius.circular(AppRadius.md)),
            child: Row(
              children: [
                const Icon(Icons.smartphone_rounded, color: AppColors.brand600),
                const SizedBox(width: AppSpacing.md),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('This device', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                      Text('Doha, Qatar · Active now', style: TextStyle(fontSize: 11, color: AppColors.slate500)),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(color: AppColors.successBg, borderRadius: BorderRadius.circular(6)),
                  child: const Text('Current', style: TextStyle(fontSize: 10, color: AppColors.success, fontWeight: FontWeight.w700)),
                ),
              ],
            ),
          ),
        ],
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
