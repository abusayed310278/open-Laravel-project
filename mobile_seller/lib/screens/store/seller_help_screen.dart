import 'package:flutter/material.dart';

import '../../core/network/api_client.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/loading_view.dart';

class SellerHelpScreen extends StatefulWidget {
  const SellerHelpScreen({super.key});

  @override
  State<SellerHelpScreen> createState() => _SellerHelpScreenState();
}

class _SellerHelpScreenState extends State<SellerHelpScreen> {
  final ApiClient _apiClient = ApiClient();
  bool _isLoading = true;
  String? _errorMessage;
  Map<String, dynamic>? _data;

  @override
  void initState() {
    super.initState();
    _fetchGuidelines();
  }

  Future<void> _fetchGuidelines() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final response = await _apiClient.get('/help-guidelines');
      if (mounted) {
        setState(() {
          _data = response is Map<String, dynamic> ? response : null;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _errorMessage = e.toString();
          _isLoading = false;
        });
      }
    }
  }

  IconData _resolveIcon(String? iconName) {
    switch (iconName) {
      case 'verified_user':
        return Icons.verified_user_outlined;
      case 'inventory_2':
        return Icons.inventory_2_outlined;
      case 'local_shipping':
        return Icons.local_shipping_outlined;
      case 'account_balance_wallet':
        return Icons.account_balance_wallet_outlined;
      case 'assignment_return':
        return Icons.assignment_return_outlined;
      default:
        return Icons.help_outline_rounded;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        title: const Text('Seller Help & Guidelines'),
      ),
      body: _isLoading
          ? const LoadingView(message: 'Loading guidelines...')
          : _errorMessage != null
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(AppSpacing.xl),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.error_outline, size: 48, color: AppColors.danger),
                        const SizedBox(height: AppSpacing.md),
                        Text(_errorMessage!, textAlign: TextAlign.center, style: const TextStyle(color: AppColors.slate600)),
                        const SizedBox(height: AppSpacing.lg),
                        ElevatedButton(onPressed: _fetchGuidelines, child: const Text('Retry')),
                      ],
                    ),
                  ),
                )
              : _buildContent(),
    );
  }

  Widget _buildContent() {
    final sections = (_data?['sections'] as List?) ?? [];
    final faqs = (_data?['faqs'] as List?) ?? [];
    final supportEmail = _data?['support_email']?.toString() ?? 'support@openbox.qa';

    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // 1. Header Banner
          Container(
            padding: const EdgeInsets.all(AppSpacing.lg),
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(AppRadius.md),
              border: Border.all(color: AppColors.slate200),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(AppSpacing.md),
                  decoration: BoxDecoration(
                    color: AppColors.brand50,
                    borderRadius: BorderRadius.circular(AppRadius.md),
                  ),
                  child: const Icon(Icons.support_agent_rounded, size: 28, color: AppColors.brand500),
                ),
                const SizedBox(width: AppSpacing.md),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Merchant Support Hub',
                        style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.ink),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Have questions? Email $supportEmail',
                        style: const TextStyle(fontSize: 12, color: AppColors.slate500),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: AppSpacing.xl),

          // 2. Guidelines & Policies Header
          const Text(
            'Core Operational Guidelines',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.ink),
          ),
          const SizedBox(height: AppSpacing.md),

          // Sections List
          ...sections.map((item) {
            final s = item as Map<String, dynamic>;
            final title = s['title']?.toString() ?? '';
            final content = s['content']?.toString() ?? '';
            final icon = _resolveIcon(s['icon']?.toString());

            return Container(
              margin: const EdgeInsets.only(bottom: AppSpacing.md),
              padding: const EdgeInsets.all(AppSpacing.lg),
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(AppRadius.md),
                border: Border.all(color: AppColors.slate200),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    padding: const EdgeInsets.all(AppSpacing.sm),
                    decoration: BoxDecoration(
                      color: AppColors.slate100,
                      borderRadius: BorderRadius.circular(AppRadius.sm),
                    ),
                    child: Icon(icon, size: 20, color: AppColors.brand600),
                  ),
                  const SizedBox(width: AppSpacing.md),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          title,
                          style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.ink),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          content,
                          style: const TextStyle(fontSize: 13, height: 1.45, color: AppColors.slate600),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            );
          }),

          const SizedBox(height: AppSpacing.lg),

          // 3. Frequently Asked Questions
          if (faqs.isNotEmpty) ...[
            const Text(
              'Frequently Asked Questions',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.ink),
            ),
            const SizedBox(height: AppSpacing.md),
            ...faqs.map((faqItem) {
              final faq = faqItem as Map<String, dynamic>;
              return Container(
                margin: const EdgeInsets.only(bottom: AppSpacing.sm),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(AppRadius.md),
                  border: Border.all(color: AppColors.slate200),
                ),
                child: Theme(
                  data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
                  child: ExpansionTile(
                    title: Text(
                      faq['question']?.toString() ?? '',
                      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.ink),
                    ),
                    children: [
                      Padding(
                        padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                        child: Text(
                          faq['answer']?.toString() ?? '',
                          style: const TextStyle(fontSize: 13, height: 1.4, color: AppColors.slate600),
                        ),
                      ),
                    ],
                  ),
                ),
              );
            }),
          ],
          const SizedBox(height: AppSpacing.xl),
        ],
      ),
    );
  }
}
