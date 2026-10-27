import 'package:flutter/material.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_spacing.dart';
import '../../../core/widgets/app_logo.dart';
import '../../store/vendor_terms_screen.dart';

class AboutScreen extends StatelessWidget {
  const AboutScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        title: const Text('About'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppSpacing.lg),
        child: Column(
          children: [
            const SizedBox(height: AppSpacing.md),

            // Logo & Title
            Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(AppRadius.xl),
                boxShadow: [
                  BoxShadow(
                    color: AppColors.brand500.withValues(alpha: 0.3),
                    blurRadius: 16,
                    offset: const Offset(0, 6),
                  ),
                ],
              ),
              child: const AppLogo(size: 80, borderRadius: AppRadius.xl),
            ),
            const SizedBox(height: AppSpacing.md),

            const Text(
              'Openbox Merchant Portal',
              style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: AppColors.ink),
            ),
            const SizedBox(height: 4),
            const Text(
              'Version 1.0.0 (Build 2026.10)',
              style: TextStyle(fontSize: 12, color: AppColors.slate500, fontWeight: FontWeight.w500),
            ),
            const SizedBox(height: AppSpacing.xl),

            // Description Card
            Container(
              padding: const EdgeInsets.all(AppSpacing.lg),
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(AppRadius.lg),
                border: Border.all(color: AppColors.slate200),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: const [
                  Text(
                    'Empowering Re-commerce & Electronics Merchants',
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.ink),
                  ),
                  SizedBox(height: 8),
                  Text(
                    'Openbox is the premier certified marketplace for open-box, certified refurbished, and brand-new electronics across Qatar and the GCC. Designed specifically for merchants, this portal provides real-time catalog management, integrated inventory grading, customer messaging, and protected wallet settlements.',
                    style: TextStyle(fontSize: 12, color: AppColors.slate600, height: 1.5),
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.lg),

            // Highlights
            Container(
              padding: const EdgeInsets.all(AppSpacing.md),
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(AppRadius.lg),
                border: Border.all(color: AppColors.slate200),
              ),
              child: Column(
                children: [
                  _buildFeatureRow(
                    icon: Icons.shield_outlined,
                    title: 'Escrow-Protected Settlements',
                    subtitle: 'Funds held securely until buyer confirmation',
                  ),
                  const Divider(height: 16, color: AppColors.slate100),
                  _buildFeatureRow(
                    icon: Icons.verified_outlined,
                    title: 'Physical Grading & Hub Inspection',
                    subtitle: 'Certified grading builds buyer confidence',
                  ),
                  const Divider(height: 16, color: AppColors.slate100),
                  _buildFeatureRow(
                    icon: Icons.electric_bolt_outlined,
                    title: 'Instant Customer Messaging',
                    subtitle: 'Real-time negotiation and inquiry threads',
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.lg),

            // Links Card
            Container(
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(AppRadius.lg),
                border: Border.all(color: AppColors.slate200),
              ),
              child: Column(
                children: [
                  ListTile(
                    leading: const Icon(Icons.description_outlined, size: 20, color: AppColors.slate600),
                    title: const Text('Vendor Terms of Service', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                    trailing: const Icon(Icons.chevron_right, size: 18, color: AppColors.slate400),
                    onTap: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(builder: (_) => const VendorTermsScreen()),
                      );
                    },
                  ),
                  const Divider(height: 1, indent: 56, color: AppColors.slate100),
                  ListTile(
                    leading: const Icon(Icons.privacy_tip_outlined, size: 20, color: AppColors.slate600),
                    title: const Text('Privacy & Data Policy', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                    trailing: const Icon(Icons.chevron_right, size: 18, color: AppColors.slate400),
                    onTap: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(builder: (_) => const VendorTermsScreen()),
                      );
                    },
                  ),
                  const Divider(height: 1, indent: 56, color: AppColors.slate100),
                  ListTile(
                    leading: const Icon(Icons.star_outline_rounded, size: 20, color: AppColors.brand500),
                    title: const Text('Rate Seller App', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                    trailing: const Icon(Icons.open_in_new, size: 16, color: AppColors.slate400),
                    onTap: () {},
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.xxl),

            const Text(
              '© 2026 Openbox Marketplace. All rights reserved.\nLicensed for commercial merchant operations in the State of Qatar.',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 11, color: AppColors.slate400, height: 1.4),
            ),
            const SizedBox(height: AppSpacing.xl),
          ],
        ),
      ),
    );
  }

  Widget _buildFeatureRow({required IconData icon, required String title, required String subtitle}) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(7),
          decoration: BoxDecoration(
            color: AppColors.brand50,
            borderRadius: BorderRadius.circular(AppRadius.sm),
          ),
          child: Icon(icon, size: 18, color: AppColors.brand600),
        ),
        const SizedBox(width: AppSpacing.md),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.ink)),
              Text(subtitle, style: const TextStyle(fontSize: 11, color: AppColors.slate500)),
            ],
          ),
        ),
      ],
    );
  }
}
