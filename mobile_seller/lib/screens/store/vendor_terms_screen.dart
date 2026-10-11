import 'package:flutter/material.dart';

import '../../core/network/api_client.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/loading_view.dart';

class VendorTermsScreen extends StatefulWidget {
  const VendorTermsScreen({super.key});

  @override
  State<VendorTermsScreen> createState() => _VendorTermsScreenState();
}

class _VendorTermsScreenState extends State<VendorTermsScreen> {
  final ApiClient _apiClient = ApiClient();
  bool _isLoading = true;
  String? _errorMessage;
  Map<String, dynamic>? _data;

  @override
  void initState() {
    super.initState();
    _fetchTerms();
  }

  Future<void> _fetchTerms() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final response = await _apiClient.get('/terms-of-service');
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        title: const Text('Vendor Terms of Service'),
      ),
      body: _isLoading
          ? const LoadingView(message: 'Loading terms of service...')
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
                        ElevatedButton(onPressed: _fetchTerms, child: const Text('Retry')),
                      ],
                    ),
                  ),
                )
              : _buildContent(),
    );
  }

  Widget _buildContent() {
    final title = _data?['title']?.toString() ?? 'Vendor Terms of Service';
    final lastUpdated = _data?['last_updated']?.toString() ?? 'October 2026';
    final summary = _data?['summary']?.toString() ?? '';
    final sections = (_data?['sections'] as List?) ?? [];

    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // 1. Header Box
          Container(
            padding: const EdgeInsets.all(AppSpacing.lg),
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(AppRadius.md),
              border: Border.all(color: AppColors.slate200),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Text(
                        title,
                        style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.ink),
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: AppColors.brand50,
                        borderRadius: BorderRadius.circular(AppRadius.sm),
                        border: Border.all(color: AppColors.brand200),
                      ),
                      child: Text(
                        'v2026',
                        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.brand700),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                Text(
                  'Last updated: $lastUpdated',
                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.slate500),
                ),
                if (summary.isNotEmpty) ...[
                  const Divider(height: 20, color: AppColors.slate100),
                  Text(
                    summary,
                    style: const TextStyle(fontSize: 13, height: 1.45, color: AppColors.slate600),
                  ),
                ],
              ],
            ),
          ),
          const SizedBox(height: AppSpacing.xl),

          // 2. Sections
          ...sections.map((item) {
            final sec = item as Map<String, dynamic>;
            final secTitle = sec['title']?.toString() ?? '';
            final secBody = sec['body']?.toString() ?? '';

            return Container(
              margin: const EdgeInsets.only(bottom: AppSpacing.md),
              padding: const EdgeInsets.all(AppSpacing.lg),
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(AppRadius.md),
                border: Border.all(color: AppColors.slate200),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    secTitle,
                    style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.ink),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    secBody,
                    style: const TextStyle(fontSize: 13, height: 1.5, color: AppColors.slate600),
                  ),
                ],
              ),
            );
          }),

          const SizedBox(height: AppSpacing.xl),
        ],
      ),
    );
  }
}
