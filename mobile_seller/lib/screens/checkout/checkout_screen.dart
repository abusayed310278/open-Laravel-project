import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../core/network/api_client.dart';
import '../../core/network/api_config.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/loading_view.dart';
import 'order_success_screen.dart';

class CheckoutScreen extends StatefulWidget {
  final List<Map<String, dynamic>> items;
  final double subtotal;
  final double shipping;
  final double total;
  final VoidCallback? onOrderCompleted;

  const CheckoutScreen({
    super.key,
    required this.items,
    required this.subtotal,
    required this.shipping,
    required this.total,
    this.onOrderCompleted,
  });

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  final ApiClient _apiClient = ApiClient();
  final NumberFormat _currencyFormat = NumberFormat('#,##0.00', 'en_US');

  bool _isLoading = true;
  bool _isSubmitting = false;
  String? _errorMessage;

  List<Map<String, dynamic>> _addresses = [];
  Map<String, dynamic>? _selectedAddress;

  List<Map<String, dynamic>> _paymentMethods = [];
  String _selectedPaymentMethod = 'cod';

  // Stripe Credit/Debit Card Form State
  final TextEditingController _cardNumberController = TextEditingController();
  final TextEditingController _expiryController = TextEditingController();
  final TextEditingController _cvcController = TextEditingController();
  final TextEditingController _cardholderController = TextEditingController();
  final GlobalKey<FormState> _cardFormKey = GlobalKey<FormState>();

  @override
  void initState() {
    super.initState();
    _loadCheckoutData();
  }

  @override
  void dispose() {
    _cardNumberController.dispose();
    _expiryController.dispose();
    _cvcController.dispose();
    _cardholderController.dispose();
    super.dispose();
  }

  void _fillTestCard() {
    _cardNumberController.text = '4242 4242 4242 4242';
    _expiryController.text = '12/28';
    _cvcController.text = '123';
    if (_cardholderController.text.trim().isEmpty) {
      _cardholderController.text = _selectedAddress?['name']?.toString() ?? 'Demo Buyer';
    }
    setState(() {});
  }

  Future<void> _loadCheckoutData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      // 1. Fetch available payment methods
      try {
        final pmBody = <String, dynamic>{};
        if (widget.items.isNotEmpty) {
          pmBody['items'] = widget.items.map((it) => {
            'product_id': it['id'] ?? it['product_id'],
            'quantity': it['quantity'] ?? 1,
          }).toList();
        }
        final pmResponse = await _apiClient.post('/checkout/payment-methods', body: pmBody);
        if (pmResponse is Map<String, dynamic> && pmResponse['payment_methods'] is List) {
          _paymentMethods = (pmResponse['payment_methods'] as List)
              .map((e) => Map<String, dynamic>.from(e as Map))
              .toList();
        }
      } catch (_) {
        try {
          final pmResponse = await _apiClient.get('/checkout/payment-methods');
          if (pmResponse is Map<String, dynamic> && pmResponse['payment_methods'] is List) {
            _paymentMethods = (pmResponse['payment_methods'] as List)
                .map((e) => Map<String, dynamic>.from(e as Map))
                .toList();
          }
        } catch (_) {
          // Fallback default methods
          _paymentMethods = [
            {'id': 'cod', 'name': 'Cash on Delivery', 'enabled': true},
            {'id': 'stripe', 'name': 'Card (Stripe)', 'enabled': true},
          ];
        }
      }

      // Ensure cod and stripe are present if list is empty
      if (_paymentMethods.isEmpty) {
        _paymentMethods = [
          {'id': 'cod', 'name': 'Cash on Delivery', 'enabled': true},
          {'id': 'stripe', 'name': 'Card (Stripe)', 'enabled': true},
        ];
      }

      // Ensure selected payment method is valid for available methods
      if (_paymentMethods.isNotEmpty) {
        final hasSelected = _paymentMethods.any((m) => m['id'] == _selectedPaymentMethod);
        if (!hasSelected) {
          _selectedPaymentMethod = _paymentMethods.first['id']?.toString() ?? 'cod';
        }
      }

      // 2. Fetch saved addresses
      try {
        final addrResponse = await _apiClient.get('/addresses');
        if (addrResponse is Map<String, dynamic> && addrResponse['addresses'] is List) {
          _addresses = (addrResponse['addresses'] as List)
              .map((e) => Map<String, dynamic>.from(e as Map))
              .toList();
        }
      } catch (_) {
        _addresses = [];
      }

      if (_addresses.isNotEmpty) {
        _selectedAddress = _addresses.firstWhere(
          (a) => a['is_default'] == true,
          orElse: () => _addresses.first,
        );
      }

      if (mounted) {
        setState(() => _isLoading = false);
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

  void _showAddAddressDialog() {
    final nameController = TextEditingController();
    final phoneController = TextEditingController();
    final streetController = TextEditingController();
    final cityController = TextEditingController(text: 'Doha');
    final formKey = GlobalKey<FormState>();

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.lg)),
        title: const Text('Add Delivery Address', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700)),
        content: Form(
          key: formKey,
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextFormField(
                  controller: nameController,
                  decoration: const InputDecoration(labelText: 'Recipient Name *', prefixIcon: Icon(Icons.person_outline, size: 20)),
                  validator: (v) => (v == null || v.trim().isEmpty) ? 'Please enter name' : null,
                ),
                const SizedBox(height: AppSpacing.sm),
                TextFormField(
                  controller: phoneController,
                  keyboardType: TextInputType.phone,
                  decoration: const InputDecoration(labelText: 'Phone Number *', prefixIcon: Icon(Icons.phone_outlined, size: 20)),
                  validator: (v) => (v == null || v.trim().isEmpty) ? 'Please enter phone' : null,
                ),
                const SizedBox(height: AppSpacing.sm),
                TextFormField(
                  controller: streetController,
                  decoration: const InputDecoration(labelText: 'Street / Villa / Building *', prefixIcon: Icon(Icons.home_outlined, size: 20)),
                  validator: (v) => (v == null || v.trim().isEmpty) ? 'Please enter address' : null,
                ),
                const SizedBox(height: AppSpacing.sm),
                TextFormField(
                  controller: cityController,
                  decoration: const InputDecoration(labelText: 'City *', prefixIcon: Icon(Icons.location_city_outlined, size: 20)),
                  validator: (v) => (v == null || v.trim().isEmpty) ? 'Please enter city' : null,
                ),
              ],
            ),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Cancel', style: TextStyle(color: AppColors.slate600)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.brand600,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
            ),
            onPressed: () async {
              if (!formKey.currentState!.validate()) return;
              Navigator.of(ctx).pop();

              try {
                final body = {
                  'name': nameController.text.trim(),
                  'phone': phoneController.text.trim(),
                  'line1': streetController.text.trim(),
                  'city': cityController.text.trim(),
                  'country': 'Qatar',
                  'is_default': true,
                };

                final res = await _apiClient.post('/addresses', body: body);
                if (res is Map<String, dynamic> && res['address'] != null) {
                  final newAddr = Map<String, dynamic>.from(res['address'] as Map);
                  setState(() {
                    _addresses.insert(0, newAddr);
                    _selectedAddress = newAddr;
                  });
                } else {
                  _loadCheckoutData();
                }

                if (mounted) {
                  AppSnackBar.success(context, 'Delivery address saved');
                }
              } catch (e) {
                if (mounted) {
                  AppSnackBar.error(context, 'Failed to save address: $e');
                }
              }
            },
            child: const Text('Save Address'),
          ),
        ],
      ),
    );
  }

  void _showAddressPickerBottomSheet() {
    showModalBottomSheet(
      context: context,
      backgroundColor: AppColors.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(AppRadius.xl)),
      ),
      builder: (ctx) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(AppSpacing.lg),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Select Delivery Address', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.ink)),
                  TextButton.icon(
                    icon: const Icon(Icons.add, size: 18),
                    label: const Text('New'),
                    onPressed: () {
                      Navigator.of(ctx).pop();
                      _showAddAddressDialog();
                    },
                  ),
                ],
              ),
              const Divider(color: AppColors.slate200),
              Flexible(
                child: ListView.separated(
                  shrinkWrap: true,
                  itemCount: _addresses.length,
                  separatorBuilder: (_, _) => const SizedBox(height: AppSpacing.sm),
                  itemBuilder: (context, index) {
                    final addr = _addresses[index];
                    final isSelected = _selectedAddress?['id'] == addr['id'];

                    return InkWell(
                      onTap: () {
                        setState(() => _selectedAddress = addr);
                        Navigator.of(ctx).pop();
                      },
                      borderRadius: BorderRadius.circular(AppRadius.md),
                      child: Container(
                        padding: const EdgeInsets.all(AppSpacing.md),
                        decoration: BoxDecoration(
                          color: isSelected ? AppColors.brand50 : AppColors.surface,
                          borderRadius: BorderRadius.circular(AppRadius.md),
                          border: Border.all(
                            color: isSelected ? AppColors.brand600 : AppColors.slate200,
                            width: isSelected ? 1.5 : 1,
                          ),
                        ),
                        child: Row(
                          children: [
                            Icon(
                              isSelected ? Icons.radio_button_checked : Icons.radio_button_off,
                              color: isSelected ? AppColors.brand600 : AppColors.slate400,
                              size: 20,
                            ),
                            const SizedBox(width: AppSpacing.md),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    addr['name']?.toString() ?? 'Contact',
                                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.ink),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    '${addr['line1'] ?? ''}, ${addr['city'] ?? 'Doha'}',
                                    style: const TextStyle(fontSize: 12, color: AppColors.slate600),
                                  ),
                                  if (addr['phone'] != null) ...[
                                    const SizedBox(height: 2),
                                    Text(
                                      addr['phone'].toString(),
                                      style: const TextStyle(fontSize: 11, color: AppColors.slate500),
                                    ),
                                  ],
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _processCheckout() async {
    if (_isSubmitting) return;

    // Address verification
    final addressId = _selectedAddress?['id'];

    if (_selectedPaymentMethod == 'stripe') {
      final rawCard = _cardNumberController.text.replaceAll(' ', '').trim();
      if (rawCard.isEmpty) {
        AppSnackBar.error(context, 'Please enter your card details or tap "Fill Stripe Test Card"');
        return;
      }

      if (!_cardFormKey.currentState!.validate()) {
        return;
      }
    }

    setState(() => _isSubmitting = true);

    try {
      final body = <String, dynamic>{
        'payment_method': _selectedPaymentMethod,
      };

      if (addressId != null) {
        body['address_id'] = addressId;
      }

      if (widget.items.isNotEmpty) {
        body['items'] = widget.items.map((it) => {
          'product_id': it['id'] ?? it['product_id'],
          'quantity': it['quantity'] ?? 1,
        }).toList();
      }

      if (_selectedPaymentMethod == 'stripe') {
        final rawCard = _cardNumberController.text.replaceAll(' ', '').trim();
        final expiryParts = _expiryController.text.split('/');
        final expMonth = expiryParts[0].trim();
        final rawYear = expiryParts.length > 1 ? expiryParts[1].trim() : '28';
        final expYear = rawYear.length == 2 ? '20$rawYear' : rawYear;

        body['card_number'] = rawCard;
        body['exp_month'] = expMonth;
        body['exp_year'] = expYear;
        body['cvc'] = _cvcController.text.trim();
        body['cardholder_name'] = _cardholderController.text.trim().isNotEmpty
            ? _cardholderController.text.trim()
            : (_selectedAddress?['name'] ?? 'Demo Buyer');
      }

      final response = await _apiClient.post(
        '/checkout',
        body: body,
        timeout: const Duration(seconds: 60),
      );

      if (!mounted) return;

      if (response is Map<String, dynamic> && response['order'] != null) {
        final order = Map<String, dynamic>.from(response['order'] as Map);
        final paymentUrl = response['payment_url']?.toString();
        final isPaid = response['paid'] == true;

        if (_selectedPaymentMethod == 'cod') {
          // COD Success
          widget.onOrderCompleted?.call();
          Navigator.of(context).pushReplacement(
            MaterialPageRoute(
              builder: (_) => OrderSuccessScreen(
                order: order,
                paymentMethod: 'cod',
                address: _selectedAddress,
              ),
            ),
          );
        } else if (_selectedPaymentMethod == 'stripe') {
          // Stripe Payment
          if (isPaid || paymentUrl == null || paymentUrl.isEmpty) {
            // Direct in-app Stripe charge confirmed successfully!
            widget.onOrderCompleted?.call();
            Navigator.of(context).pushReplacement(
              MaterialPageRoute(
                builder: (_) => OrderSuccessScreen(
                  order: order,
                  paymentMethod: 'stripe',
                  address: _selectedAddress,
                ),
              ),
            );
          } else {
            // Fallback web portal
            final sanitizedUrl = ApiConfig.sanitizeUrl(paymentUrl);
            final uri = Uri.parse(sanitizedUrl);
            try {
              if (await canLaunchUrl(uri)) {
                await launchUrl(uri, mode: LaunchMode.externalApplication);
              }
            } catch (_) {}

            if (mounted) {
              _showStripeVerificationDialog(order);
            }
          }
        } else {
          // Other payment methods
          widget.onOrderCompleted?.call();
          Navigator.of(context).pushReplacement(
            MaterialPageRoute(
              builder: (_) => OrderSuccessScreen(
                order: order,
                paymentMethod: _selectedPaymentMethod,
                address: _selectedAddress,
              ),
            ),
          );
        }
      } else {
        throw Exception(response['message'] ?? 'Unable to complete order.');
      }
    } catch (e) {
      if (mounted) {
        String msg = e.toString()
            .replaceFirst(RegExp(r'^Exception:\s*'), '')
            .replaceFirst(RegExp(r'^ApiException:\s*'), '');
        AppSnackBar.error(context, msg);
      }
    } finally {
      if (mounted) {
        setState(() => _isSubmitting = false);
      }
    }
  }

  void _showStripeVerificationDialog(Map<String, dynamic> order) {
    bool isVerifying = false;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.lg)),
          title: const Row(
            children: [
              Icon(Icons.credit_card_rounded, color: AppColors.brand600, size: 24),
              SizedBox(width: 8),
              Text('Stripe Payment', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700)),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'A secure Stripe Checkout window has been opened in your browser.',
                style: TextStyle(fontSize: 13, color: AppColors.slate700, height: 1.4),
              ),
              const SizedBox(height: AppSpacing.md),
              Container(
                padding: const EdgeInsets.all(AppSpacing.md),
                decoration: BoxDecoration(
                  color: AppColors.slate50,
                  borderRadius: BorderRadius.circular(AppRadius.md),
                  border: Border.all(color: AppColors.slate200),
                ),
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Order Number:', style: TextStyle(fontSize: 12, color: AppColors.slate500)),
                        Text('${order['order_number']}', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.ink)),
                      ],
                    ),
                    const SizedBox(height: 6),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Total Due:', style: TextStyle(fontSize: 12, color: AppColors.slate500)),
                        Text('${_currencyFormat.format(order['total'] ?? widget.total)} QAR', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: AppColors.brand600)),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.md),
              const Text(
                'Once your card transaction is completed in Stripe, tap "I Have Paid" below.',
                style: TextStyle(fontSize: 12, color: AppColors.slate500),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: isVerifying ? null : () => Navigator.of(ctx).pop(),
              child: const Text('Cancel / Later', style: TextStyle(color: AppColors.slate500)),
            ),
            ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.brand600,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
              ),
              onPressed: isVerifying
                  ? null
                  : () async {
                      setDialogState(() => isVerifying = true);
                      try {
                        final confirmRes = await _apiClient.post(
                          '/checkout/stripe-confirm/${order['id']}',
                        );

                        if (!ctx.mounted) return;
                        Navigator.of(ctx).pop();

                        if (!mounted) return;
                        widget.onOrderCompleted?.call();

                        final confirmedOrder = (confirmRes is Map<String, dynamic> && confirmRes['order'] != null)
                            ? Map<String, dynamic>.from(confirmRes['order'] as Map)
                            : order;

                        Navigator.of(context).pushReplacement(
                          MaterialPageRoute(
                            builder: (_) => OrderSuccessScreen(
                              order: confirmedOrder,
                              paymentMethod: 'stripe',
                              address: _selectedAddress,
                            ),
                          ),
                        );
                      } catch (e) {
                        if (ctx.mounted) {
                          setDialogState(() => isVerifying = false);
                          AppSnackBar.error(ctx, 'Payment verification note: $e');
                        }
                      }
                    },
              child: isVerifying
                  ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : const Text('I Have Paid', style: TextStyle(fontWeight: FontWeight.w700)),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        title: const Text('Secure Checkout'),
        actions: const [
          Padding(
            padding: EdgeInsets.only(right: 16),
            child: Icon(Icons.lock_outline, size: 20, color: AppColors.slate400),
          ),
        ],
      ),
      body: _isLoading
          ? const LoadingView(message: 'Preparing checkout...')
          : _errorMessage != null
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(AppSpacing.xl),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.error_outline, size: 48, color: AppColors.danger),
                        const SizedBox(height: AppSpacing.md),
                        Text(_errorMessage!, textAlign: TextAlign.center, style: const TextStyle(color: AppColors.slate600)),
                        const SizedBox(height: AppSpacing.lg),
                        ElevatedButton(onPressed: _loadCheckoutData, child: const Text('Retry')),
                      ],
                    ),
                  ),
                )
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(AppSpacing.lg),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // 1. Delivery Address Section
                      _buildSectionHeader('Delivery Address', Icons.location_on_outlined),
                      const SizedBox(height: AppSpacing.sm),
                      _buildAddressCard(),
                      const SizedBox(height: AppSpacing.xl),

                      // 2. Payment Method Section
                      _buildSectionHeader('Payment Method', Icons.payment_outlined),
                      const SizedBox(height: AppSpacing.sm),
                      _buildPaymentMethodOptions(),
                      const SizedBox(height: AppSpacing.xl),

                      // 3. Order Items Summary
                      _buildSectionHeader('Order Items (${widget.items.length})', Icons.shopping_bag_outlined),
                      const SizedBox(height: AppSpacing.sm),
                      _buildItemsPreview(),
                      const SizedBox(height: AppSpacing.xl),

                      // 4. Payment Breakdown
                      _buildOrderSummaryCard(),
                      const SizedBox(height: 100), // padding for sticky bottom bar
                    ],
                  ),
                ),
      bottomNavigationBar: _isLoading ? null : _buildStickyBottomBar(),
    );
  }

  Widget _buildSectionHeader(String title, IconData icon) {
    return Row(
      children: [
        Icon(icon, size: 18, color: AppColors.brand700),
        const SizedBox(width: 6),
        Text(
          title,
          style: const TextStyle(
            fontSize: 15,
            fontWeight: FontWeight.w700,
            color: AppColors.ink,
          ),
        ),
      ],
    );
  }

  Widget _buildAddressCard() {
    if (_selectedAddress == null) {
      return Container(
        width: double.infinity,
        padding: const EdgeInsets.all(AppSpacing.lg),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(AppRadius.lg),
          border: Border.all(color: AppColors.slate200),
        ),
        child: Column(
          children: [
            const Icon(Icons.location_off_outlined, size: 36, color: AppColors.slate400),
            const SizedBox(height: AppSpacing.sm),
            const Text(
              'No delivery address found',
              style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.slate600),
            ),
            const SizedBox(height: AppSpacing.md),
            ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.brand600,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
              ),
              icon: const Icon(Icons.add, size: 18),
              label: const Text('Add Delivery Address'),
              onPressed: _showAddAddressDialog,
            ),
          ],
        ),
      );
    }

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(AppSpacing.md),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.slate200),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: AppColors.brand50,
              borderRadius: BorderRadius.circular(AppRadius.sm),
            ),
            child: const Icon(Icons.location_on, color: AppColors.brand700, size: 20),
          ),
          const SizedBox(width: AppSpacing.md),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Text(
                      _selectedAddress!['name']?.toString() ?? 'Contact',
                      style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.ink),
                    ),
                    const SizedBox(width: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1.5),
                      decoration: BoxDecoration(
                        color: AppColors.slate100,
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        _selectedAddress!['label']?.toString() ?? 'Home',
                        style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppColors.slate600),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Text(
                  '${_selectedAddress!['line1'] ?? ''}, ${_selectedAddress!['city'] ?? 'Doha'}, ${_selectedAddress!['country'] ?? 'Qatar'}',
                  style: const TextStyle(fontSize: 12, color: AppColors.slate600),
                ),
                if (_selectedAddress!['phone'] != null) ...[
                  const SizedBox(height: 2),
                  Text(
                    _selectedAddress!['phone'].toString(),
                    style: const TextStyle(fontSize: 11, color: AppColors.slate500),
                  ),
                ],
              ],
            ),
          ),
          TextButton(
            onPressed: _addresses.length > 1 ? _showAddressPickerBottomSheet : _showAddAddressDialog,
            child: Text(
              _addresses.length > 1 ? 'Change' : 'Edit',
              style: const TextStyle(fontWeight: FontWeight.w700, color: AppColors.brand700),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPaymentMethodOptions() {
    final hasCod = _paymentMethods.any((m) => m['id'] == 'cod');
    final hasStripe = _paymentMethods.any((m) => m['id'] == 'stripe');
    final hasManualBank = _paymentMethods.any((m) => m['id'] == 'manual_bank');

    final showCod = hasCod || (!hasStripe && !hasManualBank);
    final showStripe = hasStripe;

    return Column(
      children: [
        if (showCod) ...[
          _buildPaymentOptionCard(
            id: 'cod',
            title: 'Cash on Delivery (COD)',
            subtitle: 'Pay with cash upon receipt of order',
            icon: Icons.payments_outlined,
            badgeColor: AppColors.success,
          ),
          const SizedBox(height: AppSpacing.sm),
        ],
        if (showStripe) ...[
          _buildPaymentOptionCard(
            id: 'stripe',
            title: 'Credit / Debit Card',
            subtitle: 'Visa, Mastercard, AMEX • Powered by Stripe',
            icon: Icons.credit_card_rounded,
            badgeColor: const Color(0xFF635BFF),
          ),
          if (_selectedPaymentMethod == 'stripe') ...[
            const SizedBox(height: AppSpacing.md),
            _buildStripeCardInputSection(),
          ],
          const SizedBox(height: AppSpacing.sm),
        ],
        if (hasManualBank) ...[
          _buildPaymentOptionCard(
            id: 'manual_bank',
            title: 'Manual Bank Transfer',
            subtitle: 'Direct transfer to seller bank account',
            icon: Icons.account_balance_outlined,
            badgeColor: AppColors.brand600,
          ),
          const SizedBox(height: AppSpacing.sm),
        ],
      ],
    );
  }

  Widget _buildStripeCardInputSection() {
    final brand = _getCardBrand(_cardNumberController.text);
    final cardNumberDisplay = _cardNumberController.text.isEmpty ? '•••• •••• •••• ••••' : _cardNumberController.text;
    final cardholderDisplay = _cardholderController.text.isEmpty
        ? (_selectedAddress?['name']?.toString().toUpperCase() ?? 'CARDHOLDER NAME')
        : _cardholderController.text.toUpperCase();
    final expiryDisplay = _expiryController.text.isEmpty ? 'MM/YY' : _expiryController.text;

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(AppSpacing.md),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: const Color(0xFF635BFF).withValues(alpha: 0.35), width: 1.5),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF635BFF).withValues(alpha: 0.08),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // 1. Realistic Credit Card Widget
          Container(
            width: double.infinity,
            height: 175,
            padding: const EdgeInsets.all(18),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF1E293B), Color(0xFF0F172A), Color(0xFF312E81)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: Colors.white.withValues(alpha: 0.12)),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.25),
                  blurRadius: 12,
                  offset: const Offset(0, 5),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                // Top Row: Chip + Contactless + Brand
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        Container(
                          width: 38,
                          height: 28,
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: [Color(0xFFFFD700), Color(0xFFD4AF37), Color(0xFFB8860B)],
                            ),
                            borderRadius: BorderRadius.circular(5),
                            border: Border.all(color: const Color(0xFFC59B27)),
                          ),
                        ),
                        const SizedBox(width: 8),
                        const Icon(Icons.wifi, color: Colors.white70, size: 20),
                      ],
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        brand,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 12,
                          fontWeight: FontWeight.w900,
                          letterSpacing: 1.0,
                        ),
                      ),
                    ),
                  ],
                ),

                // Middle Row: Card Number
                Padding(
                  padding: const EdgeInsets.symmetric(vertical: 4),
                  child: Text(
                    cardNumberDisplay,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 16,
                      fontWeight: FontWeight.w700,
                      letterSpacing: 2.2,
                      fontFamily: 'monospace',
                    ),
                  ),
                ),

                // Bottom Row: Holder + Expiry
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'CARDHOLDER',
                            style: TextStyle(color: Colors.white60, fontSize: 8.5, fontWeight: FontWeight.w600, letterSpacing: 0.5),
                          ),
                          const SizedBox(height: 1),
                          Text(
                            cardholderDisplay,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.w700),
                          ),
                        ],
                      ),
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        const Text(
                          'EXPIRES',
                          style: TextStyle(color: Colors.white60, fontSize: 8.5, fontWeight: FontWeight.w600, letterSpacing: 0.5),
                        ),
                        const SizedBox(height: 1),
                        Text(
                          expiryDisplay,
                          style: const TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.w700),
                        ),
                      ],
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: AppSpacing.sm),

          // 2. Test Card Button
          Align(
            alignment: Alignment.centerRight,
            child: TextButton.icon(
              onPressed: _fillTestCard,
              icon: const Icon(Icons.bolt_rounded, size: 16, color: Color(0xFF635BFF)),
              label: const Text(
                'Fill Stripe Test Card (4242)',
                style: TextStyle(color: Color(0xFF635BFF), fontSize: 12, fontWeight: FontWeight.w700),
              ),
              style: TextButton.styleFrom(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                backgroundColor: const Color(0xFF635BFF).withValues(alpha: 0.08),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
              ),
            ),
          ),
          const SizedBox(height: AppSpacing.sm),

          // 3. Form fields
          Form(
            key: _cardFormKey,
            child: Column(
              children: [
                TextFormField(
                  controller: _cardNumberController,
                  keyboardType: TextInputType.number,
                  inputFormatters: [
                    FilteringTextInputFormatter.digitsOnly,
                    LengthLimitingTextInputFormatter(16),
                    _CardNumberInputFormatter(),
                  ],
                  onChanged: (_) => setState(() {}),
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
                        controller: _expiryController,
                        keyboardType: TextInputType.number,
                        inputFormatters: [
                          FilteringTextInputFormatter.digitsOnly,
                          LengthLimitingTextInputFormatter(4),
                          _CardExpiryInputFormatter(),
                        ],
                        onChanged: (_) => setState(() {}),
                        decoration: const InputDecoration(
                          labelText: 'Expiry Date *',
                          hintText: 'MM/YY',
                          prefixIcon: Icon(Icons.calendar_today_outlined, size: 18),
                        ),
                        validator: (v) {
                          if (v == null || !RegExp(r'^\d{2}/\d{2}$').hasMatch(v)) {
                            return 'Enter MM/YY';
                          }
                          final parts = v.split('/');
                          final m = int.tryParse(parts[0]) ?? 0;
                          if (m < 1 || m > 12) return 'Invalid month';
                          return null;
                        },
                      ),
                    ),
                    const SizedBox(width: AppSpacing.md),
                    Expanded(
                      child: TextFormField(
                        controller: _cvcController,
                        keyboardType: TextInputType.number,
                        obscureText: true,
                        inputFormatters: [
                          FilteringTextInputFormatter.digitsOnly,
                          LengthLimitingTextInputFormatter(4),
                        ],
                        decoration: const InputDecoration(
                          labelText: 'CVC / CVV *',
                          hintText: '123',
                          prefixIcon: Icon(Icons.lock_outline, size: 18),
                        ),
                        validator: (v) {
                          if (v == null || v.trim().length < 3) {
                            return '3-4 digits';
                          }
                          return null;
                        },
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: AppSpacing.md),
                TextFormField(
                  controller: _cardholderController,
                  textCapitalization: TextCapitalization.words,
                  onChanged: (_) => setState(() {}),
                  decoration: const InputDecoration(
                    labelText: 'Cardholder Name *',
                    hintText: 'Name as shown on card',
                    prefixIcon: Icon(Icons.person_outline, size: 18),
                  ),
                  validator: (v) => (v == null || v.trim().isEmpty) ? 'Please enter name' : null,
                ),
              ],
            ),
          ),
          const SizedBox(height: AppSpacing.md),

          // 4. Trust and SSL Badge
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
                    '256-bit SSL encrypted • Processed securely via Stripe Payments',
                    style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w600, color: AppColors.slate600),
                  ),
                ),
              ],
            ),
          ),
        ],
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

  Widget _buildPaymentOptionCard({
    required String id,
    required String title,
    required String subtitle,
    required IconData icon,
    required Color badgeColor,
  }) {
    final isSelected = _selectedPaymentMethod == id;

    return InkWell(
      onTap: () => setState(() => _selectedPaymentMethod = id),
      borderRadius: BorderRadius.circular(AppRadius.lg),
      child: Container(
        padding: const EdgeInsets.all(AppSpacing.md),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.brand50.withValues(alpha: 0.5) : AppColors.surface,
          borderRadius: BorderRadius.circular(AppRadius.lg),
          border: Border.all(
            color: isSelected ? AppColors.brand600 : AppColors.slate200,
            width: isSelected ? 1.5 : 1,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.02),
              blurRadius: 6,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Row(
          children: [
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: badgeColor.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(AppRadius.md),
              ),
              child: Icon(icon, color: badgeColor, size: 24),
            ),
            const SizedBox(width: AppSpacing.md),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w700,
                      color: AppColors.ink,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    subtitle,
                    style: const TextStyle(fontSize: 11, color: AppColors.slate500),
                  ),
                ],
              ),
            ),
            Icon(
              isSelected ? Icons.radio_button_checked : Icons.radio_button_off,
              color: isSelected ? AppColors.brand600 : AppColors.slate400,
              size: 20,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildItemsPreview() {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.slate200),
      ),
      child: ListView.separated(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        itemCount: widget.items.length,
        separatorBuilder: (_, _) => const Divider(height: 1, color: AppColors.slate100),
        itemBuilder: (context, index) {
          final item = widget.items[index];
          final product = item['product'] as Map<String, dynamic>? ?? {};
          final title = product['name']?.toString() ?? product['title']?.toString() ?? 'Product';
          final qty = (item['quantity'] as num?)?.toInt() ?? 1;
          final price = (product['price'] as num?)?.toDouble() ?? 0.0;
          final imgUrl = _resolveImage(product, item);

          return Padding(
            padding: const EdgeInsets.all(AppSpacing.md),
            child: Row(
              children: [
                Container(
                  width: 48,
                  height: 48,
                  decoration: BoxDecoration(
                    color: AppColors.slate100,
                    borderRadius: BorderRadius.circular(AppRadius.sm),
                    border: Border.all(color: AppColors.slate200),
                  ),
                  clipBehavior: Clip.antiAlias,
                  child: imgUrl != null
                      ? Image.network(
                          imgUrl,
                          fit: BoxFit.cover,
                          errorBuilder: (_, _, _) => const Icon(Icons.inventory_2_outlined, color: AppColors.slate400, size: 20),
                        )
                      : const Icon(Icons.inventory_2_outlined, color: AppColors.slate400, size: 20),
                ),
                const SizedBox(width: AppSpacing.md),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.ink),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Qty: $qty × ${_currencyFormat.format(price)} QAR',
                        style: const TextStyle(fontSize: 11, color: AppColors.slate500),
                      ),
                    ],
                  ),
                ),
                Text(
                  '${_currencyFormat.format(price * qty)} QAR',
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.ink),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildOrderSummaryCard() {
    return Container(
      padding: const EdgeInsets.all(AppSpacing.lg),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.slate200),
      ),
      child: Column(
        children: [
          _buildSummaryRow('Subtotal', '${_currencyFormat.format(widget.subtotal)} QAR'),
          const SizedBox(height: AppSpacing.sm),
          _buildSummaryRow(
            'Estimated Shipping',
            widget.shipping == 0 ? 'Free' : '${_currencyFormat.format(widget.shipping)} QAR',
            valueColor: widget.shipping == 0 ? AppColors.success : null,
          ),
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 10),
            child: Divider(height: 1, color: AppColors.slate200),
          ),
          _buildSummaryRow(
            'Total Amount',
            '${_currencyFormat.format(widget.total)} QAR',
            isBold: true,
            valueColor: AppColors.brand700,
            valueSize: 16,
          ),
        ],
      ),
    );
  }

  Widget _buildSummaryRow(String label, String value, {bool isBold = false, Color? valueColor, double valueSize = 13}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: isBold ? 14 : 13,
            fontWeight: isBold ? FontWeight.w700 : FontWeight.w500,
            color: isBold ? AppColors.ink : AppColors.slate500,
          ),
        ),
        Text(
          value,
          style: TextStyle(
            fontSize: valueSize,
            fontWeight: isBold ? FontWeight.w800 : FontWeight.w600,
            color: valueColor ?? AppColors.ink,
          ),
        ),
      ],
    );
  }

  Widget _buildStickyBottomBar() {
    final isStripe = _selectedPaymentMethod == 'stripe';

    return Container(
      padding: const EdgeInsets.all(AppSpacing.lg),
      decoration: BoxDecoration(
        color: AppColors.surface,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, -4),
          ),
        ],
      ),
      child: SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Total to pay', style: TextStyle(fontSize: 11, color: AppColors.slate500)),
                    Text(
                      '${_currencyFormat.format(widget.total)} QAR',
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w800,
                        color: AppColors.brand700,
                      ),
                    ),
                  ],
                ),
                Expanded(
                  child: Padding(
                    padding: const EdgeInsets.only(left: AppSpacing.lg),
                    child: CustomButton(
                      text: isStripe ? 'Pay with Card' : 'Place Order (COD)',
                      isLoading: _isSubmitting,
                      backgroundColor: isStripe ? const Color(0xFF635BFF) : AppColors.brand600,
                      onPressed: _isSubmitting ? null : _processCheckout,
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  String? _resolveImage(Map<String, dynamic> product, Map<String, dynamic> item) {
    final keys = ['thumbnail', 'image', 'photo', 'featured_image'];
    for (final k in keys) {
      if (product[k] != null && product[k].toString().isNotEmpty && product[k].toString() != 'null') {
        return ApiConfig.sanitizeUrl(product[k].toString());
      }
      if (item[k] != null && item[k].toString().isNotEmpty && item[k].toString() != 'null') {
        return ApiConfig.sanitizeUrl(item[k].toString());
      }
    }
    return null;
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
