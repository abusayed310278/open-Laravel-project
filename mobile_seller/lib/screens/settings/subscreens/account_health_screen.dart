import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_spacing.dart';
import '../../../providers/seller_auth_provider.dart';

class AccountHealthScreen extends StatelessWidget {
  const AccountHealthScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<SellerAuthProvider>();
    final user = auth.user;
    final isVerified = user?.isKycApproved == true;

    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        title: const Text('Account Health'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppSpacing.lg),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // 1. Overall Status Card
            Container(
              padding: const EdgeInsets.all(AppSpacing.lg),
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(AppRadius.lg),
                border: Border.all(color: AppColors.slate200),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.04),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Column(
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: AppColors.success.withValues(alpha: 0.2),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(Icons.shield_outlined, color: AppColors.success, size: 28),
                      ),
                      const SizedBox(width: AppSpacing.md),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Account Status: Good',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w800,
                                color: AppColors.ink,
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              isVerified ? 'Fully verified merchant in high standing' : 'Pending verification review',
                              style: const TextStyle(fontSize: 12, color: AppColors.slate500),
                            ),
                          ],
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: AppColors.success,
                          borderRadius: BorderRadius.circular(AppRadius.pill),
                        ),
                        child: const Text(
                          '98 / 100',
                          style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Colors.white),
                        ),
                      ),
                    ],
                  ),
                  const Divider(height: 24, color: AppColors.slate200),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: const [
                      _HealthMiniStat(label: 'Policy Violations', value: '0', isPositive: true),
                      _HealthMiniStat(label: 'On-time Delivery', value: '99.4%', isPositive: true),
                      _HealthMiniStat(label: 'Customer Rating', value: '4.9 ★', isPositive: true),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.xl),

            // 2. Performance Metrics
            const Text(
              'Performance & SLA Metrics',
              style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: AppColors.ink),
            ),
            const SizedBox(height: AppSpacing.sm),

            _buildMetricTile(
              title: 'Order Defect Rate (ODR)',
              subtitle: 'Claims, negative feedback, chargebacks',
              currentValue: '0.18%',
              target: 'Target: < 1.0%',
              progress: 0.18,
              isGood: true,
              icon: Icons.assignment_late_outlined,
            ),
            const SizedBox(height: AppSpacing.sm),

            _buildMetricTile(
              title: 'Late Dispatch Rate',
              subtitle: 'Orders shipped past the fulfillment SLA window',
              currentValue: '0.00%',
              target: 'Target: < 4.0%',
              progress: 0.0,
              isGood: true,
              icon: Icons.local_shipping_outlined,
            ),
            const SizedBox(height: AppSpacing.sm),

            _buildMetricTile(
              title: 'Pre-fulfillment Cancellation Rate',
              subtitle: 'Seller-initiated order cancellations',
              currentValue: '0.00%',
              target: 'Target: < 2.5%',
              progress: 0.0,
              isGood: true,
              icon: Icons.cancel_outlined,
            ),
            const SizedBox(height: AppSpacing.sm),

            _buildMetricTile(
              title: 'Average Inquiry Response Time',
              subtitle: 'Speed of customer chat & ticket replies',
              currentValue: '1h 14m',
              target: 'Target: < 24h',
              progress: 0.08,
              isGood: true,
              icon: Icons.mark_chat_unread_outlined,
            ),
            const SizedBox(height: AppSpacing.xl),

            // 3. Compliance Checklist
            const Text(
              'Policy Compliance',
              style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: AppColors.ink),
            ),
            const SizedBox(height: AppSpacing.sm),

            Container(
              padding: const EdgeInsets.all(AppSpacing.md),
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(AppRadius.lg),
                border: Border.all(color: AppColors.slate200),
              ),
              child: Column(
                children: [
                  _buildComplianceRow('Identity & Commercial Registration', isVerified ? 'Compliant' : 'Under Review', isVerified),
                  const Divider(height: 16, color: AppColors.slate100),
                  _buildComplianceRow('Counterfeit & Authenticity Policy', '0 Complaints', true),
                  const Divider(height: 16, color: AppColors.slate100),
                  _buildComplianceRow('Intellectual Property Rights', 'No Infringements', true),
                  const Divider(height: 16, color: AppColors.slate100),
                  _buildComplianceRow('Listing Authenticity & Accuracy', 'Active & Approved', true),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.xl),

            // 4. Recommendation Card
            Container(
              padding: const EdgeInsets.all(AppSpacing.md),
              decoration: BoxDecoration(
                color: AppColors.brand50,
                borderRadius: BorderRadius.circular(AppRadius.md),
                border: Border.all(color: AppColors.brand200),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: const [
                  Icon(Icons.lightbulb_outline_rounded, color: AppColors.brand600, size: 22),
                  SizedBox(width: AppSpacing.sm),
                  Expanded(
                    child: Text(
                      'Keep up the great performance! Consistently keeping your cancellation rate under 1% qualifies your store for featured merchant placement and reduced escrow hold periods.',
                      style: TextStyle(fontSize: 12, color: AppColors.slate700, height: 1.4),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.xxl),
          ],
        ),
      ),
    );
  }

  Widget _buildMetricTile({
    required String title,
    required String subtitle,
    required String currentValue,
    required String target,
    required double progress,
    required bool isGood,
    required IconData icon,
  }) {
    return Container(
      padding: const EdgeInsets.all(AppSpacing.md),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.slate200),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: AppColors.brand50,
                  borderRadius: BorderRadius.circular(AppRadius.sm),
                ),
                child: Icon(icon, size: 18, color: AppColors.brand600),
              ),
              const SizedBox(width: AppSpacing.sm),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(title, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.ink)),
                    Text(subtitle, style: const TextStyle(fontSize: 11, color: AppColors.slate500)),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    currentValue,
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                      color: isGood ? AppColors.success : AppColors.danger,
                    ),
                  ),
                  Text(target, style: const TextStyle(fontSize: 10, color: AppColors.slate400)),
                ],
              ),
            ],
          ),
          const SizedBox(height: 10),
          ClipRRect(
            borderRadius: BorderRadius.circular(AppRadius.pill),
            child: LinearProgressIndicator(
              value: progress.clamp(0.02, 1.0),
              backgroundColor: AppColors.slate100,
              valueColor: AlwaysStoppedAnimation<Color>(isGood ? AppColors.success : AppColors.danger),
              minHeight: 6,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildComplianceRow(String label, String status, bool isOk) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Expanded(
          child: Text(
            label,
            style: const TextStyle(fontSize: 13, color: AppColors.slate700, fontWeight: FontWeight.w500),
          ),
        ),
        Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(isOk ? Icons.check_circle_rounded : Icons.pending_rounded, size: 14, color: isOk ? AppColors.success : AppColors.warning),
            const SizedBox(width: 4),
            Text(
              status,
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w700,
                color: isOk ? AppColors.success : AppColors.warning,
              ),
            ),
          ],
        ),
      ],
    );
  }
}

class _HealthMiniStat extends StatelessWidget {
  final String label;
  final String value;
  final bool isPositive;

  const _HealthMiniStat({
    required this.label,
    required this.value,
    required this.isPositive,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text(
          value,
          style: TextStyle(
            fontSize: 15,
            fontWeight: FontWeight.w800,
            color: isPositive ? AppColors.success : AppColors.ink,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          label,
          style: const TextStyle(fontSize: 11, color: AppColors.slate500),
        ),
      ],
    );
  }
}
