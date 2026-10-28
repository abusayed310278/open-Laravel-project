import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../../core/network/api_client.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_spacing.dart';
import '../../../core/widgets/app_snackbar.dart';
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
          : _errorMessage != null && _plans.isEmpty && _currentPlan == null
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
                        ElevatedButton(onPressed: _fetchSubscriptions, child: const Text('Retry')),
                      ],
                    ),
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _fetchSubscriptions,
                  color: AppColors.brand500,
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(AppSpacing.lg),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Active Plan Card
                    Container(
                      padding: const EdgeInsets.all(AppSpacing.xl),
                      decoration: BoxDecoration(
                        color: AppColors.surface,
                        borderRadius: BorderRadius.circular(AppRadius.lg),
                        border: Border.all(color: AppColors.slate200),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                _currentPlan?['name']?.toString() ?? 'Active Plan',
                                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.ink),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                decoration: BoxDecoration(
                                  color: AppColors.successBg,
                                  borderRadius: BorderRadius.circular(AppRadius.pill),
                                ),
                                child: Text(
                                  (_currentPlan?['status'] ?? 'Active').toString().toUpperCase(),
                                  style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: AppColors.success),
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
                          const Divider(height: 28, color: AppColors.slate100),
                          Text(
                            'Renews on: ${_currentPlan?['renews_at'] ?? 'Continuous'}',
                            style: const TextStyle(fontSize: 12, color: AppColors.slate500),
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
                                onPressed: isCurrent ? null : () => _showSubscriptionCheckoutSheet(plan),
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

  void _showSubscriptionCheckoutSheet(Map<String, dynamic> plan) {
    final price = (plan['price'] as num?)?.toDouble() ?? 0.0;
    final planId = plan['id'];
    final planName = plan['name']?.toString() ?? 'Tier';
    final interval = plan['interval']?.toString() ?? 'monthly';

    final cardNumberController = TextEditingController();
    final expiryController = TextEditingController();
    final cvcController = TextEditingController();
    final cardholderController = TextEditingController(text: 'Openbox Seller');
    final formKey = GlobalKey<FormState>();

    bool isSubmitting = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (bottomSheetContext) => StatefulBuilder(
        builder: (ctx, setSheetState) {
          final brand = _getCardBrand(cardNumberController.text);
          final cardNumberDisplay = cardNumberController.text.isEmpty ? '•••• •••• •••• ••••' : cardNumberController.text;
          final cardholderDisplay = cardholderController.text.isEmpty ? 'OPENBOX SELLER' : cardholderController.text.toUpperCase();
          final expiryDisplay = expiryController.text.isEmpty ? 'MM/YY' : expiryController.text;

          void fillTestCard() {
            setSheetState(() {
              cardNumberController.text = '4242 4242 4242 4242';
              expiryController.text = '12/28';
              cvcController.text = '123';
              cardholderController.text = 'Openbox Seller';
            });
          }

          Future<void> submitSubscription() async {
            if (price > 0) {
              final raw = cardNumberController.text.replaceAll(' ', '').trim();
              if (raw.isEmpty) {
                AppSnackBar.error(context, 'Please enter card details or tap "Fill Test Card"');
                return;
              }
              if (!formKey.currentState!.validate()) {
                return;
              }
            }

            setSheetState(() => isSubmitting = true);

            try {
              final body = <String, dynamic>{
                'plan_id': planId,
                'payment_method': 'stripe',
              };

              if (price > 0) {
                final expiryParts = expiryController.text.split('/');
                final expMonth = expiryParts[0].trim();
                final rawYear = expiryParts.length > 1 ? expiryParts[1].trim() : '28';
                final expYear = rawYear.length == 2 ? '20$rawYear' : rawYear;

                body['card_number'] = cardNumberController.text.replaceAll(' ', '').trim();
                body['exp_month'] = expMonth;
                body['exp_year'] = expYear;
                body['cvc'] = cvcController.text.trim();
                body['cardholder_name'] = cardholderController.text.trim();
              }

              final response = await _apiClient.post('/subscriptions/purchase', body: body);

              if (!ctx.mounted) return;
              Navigator.of(ctx).pop();

              if (!mounted) return;
              final msg = (response is Map<String, dynamic> && response['message'] != null)
                  ? response['message'].toString()
                  : 'Successfully subscribed to $planName!';

              AppSnackBar.success(context, msg);
              _fetchSubscriptions();
            } catch (e) {
              if (ctx.mounted) {
                setSheetState(() => isSubmitting = false);
                AppSnackBar.error(ctx, e.toString());
              }
            }
          }

          return Container(
            decoration: const BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.vertical(top: Radius.circular(AppRadius.xl)),
            ),
            padding: EdgeInsets.only(
              left: AppSpacing.lg,
              right: AppSpacing.lg,
              top: AppSpacing.lg,
              bottom: MediaQuery.of(ctx).viewInsets.bottom + AppSpacing.lg,
            ),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Header
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('Upgrade to $planName', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.ink)),
                          const SizedBox(height: 2),
                          Text(
                            price > 0 ? '$price QAR / $interval • Billed via Platform Stripe' : 'Free Tier',
                            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF635BFF)),
                          ),
                        ],
                      ),
                      IconButton(
                        onPressed: () => Navigator.of(ctx).pop(),
                        icon: const Icon(Icons.close_rounded, size: 22, color: AppColors.slate400),
                      ),
                    ],
                  ),
                  const Divider(color: AppColors.slate200, height: 24),

                  if (price > 0) ...[
                    // Virtual Card
                    Container(
                      width: double.infinity,
                      height: 165,
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [Color(0xFF1E293B), Color(0xFF0F172A), Color(0xFF312E81)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: Colors.white.withValues(alpha: 0.12)),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.25),
                            blurRadius: 10,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Row(
                                children: [
                                  Container(
                                    width: 36,
                                    height: 26,
                                    decoration: BoxDecoration(
                                      gradient: const LinearGradient(
                                        colors: [Color(0xFFFFD700), Color(0xFFD4AF37), Color(0xFFB8860B)],
                                      ),
                                      borderRadius: BorderRadius.circular(4),
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  const Icon(Icons.wifi, color: Colors.white70, size: 18),
                                ],
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                                decoration: BoxDecoration(
                                  color: Colors.white.withValues(alpha: 0.15),
                                  borderRadius: BorderRadius.circular(4),
                                ),
                                child: Text(
                                  brand,
                                  style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w900, letterSpacing: 0.8),
                                ),
                              ),
                            ],
                          ),
                          Padding(
                            padding: const EdgeInsets.symmetric(vertical: 2),
                            child: Text(
                              cardNumberDisplay,
                              style: const TextStyle(color: Colors.white, fontSize: 15.5, fontWeight: FontWeight.w700, letterSpacing: 2.0, fontFamily: 'monospace'),
                            ),
                          ),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text('CARDHOLDER', style: TextStyle(color: Colors.white60, fontSize: 8, fontWeight: FontWeight.w600)),
                                    Text(cardholderDisplay, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700)),
                                  ],
                                ),
                              ),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.end,
                                children: [
                                  const Text('EXPIRES', style: TextStyle(color: Colors.white60, fontSize: 8, fontWeight: FontWeight.w600)),
                                  Text(expiryDisplay, style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700)),
                                ],
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: AppSpacing.sm),

                    // Fill Test Card Button
                    Align(
                      alignment: Alignment.centerRight,
                      child: TextButton.icon(
                        onPressed: fillTestCard,
                        icon: const Icon(Icons.bolt_rounded, size: 16, color: Color(0xFF635BFF)),
                        label: const Text('Fill Stripe Test Card (4242)', style: TextStyle(color: Color(0xFF635BFF), fontSize: 12, fontWeight: FontWeight.w700)),
                        style: TextButton.styleFrom(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          backgroundColor: const Color(0xFF635BFF).withValues(alpha: 0.08),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
                        ),
                      ),
                    ),
                    const SizedBox(height: AppSpacing.sm),

                    // Card Form
                    Form(
                      key: formKey,
                      child: Column(
                        children: [
                          TextFormField(
                            controller: cardNumberController,
                            keyboardType: TextInputType.number,
                            inputFormatters: [
                              FilteringTextInputFormatter.digitsOnly,
                              LengthLimitingTextInputFormatter(16),
                              _CardNumberInputFormatter(),
                            ],
                            onChanged: (_) => setSheetState(() {}),
                            decoration: InputDecoration(
                              labelText: 'Card Number *',
                              hintText: '4242 4242 4242 4242',
                              prefixIcon: const Icon(Icons.credit_card_rounded, size: 20),
                              suffixIcon: _buildCardBrandIcon(brand),
                            ),
                            validator: (v) {
                              if (v == null || v.replaceAll(' ', '').length < 15) {
                                return 'Enter a valid 15-16 digit card number';
                              }
                              return null;
                            },
                          ),
                          const SizedBox(height: AppSpacing.md),
                          Row(
                            children: [
                              Expanded(
                                child: TextFormField(
                                  controller: expiryController,
                                  keyboardType: TextInputType.number,
                                  inputFormatters: [
                                    FilteringTextInputFormatter.digitsOnly,
                                    LengthLimitingTextInputFormatter(4),
                                    _CardExpiryInputFormatter(),
                                  ],
                                  onChanged: (_) => setSheetState(() {}),
                                  decoration: const InputDecoration(
                                    labelText: 'Expiry *',
                                    hintText: 'MM/YY',
                                    prefixIcon: Icon(Icons.calendar_today_outlined, size: 18),
                                  ),
                                  validator: (v) {
                                    if (v == null || !RegExp(r'^\d{2}/\d{2}$').hasMatch(v)) {
                                      return 'MM/YY';
                                    }
                                    return null;
                                  },
                                ),
                              ),
                              const SizedBox(width: AppSpacing.md),
                              Expanded(
                                child: TextFormField(
                                  controller: cvcController,
                                  keyboardType: TextInputType.number,
                                  obscureText: true,
                                  inputFormatters: [
                                    FilteringTextInputFormatter.digitsOnly,
                                    LengthLimitingTextInputFormatter(4),
                                  ],
                                  decoration: const InputDecoration(
                                    labelText: 'CVC *',
                                    hintText: '123',
                                    prefixIcon: Icon(Icons.lock_outline, size: 18),
                                  ),
                                  validator: (v) => (v == null || v.trim().length < 3) ? '3-4 digits' : null,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: AppSpacing.md),
                          TextFormField(
                            controller: cardholderController,
                            textCapitalization: TextCapitalization.words,
                            onChanged: (_) => setSheetState(() {}),
                            decoration: const InputDecoration(
                              labelText: 'Cardholder Name *',
                              hintText: 'Name on card',
                              prefixIcon: Icon(Icons.person_outline, size: 18),
                            ),
                            validator: (v) => (v == null || v.trim().isEmpty) ? 'Please enter name' : null,
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: AppSpacing.md),

                    // Security Banner
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                      decoration: BoxDecoration(
                        color: AppColors.slate50,
                        borderRadius: BorderRadius.circular(AppRadius.sm),
                        border: Border.all(color: AppColors.slate200),
                      ),
                      child: const Row(
                        children: [
                          Icon(Icons.shield_outlined, size: 16, color: Color(0xFF059669)),
                          SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              'Charged to Admin Stripe Master account • 256-bit SSL encrypted',
                              style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w600, color: AppColors.slate600),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ] else ...[
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: AppSpacing.md),
                      child: Text(
                        'This plan is free. Confirm below to activate it immediately on your account.',
                        style: TextStyle(fontSize: 13, color: AppColors.slate600),
                      ),
                    ),
                  ],
                  const SizedBox(height: AppSpacing.xl),

                  // Submit Button
                  CustomButton(
                    text: price > 0 ? 'Pay $price QAR & Subscribe' : 'Activate Free Plan',
                    backgroundColor: const Color(0xFF635BFF),
                    isLoading: isSubmitting,
                    onPressed: isSubmitting ? null : submitSubscription,
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildCardBrandIcon(String brand) {
    Color color = AppColors.slate400;
    if (brand == 'VISA') color = const Color(0xFF1A1F71);
    if (brand == 'MASTERCARD') color = const Color(0xFFEB001B);
    if (brand == 'AMEX') color = const Color(0xFF006FCF);

    return Padding(
      padding: const EdgeInsets.only(right: 12),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(4),
              border: Border.all(color: color.withValues(alpha: 0.3)),
            ),
            child: Text(
              brand,
              style: TextStyle(
                color: color,
                fontSize: 10,
                fontWeight: FontWeight.w900,
              ),
            ),
          ),
        ],
      ),
    );
  }

  String _getCardBrand(String number) {
    final clean = number.replaceAll(RegExp(r'\D'), '');
    if (clean.startsWith('4')) return 'VISA';
    if (clean.startsWith(RegExp(r'^(5[1-5]|2[2-7])'))) return 'MASTERCARD';
    if (clean.startsWith(RegExp(r'^(34|37)'))) return 'AMEX';
    if (clean.startsWith('6011')) return 'DISCOVER';
    return 'CARD';
  }

  Widget _buildMetric(String label, String value, IconData icon) {
    return Row(
      children: [
        Icon(icon, size: 20, color: AppColors.brand600),
        const SizedBox(width: 8),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(value, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: AppColors.ink)),
            Text(label, style: const TextStyle(fontSize: 11, color: AppColors.slate500)),
          ],
        ),
      ],
    );
  }
}

class _CardNumberInputFormatter extends TextInputFormatter {
  @override
  TextEditingValue formatEditUpdate(TextEditingValue oldValue, TextEditingValue newValue) {
    final text = newValue.text.replaceAll(' ', '');
    final buffer = StringBuffer();
    for (int i = 0; i < text.length; i++) {
      if (i > 0 && i % 4 == 0) {
        buffer.write(' ');
      }
      buffer.write(text[i]);
    }
    final string = buffer.toString();
    return TextEditingValue(
      text: string,
      selection: TextSelection.collapsed(offset: string.length),
    );
  }
}

class _CardExpiryInputFormatter extends TextInputFormatter {
  @override
  TextEditingValue formatEditUpdate(TextEditingValue oldValue, TextEditingValue newValue) {
    var text = newValue.text.replaceAll('/', '');
    if (text.length > 4) text = text.substring(0, 4);
    final buffer = StringBuffer();
    for (int i = 0; i < text.length; i++) {
      if (i == 2) buffer.write('/');
      buffer.write(text[i]);
    }
    final string = buffer.toString();
    return TextEditingValue(
      text: string,
      selection: TextSelection.collapsed(offset: string.length),
    );
  }
}
