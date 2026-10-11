import 'package:flutter/material.dart';

import '../../../core/network/api_client.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_spacing.dart';
import '../../../core/widgets/custom_button.dart';
import '../../../core/widgets/custom_text_field.dart';
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

  final _bankNameController = TextEditingController();
  final _accountNameController = TextEditingController();
  final _accountNumberController = TextEditingController();
  final _ibanController = TextEditingController();
  final _swiftController = TextEditingController();
  final _branchController = TextEditingController();

  bool _codEnabled = true;
  bool _bankTransferEnabled = true;

  @override
  void initState() {
    super.initState();
    _fetchPaymentSettings();
  }

  @override
  void dispose() {
    _bankNameController.dispose();
    _accountNameController.dispose();
    _accountNumberController.dispose();
    _ibanController.dispose();
    _swiftController.dispose();
    _branchController.dispose();
    super.dispose();
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
          _bankNameController.text = s['bank_name']?.toString() ?? '';
          _accountNameController.text = s['account_name']?.toString() ?? '';
          _accountNumberController.text = s['account_number']?.toString() ?? '';
          _ibanController.text = s['iban']?.toString() ?? '';
          _swiftController.text = s['swift_code']?.toString() ?? '';
          _branchController.text = s['bank_branch']?.toString() ?? '';
          _codEnabled = s['cod_enabled'] == true;
          _bankTransferEnabled = s['manual_bank_enabled'] != false;
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
      await _apiClient.post(
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
        },
      );

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Payment settings saved successfully!'), backgroundColor: AppColors.success),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to save settings: $e'), backgroundColor: AppColors.danger),
        );
      }
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.surface,
      appBar: AppBar(title: const Text('Payment & Payout Settings')),
      body: _isLoading
          ? const LoadingView(message: 'Loading payment settings...')
          : SingleChildScrollView(
              padding: const EdgeInsets.all(AppSpacing.lg),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(AppSpacing.sm),
                        decoration: BoxDecoration(color: AppColors.brand50, borderRadius: BorderRadius.circular(AppRadius.sm)),
                        child: const Icon(Icons.account_balance_rounded, size: 22, color: AppColors.brand600),
                      ),
                      const SizedBox(width: AppSpacing.md),
                      const Expanded(
                        child: Text('Bank Account for Payouts', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.ink)),
                      ),
                    ],
                  ),
                  const SizedBox(height: AppSpacing.md),
                  CustomTextField(
                    controller: _bankNameController,
                    label: 'Bank Name',
                    hint: 'e.g. Qatar National Bank (QNB)',
                  ),
                  const SizedBox(height: AppSpacing.md),
                  CustomTextField(
                    controller: _accountNameController,
                    label: 'Account Holder Name',
                    hint: 'e.g. John Doe / My Store Trading',
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
                    hint: 'QA00 QNBA 0000 0000 0000 0000 00',
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
                  const SizedBox(height: AppSpacing.xl),
                  const Text('Payment Methods', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.ink)),
                  const SizedBox(height: AppSpacing.sm),
                  SwitchListTile(
                    contentPadding: EdgeInsets.zero,
                    title: const Text('Direct Bank Transfer Payouts', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                    subtitle: const Text('Receive earnings directly into your registered bank account', style: TextStyle(fontSize: 12, color: AppColors.slate500)),
                    value: _bankTransferEnabled,
                    activeColor: AppColors.brand500,
                    onChanged: (val) => setState(() => _bankTransferEnabled = val),
                  ),
                  const Divider(height: 1, color: AppColors.slate100),
                  SwitchListTile(
                    contentPadding: EdgeInsets.zero,
                    title: const Text('Cash on Delivery (COD) Acceptance', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                    subtitle: const Text('Allow local customers to pay upon package receipt', style: TextStyle(fontSize: 12, color: AppColors.slate500)),
                    value: _codEnabled,
                    activeColor: AppColors.brand500,
                    onChanged: (val) => setState(() => _codEnabled = val),
                  ),
                  const SizedBox(height: AppSpacing.xxl),

                  CustomButton(
                    text: 'Save Payment Settings',
                    isLoading: _isSaving,
                    onPressed: _saveSettings,
                  ),
                ],
              ),
            ),
    );
  }
}
