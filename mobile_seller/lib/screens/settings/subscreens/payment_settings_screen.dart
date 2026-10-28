import 'package:flutter/material.dart';

import '../../../core/network/api_client.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_spacing.dart';
import '../../../core/widgets/app_snackbar.dart';
import '../../../core/widgets/custom_button.dart';
import '../../../core/widgets/custom_text_field.dart';
import '../../../core/widgets/elegant_switch.dart';
import '../../../core/widgets/loading_view.dart';

class PaymentSettingsScreen extends StatefulWidget {
  const PaymentSettingsScreen({super.key});

  @override
  State<PaymentSettingsScreen> createState() => _PaymentSettingsScreenState();
}

class _PaymentSettingsScreenState extends State<PaymentSettingsScreen> {
  final ApiClient _apiClient = ApiClient();
  bool _isLoading = true;
  bool _isSaving = false;
  String? _errorMessage;

  // Stripe Merchant Keys
  final _stripePublishableController = TextEditingController();
  final _stripeSecretController = TextEditingController();
  bool _stripeEnabled = false;
  bool _hasStripeConnected = false;
  bool _obscureStripeSecret = true;
  bool _isDisconnectingStripe = false;

  // PayPal Merchant Keys
  final _paypalClientIdController = TextEditingController();
  final _paypalSecretController = TextEditingController();
  bool _paypalEnabled = false;
  bool _hasPaypalConnected = false;
  bool _obscurePaypalSecret = true;
  bool _isDisconnectingPaypal = false;

  // Accepted Checkout Methods
  bool _codEnabled = true;
  bool _bankTransferEnabled = true;

  // Bank Account for Payouts
  final _bankNameController = TextEditingController();
  final _accountNameController = TextEditingController();
  final _accountNumberController = TextEditingController();
  final _ibanController = TextEditingController();
  final _swiftController = TextEditingController();
  final _branchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _fetchPaymentSettings();
  }

  @override
  void dispose() {
    _stripePublishableController.dispose();
    _stripeSecretController.dispose();
    _paypalClientIdController.dispose();
    _paypalSecretController.dispose();
    _bankNameController.dispose();
    _accountNameController.dispose();
    _accountNumberController.dispose();
    _ibanController.dispose();
    _swiftController.dispose();
    _branchController.dispose();
    super.dispose();
  }

  void _applySettingsData(Map<String, dynamic> s) {
    _bankNameController.text = s['bank_name']?.toString() ?? '';
    _accountNameController.text = s['account_name']?.toString() ?? '';
    _accountNumberController.text = s['account_number']?.toString() ?? '';
    _ibanController.text = s['iban']?.toString() ?? '';
    _swiftController.text = s['swift_code']?.toString() ?? '';
    _branchController.text = s['bank_branch']?.toString() ?? '';
    _codEnabled = s['cod_enabled'] == true;
    _bankTransferEnabled = s['manual_bank_enabled'] != false;

    _stripeEnabled = s['stripe_enabled'] == true;
    _stripePublishableController.text = s['stripe_publishable_key']?.toString() ?? '';
    _stripeSecretController.text = s['stripe_secret_key']?.toString() ?? '';
    _hasStripeConnected = s['has_stripe_connected'] == true;

    _paypalEnabled = s['paypal_enabled'] == true;
    _paypalClientIdController.text = s['paypal_client_id']?.toString() ?? '';
    _paypalSecretController.text = s['paypal_client_secret']?.toString() ?? '';
    _hasPaypalConnected = s['has_paypal_connected'] == true;
  }

  Future<void> _fetchPaymentSettings() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final response = await _apiClient.get('/payment-settings');
      if (mounted && response is Map<String, dynamic>) {
        final s = (response['settings'] as Map<String, dynamic>?) ?? {};
        setState(() {
          _applySettingsData(s);
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

  Future<void> _saveSettings() async {
    setState(() => _isSaving = true);

    try {
      final response = await _apiClient.post(
        '/payment-settings',
        body: {
          'bank_name': _bankNameController.text.trim(),
          'account_name': _accountNameController.text.trim(),
          'account_number': _accountNumberController.text.trim(),
          'iban': _ibanController.text.trim(),
          'swift_code': _swiftController.text.trim(),
          'bank_branch': _branchController.text.trim(),
          'cod_enabled': _codEnabled,
          'manual_bank_enabled': _bankTransferEnabled,
          'stripe_enabled': _stripeEnabled,
          'stripe_publishable_key': _stripePublishableController.text.trim(),
          'stripe_secret_key': _stripeSecretController.text.trim(),
          'paypal_enabled': _paypalEnabled,
          'paypal_client_id': _paypalClientIdController.text.trim(),
          'paypal_client_secret': _paypalSecretController.text.trim(),
        },
      );

      if (mounted) {
        if (response is Map<String, dynamic> && response['settings'] is Map<String, dynamic>) {
          setState(() {
            _applySettingsData(response['settings'] as Map<String, dynamic>);
          });
        }
        AppSnackBar.success(context, 'Payment settings & credentials saved successfully!');
      }
    } catch (e) {
      if (mounted) {
        AppSnackBar.error(context, 'Failed to save settings: $e');
      }
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  Future<void> _disconnectStripe() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Disconnect Stripe?'),
        content: const Text(
          'Are you sure you want to disconnect Stripe? Buyers will not be able to pay with credit/debit card directly to your Stripe account.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.danger),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Disconnect', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    setState(() => _isDisconnectingStripe = true);
    try {
      final response = await _apiClient.post(
        '/payment-settings',
        body: {'action': 'disconnect_stripe'},
      );

      if (mounted) {
        if (response is Map<String, dynamic> && response['settings'] is Map<String, dynamic>) {
          setState(() {
            _applySettingsData(response['settings'] as Map<String, dynamic>);
          });
        } else {
          setState(() {
            _stripePublishableController.clear();
            _stripeSecretController.clear();
            _stripeEnabled = false;
            _hasStripeConnected = false;
          });
        }
        AppSnackBar.success(context, 'Stripe merchant account disconnected.');
      }
    } catch (e) {
      if (mounted) {
        AppSnackBar.error(context, 'Failed to disconnect Stripe: $e');
      }
    } finally {
      if (mounted) setState(() => _isDisconnectingStripe = false);
    }
  }

  Future<void> _disconnectPaypal() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Disconnect PayPal?'),
        content: const Text(
          'Are you sure you want to disconnect PayPal? Buyers will not be able to pay directly into your PayPal account.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.danger),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Disconnect', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    setState(() => _isDisconnectingPaypal = true);
    try {
      final response = await _apiClient.post(
        '/payment-settings',
        body: {'action': 'disconnect_paypal'},
      );

      if (mounted) {
        if (response is Map<String, dynamic> && response['settings'] is Map<String, dynamic>) {
          setState(() {
            _applySettingsData(response['settings'] as Map<String, dynamic>);
          });
        } else {
          setState(() {
            _paypalClientIdController.clear();
            _paypalSecretController.clear();
            _paypalEnabled = false;
            _hasPaypalConnected = false;
          });
        }
        AppSnackBar.success(context, 'PayPal merchant account disconnected.');
      }
    } catch (e) {
      if (mounted) {
        AppSnackBar.error(context, 'Failed to disconnect PayPal: $e');
      }
    } finally {
      if (mounted) setState(() => _isDisconnectingPaypal = false);
    }
  }

  Widget _buildStatusBadge({required bool isConnected, required bool isEnabled}) {
    if (isConnected && isEnabled) {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
        decoration: BoxDecoration(
          color: AppColors.successBg,
          borderRadius: BorderRadius.circular(AppRadius.pill),
          border: Border.all(color: AppColors.success.withValues(alpha: 0.3)),
        ),
        child: const Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.check_circle_rounded, size: 12, color: AppColors.success),
            SizedBox(width: 4),
            Text(
              'Connected',
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w700,
                color: AppColors.success,
              ),
            ),
          ],
        ),
      );
    } else if (isConnected) {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
        decoration: BoxDecoration(
          color: AppColors.warningBg,
          borderRadius: BorderRadius.circular(AppRadius.pill),
          border: Border.all(color: AppColors.warning.withValues(alpha: 0.3)),
        ),
        child: const Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.pause_circle_outline_rounded, size: 12, color: AppColors.warning),
            SizedBox(width: 4),
            Text(
              'Keys Saved (Disabled)',
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w700,
                color: AppColors.warning,
              ),
            ),
          ],
        ),
      );
    } else {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
        decoration: BoxDecoration(
          color: AppColors.slate100,
          borderRadius: BorderRadius.circular(AppRadius.pill),
          border: Border.all(color: AppColors.slate300),
        ),
        child: const Text(
          'Not Connected',
          style: TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w600,
            color: AppColors.slate600,
          ),
        ),
      );
    }
  }

  Widget _buildStripeCard() {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.slate200),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Row
          Row(
            children: [
              Container(
                width: 42,
                height: 42,
                decoration: BoxDecoration(
                  color: AppColors.brand50,
                  borderRadius: BorderRadius.circular(AppRadius.md),
                ),
                child: const Icon(
                  Icons.credit_card_rounded,
                  color: AppColors.brand600,
                  size: 24,
                ),
              ),
              const SizedBox(width: AppSpacing.md),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Stripe Merchant Account',
                      style: TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w700,
                        color: AppColors.ink,
                      ),
                    ),
                    const SizedBox(height: 2),
                    const Text(
                      'Direct Credit / Debit Card Checkout',
                      style: TextStyle(
                        fontSize: 12,
                        color: AppColors.slate500,
                      ),
                    ),
                  ],
                ),
              ),
              _buildStatusBadge(
                isConnected: _hasStripeConnected,
                isEnabled: _stripeEnabled,
              ),
            ],
          ),

          const SizedBox(height: AppSpacing.md),
          const Divider(height: 1, color: AppColors.slate100),
          const SizedBox(height: AppSpacing.md),

          // Enable toggle
          ElegantSwitchTile(
            dense: true,
            title: 'Enable Stripe for your listings',
            subtitle: 'Route card payments directly to your Stripe merchant account',
            value: _stripeEnabled,
            onChanged: (val) {
              setState(() => _stripeEnabled = val);
            },
          ),

          const SizedBox(height: AppSpacing.sm),

          // Keys inputs
          CustomTextField(
            controller: _stripePublishableController,
            label: 'Stripe Publishable Key',
            hint: 'pk_live_... or pk_test_...',
            prefixIcon: const Icon(Icons.vpn_key_outlined, size: 18, color: AppColors.brand600),
            onChanged: (val) {
              setState(() {
                _hasStripeConnected = val.trim().isNotEmpty && _stripeSecretController.text.trim().isNotEmpty;
              });
            },
          ),
          const SizedBox(height: AppSpacing.md),
          CustomTextField(
            controller: _stripeSecretController,
            label: 'Stripe Secret Key',
            hint: 'sk_live_... or sk_test_...',
            obscureText: _obscureStripeSecret,
            prefixIcon: const Icon(Icons.lock_outline_rounded, size: 18, color: AppColors.brand600),
            suffixIcon: IconButton(
              icon: Icon(
                _obscureStripeSecret ? Icons.visibility_rounded : Icons.visibility_off_rounded,
                size: 18,
                color: AppColors.slate400,
              ),
              onPressed: () => setState(() => _obscureStripeSecret = !_obscureStripeSecret),
            ),
            onChanged: (val) {
              setState(() {
                _hasStripeConnected = val.trim().isNotEmpty && _stripePublishableController.text.trim().isNotEmpty;
              });
            },
          ),

          const SizedBox(height: AppSpacing.xs),
          const Text(
            'Obtain your API keys from dashboard.stripe.com/apikeys',
            style: TextStyle(fontSize: 11, color: AppColors.slate400),
          ),

          if (_hasStripeConnected) ...[
            const SizedBox(height: AppSpacing.md),
            Align(
              alignment: Alignment.centerRight,
              child: TextButton.icon(
                onPressed: _isDisconnectingStripe ? null : _disconnectStripe,
                icon: _isDisconnectingStripe
                    ? const SizedBox(
                        width: 14,
                        height: 14,
                        child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.danger),
                      )
                    : const Icon(Icons.link_off_rounded, size: 16, color: AppColors.danger),
                label: const Text(
                  'Disconnect Stripe',
                  style: TextStyle(
                    color: AppColors.danger,
                    fontSize: 12.5,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildPaypalCard() {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.slate200),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Row
          Row(
            children: [
              Container(
                width: 42,
                height: 42,
                decoration: BoxDecoration(
                  color: AppColors.brand50,
                  borderRadius: BorderRadius.circular(AppRadius.md),
                ),
                child: const Icon(
                  Icons.account_balance_wallet_rounded,
                  color: AppColors.brand600,
                  size: 24,
                ),
              ),
              const SizedBox(width: AppSpacing.md),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'PayPal Merchant Account',
                      style: TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w700,
                        color: AppColors.ink,
                      ),
                    ),
                    const SizedBox(height: 2),
                    const Text(
                      'PayPal Wallet & Express Checkout',
                      style: TextStyle(
                        fontSize: 12,
                        color: AppColors.slate500,
                      ),
                    ),
                  ],
                ),
              ),
              _buildStatusBadge(
                isConnected: _hasPaypalConnected,
                isEnabled: _paypalEnabled,
              ),
            ],
          ),

          const SizedBox(height: AppSpacing.md),
          const Divider(height: 1, color: AppColors.slate100),
          const SizedBox(height: AppSpacing.md),

          // Enable toggle
          ElegantSwitchTile(
            dense: true,
            title: 'Enable PayPal for your listings',
            subtitle: 'Allow buyers to checkout directly into your PayPal account',
            value: _paypalEnabled,
            onChanged: (val) {
              setState(() => _paypalEnabled = val);
            },
          ),

          const SizedBox(height: AppSpacing.sm),

          // Keys inputs
          CustomTextField(
            controller: _paypalClientIdController,
            label: 'PayPal Client ID',
            hint: 'Client ID from PayPal Developer Portal',
            prefixIcon: const Icon(Icons.badge_outlined, size: 18, color: AppColors.brand600),
            onChanged: (val) {
              setState(() {
                _hasPaypalConnected = val.trim().isNotEmpty && _paypalSecretController.text.trim().isNotEmpty;
              });
            },
          ),
          const SizedBox(height: AppSpacing.md),
          CustomTextField(
            controller: _paypalSecretController,
            label: 'PayPal Client Secret',
            hint: 'Client Secret from PayPal Developer Portal',
            obscureText: _obscurePaypalSecret,
            prefixIcon: const Icon(Icons.lock_outline_rounded, size: 18, color: AppColors.brand600),
            suffixIcon: IconButton(
              icon: Icon(
                _obscurePaypalSecret ? Icons.visibility_rounded : Icons.visibility_off_rounded,
                size: 18,
                color: AppColors.slate400,
              ),
              onPressed: () => setState(() => _obscurePaypalSecret = !_obscurePaypalSecret),
            ),
            onChanged: (val) {
              setState(() {
                _hasPaypalConnected = val.trim().isNotEmpty && _paypalClientIdController.text.trim().isNotEmpty;
              });
            },
          ),

          const SizedBox(height: AppSpacing.xs),
          const Text(
            'Obtain your REST API keys from developer.paypal.com/dashboard/applications',
            style: TextStyle(fontSize: 11, color: AppColors.slate400),
          ),

          if (_hasPaypalConnected) ...[
            const SizedBox(height: AppSpacing.md),
            Align(
              alignment: Alignment.centerRight,
              child: TextButton.icon(
                onPressed: _isDisconnectingPaypal ? null : _disconnectPaypal,
                icon: _isDisconnectingPaypal
                    ? const SizedBox(
                        width: 14,
                        height: 14,
                        child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.danger),
                      )
                    : const Icon(Icons.link_off_rounded, size: 16, color: AppColors.danger),
                label: const Text(
                  'Disconnect PayPal',
                  style: TextStyle(
                    color: AppColors.danger,
                    fontSize: 12.5,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildAcceptedPaymentMethodsCard() {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.slate200),
      ),
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Accepted Checkout Payment Methods',
            style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.ink),
          ),
          const SizedBox(height: 2),
          const Text(
            'Toggle which payment methods buyers can select at checkout for your listings.',
            style: TextStyle(fontSize: 12, color: AppColors.slate500),
          ),
          const SizedBox(height: AppSpacing.md),
          const Divider(height: 1, color: AppColors.slate100),

          // Stripe Switch
          ElegantSwitchTile(
            title: 'Stripe Card Checkout',
            subtitle: 'Allow buyers to pay via Stripe card gateway',
            value: _stripeEnabled,
            onChanged: (val) => setState(() => _stripeEnabled = val),
          ),
          const Divider(height: 1, color: AppColors.slate100),

          // PayPal Switch
          ElegantSwitchTile(
            title: 'PayPal Express Checkout',
            subtitle: 'Allow buyers to pay via PayPal wallet',
            value: _paypalEnabled,
            onChanged: (val) => setState(() => _paypalEnabled = val),
          ),
          const Divider(height: 1, color: AppColors.slate100),

          // COD Switch
          ElegantSwitchTile(
            title: 'Cash on Delivery (COD)',
            subtitle: 'Collect cash payment upon product delivery',
            value: _codEnabled,
            onChanged: (val) => setState(() => _codEnabled = val),
          ),
          const Divider(height: 1, color: AppColors.slate100),

          // Manual Bank Transfer Switch
          ElegantSwitchTile(
            title: 'Direct Bank Transfer',
            subtitle: 'Direct wire deposit into your registered bank account',
            value: _bankTransferEnabled,
            onChanged: (val) => setState(() => _bankTransferEnabled = val),
          ),
        ],
      ),
    );
  }

  Widget _buildBankDetailsCard() {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.slate200),
      ),
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(AppSpacing.sm),
                decoration: BoxDecoration(
                  color: AppColors.brand50,
                  borderRadius: BorderRadius.circular(AppRadius.sm),
                ),
                child: const Icon(Icons.account_balance_rounded, size: 22, color: AppColors.brand600),
              ),
              const SizedBox(width: AppSpacing.md),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Your Bank Transfer Details',
                      style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.ink),
                    ),
                    SizedBox(height: 2),
                    Text(
                      'For customer manual bank transfers & platform earnings payout',
                      style: TextStyle(fontSize: 11.5, color: AppColors.slate500),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.lg),

          CustomTextField(
            controller: _bankNameController,
            label: 'Bank Name',
            hint: 'e.g. Qatar National Bank (QNB), Chase, HSBC',
          ),
          const SizedBox(height: AppSpacing.md),
          CustomTextField(
            controller: _accountNameController,
            label: 'Account Holder Name',
            hint: 'e.g. John Doe / My Store Trading LLC',
          ),
          const SizedBox(height: AppSpacing.md),
          CustomTextField(
            controller: _accountNumberController,
            label: 'Account Number',
            hint: 'e.g. 00123456789',
          ),
          const SizedBox(height: AppSpacing.md),
          CustomTextField(
            controller: _ibanController,
            label: 'IBAN Number',
            hint: 'e.g. QA00 QNBA 0000 0000 0000 0000 00',
          ),
          const SizedBox(height: AppSpacing.md),
          Row(
            children: [
              Expanded(
                child: CustomTextField(
                  controller: _swiftController,
                  label: 'SWIFT / BIC Code',
                  hint: 'e.g. QNBAQAQA',
                ),
              ),
              const SizedBox(width: AppSpacing.md),
              Expanded(
                child: CustomTextField(
                  controller: _branchController,
                  label: 'Branch Name',
                  hint: 'e.g. Doha Main Branch',
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        title: const Text('Payment & Payout Settings'),
      ),
      body: _isLoading
          ? const LoadingView(message: 'Loading payment settings...')
          : _errorMessage != null &&
                  _bankNameController.text.isEmpty &&
                  _stripePublishableController.text.isEmpty
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
                        ElevatedButton(onPressed: _fetchPaymentSettings, child: const Text('Retry')),
                      ],
                    ),
                  ),
                )
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(AppSpacing.lg),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Header Info Banner
                      Container(
                        padding: const EdgeInsets.all(AppSpacing.md),
                        decoration: BoxDecoration(
                          color: AppColors.brand50,
                          borderRadius: BorderRadius.circular(AppRadius.md),
                          border: Border.all(color: AppColors.brand200),
                        ),
                        child: const Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Icon(Icons.info_outline_rounded, size: 20, color: AppColors.brand700),
                            SizedBox(width: AppSpacing.sm),
                            Expanded(
                              child: Text(
                                'Connect your merchant API keys so customer payments for your listings route directly into your Stripe or PayPal account.',
                                style: TextStyle(
                                  fontSize: 12.5,
                                  color: AppColors.brand900,
                                  height: 1.4,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),

                      const SizedBox(height: AppSpacing.lg),

                      // 1. Stripe Card
                      _buildStripeCard(),

                      const SizedBox(height: AppSpacing.lg),

                      // 2. PayPal Card
                      _buildPaypalCard(),

                      const SizedBox(height: AppSpacing.lg),

                      // 3. Accepted Checkout Payment Methods
                      _buildAcceptedPaymentMethodsCard(),

                      const SizedBox(height: AppSpacing.lg),

                      // 4. Bank Account Details
                      _buildBankDetailsCard(),

                      const SizedBox(height: AppSpacing.xxl),

                      // Save Button
                      CustomButton(
                        text: 'Save Payment Settings',
                        isLoading: _isSaving,
                        onPressed: _saveSettings,
                      ),

                      const SizedBox(height: AppSpacing.xl),
                    ],
                  ),
                ),
    );
  }
}
