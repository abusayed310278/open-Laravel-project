import 'package:flutter/material.dart';

import '../../../core/network/api_client.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_spacing.dart';
import '../../../core/widgets/empty_view.dart';
import '../../../core/widgets/loading_view.dart';

class InspectionQueueScreen extends StatefulWidget {
  const InspectionQueueScreen({super.key});

  @override
  State<InspectionQueueScreen> createState() => _InspectionQueueScreenState();
}

class _InspectionQueueScreenState extends State<InspectionQueueScreen> {
  final ApiClient _apiClient = ApiClient();
  bool _isLoading = true;
  String? _errorMessage;
  List<dynamic> _inspections = [];
  Map<String, dynamic> _summary = {};

  @override
  void initState() {
    super.initState();
    _fetchInspections();
  }

  Future<void> _fetchInspections() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final response = await _apiClient.get('/inspections');
      if (mounted && response is Map<String, dynamic>) {
        setState(() {
          _inspections = (response['inspections'] as List?) ?? [];
          _summary = (response['summary'] as Map<String, dynamic>?) ?? {};
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
      appBar: AppBar(title: const Text('Product Inspection Queue')),
      body: _isLoading
          ? const LoadingView(message: 'Loading inspection queue...')
          : _errorMessage != null && _inspections.isEmpty
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(AppSpacing.xl),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.error_outline, size: 48, color: AppColors.danger),
                        const SizedBox(height: AppSpacing.md),
                        Text(_errorMessage!, textAlign: TextAlign.center, style: const TextStyle(color: AppColors.slate600)),
                        const SizedBox(height: AppSpacing.md),
                        ElevatedButton(onPressed: _fetchInspections, child: const Text('Retry')),
                      ],
                    ),
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _fetchInspections,
                  color: AppColors.brand500,
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(AppSpacing.lg),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Summary Grid
                    Row(
                      children: [
                        _buildSummaryCard('Total', '${_summary['total'] ?? 0}', AppColors.ink),
                        const SizedBox(width: 8),
                        _buildSummaryCard('Pending', '${_summary['pending'] ?? 0}', AppColors.brand600),
                        const SizedBox(width: 8),
                        _buildSummaryCard('Scheduled', '${_summary['scheduled'] ?? 0}', AppColors.warning),
                        const SizedBox(width: 8),
                        _buildSummaryCard('Verified', '${_summary['verified'] ?? 0}', AppColors.success),
                      ],
                    ),
                    const SizedBox(height: AppSpacing.xl),

                    const Text('Inspection Appointments & Requests', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.ink)),
                    const SizedBox(height: AppSpacing.md),

                    if (_inspections.isEmpty)
                      const EmptyView(
                        icon: Icons.fact_check_outlined,
                        title: 'Inspection Queue Clean',
                        subtitle: 'No products currently awaiting verification or station inspection.',
                      )
                    else
                      ListView.separated(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemCount: _inspections.length,
                        separatorBuilder: (_, _) => const SizedBox(height: AppSpacing.md),
                        itemBuilder: (context, index) {
                          final item = _inspections[index] as Map<String, dynamic>;
                          final status = item['status']?.toString() ?? 'pending';

                          Color statusColor = AppColors.brand600;
                          if (status == 'verified') statusColor = AppColors.success;
                          if (status == 'rejected') statusColor = AppColors.danger;
                          if (status == 'scheduled') statusColor = AppColors.warning;

                          return Container(
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
                                        item['product_title']?.toString() ?? 'Product',
                                        style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.ink),
                                      ),
                                    ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                      decoration: BoxDecoration(
                                        color: statusColor.withValues(alpha: 0.12),
                                        borderRadius: BorderRadius.circular(AppRadius.pill),
                                      ),
                                      child: Text(
                                        status.toUpperCase(),
                                        style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: statusColor),
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 6),
                                Text('SKU: ${item['product_sku']}', style: const TextStyle(fontSize: 12, color: AppColors.slate500)),
                                const Divider(height: 18, color: AppColors.slate100),
                                Row(
                                  children: [
                                    const Icon(Icons.location_on_outlined, size: 16, color: AppColors.slate400),
                                    const SizedBox(width: 4),
                                    Expanded(
                                      child: Text(
                                        item['location_name']?.toString() ?? 'Station Hub',
                                        style: const TextStyle(fontSize: 12, color: AppColors.slate600),
                                      ),
                                    ),
                                  ],
                                ),
                                if (item['scheduled_at'] != null) ...[
                                  const SizedBox(height: 4),
                                  Row(
                                    children: [
                                      const Icon(Icons.calendar_today_outlined, size: 16, color: AppColors.slate400),
                                      const SizedBox(width: 4),
                                      Text(
                                        'Scheduled: ${item['scheduled_at']}',
                                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.slate700),
                                      ),
                                    ],
                                  ),
                                ],
                              ],
                            ),
                          );
                        },
                      ),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildSummaryCard(String label, String value, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 4),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(AppRadius.md),
          border: Border.all(color: AppColors.slate200),
        ),
        child: Column(
          children: [
            Text(value, style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: color)),
            const SizedBox(height: 2),
            Text(label, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppColors.slate500)),
          ],
        ),
      ),
    );
  }
}
