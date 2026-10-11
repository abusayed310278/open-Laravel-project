import 'package:flutter/material.dart';

import '../../../core/network/api_client.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_spacing.dart';
import '../../../core/widgets/custom_button.dart';
import '../../../core/widgets/loading_view.dart';

class SubscriptionPlanScreen extends StatefulWidget {
  const SubscriptionPlanScreen({super.key});

  @override
  State<SubscriptionPlanScreen> createState() => _SubscriptionPlanScreenState();
}

class _SubscriptionPlanScreenState extends State<SubscriptionPlanScreen> {
  final ApiClient _apiClient = ApiClient();
  bool _isLoading = true;
  String? _errorMessage;
  Map<String, dynamic>? _currentPlan;
  List<dynamic> _plans = [];

  @override
  void initState() {
    super.initState();
    _fetchSubscriptions();
  }

  Future<void> _fetchSubscriptions() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final response = await _apiClient.get('/subscriptions');
      if (mounted) {
        setState(() {
          if (response is Map<String, dynamic>) {
            _currentPlan = response['current_plan'] as Map<String, dynamic>?;
            _plans = (response['plans'] as List?) ?? [];
          }
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
      appBar: AppBar(title: const Text('Subscription Plan & Credits')),
      body: _isLoading
          ? const LoadingView(message: 'Loading subscription details...')
          : RefreshIndicator(
              onRefresh: _fetchSubscriptions,
              color: AppColors.brand500,
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(AppSpacing.lg),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Active Plan Hero Card
                    Container(
                      padding: const EdgeInsets.all(AppSpacing.xl),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [AppColors.slate900, AppColors.slate800],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.circular(AppRadius.lg),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.08),
                            blurRadius: 10,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                _currentPlan?['name']?.toString() ?? 'Active Plan',
                                style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: Colors.white),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                decoration: BoxDecoration(
                                  color: AppColors.brand500,
                                  borderRadius: BorderRadius.circular(AppRadius.pill),
                                ),
                                child: Text(
                                  (_currentPlan?['status'] ?? 'Active').toString().toUpperCase(),
                                  style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: Colors.white),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: AppSpacing.lg),
                          Row(
                            children: [
                              _buildMetric(
                                'Listing Credits',
                                '${_currentPlan?['listing_credits'] ?? 0}',
                                Icons.bolt_rounded,
                              ),
                              const SizedBox(width: AppSpacing.xl),
                              _buildMetric(
                                'Billing Cycle',
                                _currentPlan?['interval']?.toString().toUpperCase() ?? 'MONTHLY',
                                Icons.calendar_today_rounded,
                              ),
                            ],
                          ),
                          const Divider(height: 28, color: AppColors.slate700),
                          Text(
                            'Renews on: ${_currentPlan?['renews_at'] ?? 'Continuous'}',
                            style: const TextStyle(fontSize: 12, color: AppColors.slate400),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: AppSpacing.xxl),

                    // Available Plans Section
                    const Text('Available Merchant Tiers', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.ink)),
                    const SizedBox(height: AppSpacing.xs),
                    const Text('Upgrade to unlock higher listing capacities and reduced platform commission.', style: TextStyle(fontSize: 12, color: AppColors.slate500)),
                    const SizedBox(height: AppSpacing.lg),

                    if (_plans.isEmpty)
                      const Center(child: Text('No plans available at this moment.'))
                    else
                      ..._plans.map((p) {
                        final plan = p as Map<String, dynamic>;
                        final isCurrent = plan['name'] == _currentPlan?['name'];
                        final features = (plan['features'] as List?) ?? [];

                        return Container(
                          margin: const EdgeInsets.only(bottom: AppSpacing.lg),
                          padding: const EdgeInsets.all(AppSpacing.xl),
                          decoration: BoxDecoration(
                            color: AppColors.surface,
                            borderRadius: BorderRadius.circular(AppRadius.lg),
                            border: Border.all(
                              color: isCurrent ? AppColors.brand500 : AppColors.slate200,
                              width: isCurrent ? 2 : 1,
                            ),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(plan['name'] ?? '', style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.ink)),
                                  Text(
                                    '${plan['price']} QAR / ${plan['interval'] ?? 'mo'}',
                                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.brand600),
                                  ),
                                ],
                              ),
                              if (plan['description'] != null) ...[
                                const SizedBox(height: 4),
                                Text(plan['description'], style: const TextStyle(fontSize: 12, color: AppColors.slate500)),
                              ],
                              const Divider(height: 20, color: AppColors.slate100),
                              ...features.map((f) => Padding(
                                    padding: const EdgeInsets.only(bottom: 6),
                                    child: Row(
                                      children: [
                                        const Icon(Icons.check_circle_rounded, size: 16, color: AppColors.success),
                                        const SizedBox(width: 8),
                                        Expanded(child: Text(f.toString(), style: const TextStyle(fontSize: 13, color: AppColors.slate700))),
                                      ],
                                    ),
                                  )),
                              const SizedBox(height: AppSpacing.md),
                              CustomButton(
                                text: isCurrent ? 'Current Plan' : 'Select Plan',
                                isOutlined: isCurrent,
                                onPressed: isCurrent
                                    ? null
                                    : () {
                                        ScaffoldMessenger.of(context).showSnackBar(
                                          SnackBar(
                                            content: Text('Selected ${plan['name']} tier. Please complete payment via Web Portal or contact support.'),
                                            backgroundColor: AppColors.brand600,
                                          ),
                                        );
                                      },
                              ),
                            ],
                          ),
                        );
                      }),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildMetric(String label, String value, IconData icon) {
    return Row(
      children: [
        Icon(icon, size: 20, color: AppColors.brand400),
        const SizedBox(width: 8),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(value, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: Colors.white)),
            Text(label, style: const TextStyle(fontSize: 11, color: AppColors.slate400)),
          ],
        ),
      ],
    );
  }
}
