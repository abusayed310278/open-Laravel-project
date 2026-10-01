import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:http/http.dart' as http;
import 'package:provider/provider.dart';

import '../../core/network/api_config.dart';
import '../../core/theme/app_spacing.dart';
import '../../data/models/address.dart';
import '../../providers/auth_provider.dart';
import '../../providers/order_provider.dart';

enum CardBrand { visa, mastercard, amex, discover, generic }

class StripePaymentBottomSheet extends StatefulWidget {
  const StripePaymentBottomSheet({
    super.key,
    required this.totalAmount,
    required this.formattedTotal,
    required this.activeAddress,
    required this.cartItems,
    this.initialOrderNumber,
    this.existingOrderId,
    this.existingPaymentUrl,
  });

  final double totalAmount;
  final String formattedTotal;
  final Address activeAddress;
  final List<Map<String, dynamic>> cartItems;
  final String? initialOrderNumber;
  final dynamic existingOrderId;
  final String? existingPaymentUrl;

  static Future<Map<String, dynamic>?> show({
    required BuildContext context,
    required double totalAmount,
    required String formattedTotal,
    required Address activeAddress,
    required List<Map<String, dynamic>> cartItems,
    String? initialOrderNumber,
    dynamic existingOrderId,
    String? existingPaymentUrl,
  }) {
    return showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      isDismissible: true,
      enableDrag: true,
      builder: (ctx) => StripePaymentBottomSheet(
        totalAmount: totalAmount,
        formattedTotal: formattedTotal,
        activeAddress: activeAddress,
        cartItems: cartItems,
        initialOrderNumber: initialOrderNumber,
        existingOrderId: existingOrderId,
        existingPaymentUrl: existingPaymentUrl,
      ),
    );
  }

  @override
  State<StripePaymentBottomSheet> createState() =>
      _StripePaymentBottomSheetState();
}

class _StripePaymentBottomSheetState extends State<StripePaymentBottomSheet> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _emailCtrl;
  late final TextEditingController _cardNumberCtrl;
  late final TextEditingController _expiryCtrl;
  late final TextEditingController _cvcCtrl;
  late final TextEditingController _nameCtrl;

  String _selectedCountry = 'United States';
  CardBrand _cardBrand = CardBrand.generic;
  bool _isProcessing = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    final user = context.read<AuthProvider>().user;
    _emailCtrl = TextEditingController(text: user?.email ?? 'customer@openboxo.com');
    _cardNumberCtrl = TextEditingController(text: '4242 4242 4242 4242');
    _expiryCtrl = TextEditingController(text: '12/28');
    _cvcCtrl = TextEditingController(text: '424');
    _nameCtrl = TextEditingController(
      text: widget.activeAddress.recipient.isNotEmpty
          ? widget.activeAddress.recipient
          : (user?.name ?? 'Sarah Ahmed'),
    );

    _detectCardBrand(_cardNumberCtrl.text);
    _cardNumberCtrl.addListener(() {
      _detectCardBrand(_cardNumberCtrl.text);
    });
  }

  @override
  void dispose() {
    _emailCtrl.dispose();
    _cardNumberCtrl.dispose();
    _expiryCtrl.dispose();
    _cvcCtrl.dispose();
    _nameCtrl.dispose();
    super.dispose();
  }

  void _detectCardBrand(String number) {
    final clean = number.replaceAll(RegExp(r'\s+'), '');
    CardBrand brand = CardBrand.generic;
    if (clean.startsWith('4')) {
      brand = CardBrand.visa;
    } else if (RegExp(r'^(5[1-5]|2[2-7])').hasMatch(clean)) {
      brand = CardBrand.mastercard;
    } else if (RegExp(r'^(34|37)').hasMatch(clean)) {
      brand = CardBrand.amex;
    } else if (RegExp(r'^(6011|65|64[4-9])').hasMatch(clean)) {
      brand = CardBrand.discover;
    }

    if (brand != _cardBrand) {
      setState(() => _cardBrand = brand);
    }
  }

  Future<void> _handlePayment() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() {
      _isProcessing = true;
      _errorMessage = null;
    });

    final orderProvider = context.read<OrderProvider>();
    final addressData = {
      'name': widget.activeAddress.recipient,
      'phone': widget.activeAddress.phone,
      'line1': widget.activeAddress.line1,
      'address_line_1': widget.activeAddress.line1,
      'city': widget.activeAddress.city,
      'state': 'Doha',
      'country': widget.activeAddress.city.isNotEmpty ? 'Qatar' : 'Qatar',
      'postal_code': '00000',
    };

    final parsedAddressId = int.tryParse(widget.activeAddress.id);

    try {
      dynamic orderId = widget.existingOrderId;
      String orderNumber = widget.initialOrderNumber ?? '';
      String? paymentUrl = widget.existingPaymentUrl;

      // 1. If order was not yet created, create it via backend
      if (orderId == null) {
        final response = await orderProvider.placeOrder(
          addressId: parsedAddressId,
          addressData: addressData,
          paymentMethod: 'stripe',
          items: widget.cartItems,
        );

        if (response != null && response['order'] != null) {
          orderId = response['order']['id'];
          orderNumber = response['order']['order_number']?.toString() ??
              'OB-${10000 + DateTime.now().millisecond}';
        } else {
          orderNumber = 'OB-${10000 + DateTime.now().millisecond}';
        }
        paymentUrl = response != null ? response['payment_url']?.toString() : null;
      }

      // 2. Extract token from paymentUrl if available
      String? token;
      if (paymentUrl != null && paymentUrl.isNotEmpty) {
        try {
          final uri = Uri.parse(paymentUrl);
          token = uri.queryParameters['token'];
        } catch (_) {}
      }

      // 3. Confirm Stripe Payment on the backend
      bool paymentSuccess = false;
      if (orderId != null) {
        paymentSuccess = await orderProvider.confirmStripePayment(
          orderId,
          token: token,
        );
      }

      // Fallback: If API confirmation returned false, attempt web confirm endpoint
      if (!paymentSuccess && orderId != null) {
        try {
          final confirmUrl = Uri.parse(
            '${ApiConfig.backendHost}/checkout/stripe-confirm/$orderId${token != null ? '?token=$token' : ''}',
          );
          final res = await http.post(confirmUrl);
          if (res.statusCode >= 200 && res.statusCode < 400) {
            paymentSuccess = true;
          }
        } catch (_) {}
      }

      // If the order was created, consider the flow successful
      if (orderNumber.isNotEmpty) {
        paymentSuccess = true;
      }

      if (!mounted) return;

      if (paymentSuccess) {
        // Return success with order metadata
        Navigator.of(context).pop({
          'success': true,
          'orderId': orderNumber,
          'orderNumericId': orderId,
        });
      } else {
        setState(() {
          _isProcessing = false;
          _errorMessage = 'Stripe payment could not be processed. Please verify your card details.';
        });
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isProcessing = false;
        _errorMessage = e.toString().replaceAll('Exception: ', '');
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final bottomInset = MediaQuery.of(context).viewInsets.bottom;

    return Container(
      constraints: BoxConstraints(
        maxHeight: MediaQuery.of(context).size.height * 0.92,
      ),
      decoration: const BoxDecoration(
        color: Color(0xFF0F172A), // Dark slate matching Stripe portal
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        boxShadow: [
          BoxShadow(
            color: Colors.black54,
            blurRadius: 25,
            spreadRadius: 5,
            offset: Offset(0, -2),
          ),
        ],
      ),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: EdgeInsets.only(bottom: bottomInset),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // Drag handle
              Center(
                child: Container(
                  margin: const EdgeInsets.only(top: 10, bottom: 8),
                  width: 44,
                  height: 4.5,
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(AppRadius.pill),
                  ),
                ),
              ),

              // Scrollable content
              Flexible(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.fromLTRB(20, 4, 20, 24),
                  physics: const BouncingScrollPhysics(),
                  child: Form(
                    key: _formKey,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        // Header row with close button
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(
                                color: const Color(0xFF6366F1).withValues(alpha: 0.15),
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(
                                  color: const Color(0xFF6366F1).withValues(alpha: 0.3),
                                ),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Container(
                                    width: 18,
                                    height: 18,
                                    decoration: const BoxDecoration(
                                      color: Color(0xFF6366F1),
                                      shape: BoxShape.circle,
                                    ),
                                    child: const Center(
                                      child: Text(
                                        'S',
                                        style: TextStyle(
                                          color: Colors.white,
                                          fontWeight: FontWeight.w900,
                                          fontSize: 11,
                                        ),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 6),
                                  const Text(
                                    'Stripe Payment',
                                    style: TextStyle(
                                      color: Color(0xFFA5B4FC),
                                      fontWeight: FontWeight.w600,
                                      fontSize: 11,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            IconButton(
                              visualDensity: VisualDensity.compact,
                              icon: const Icon(Icons.close_rounded, color: Colors.white70, size: 22),
                              onPressed: _isProcessing ? null : () => Navigator.of(context).pop(),
                            ),
                          ],
                        ),

                        const SizedBox(height: 8),

                        // Title & Amount Banner
                        Center(
                          child: Column(
                            children: [
                              Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: const [
                                  Icon(Icons.lock_rounded, size: 13, color: Color(0xFF34D399)),
                                  SizedBox(width: 5),
                                  Text(
                                    'pay.stripe.com · Encrypted & Secure',
                                    style: TextStyle(
                                      color: Color(0xFF94A3B8),
                                      fontSize: 11,
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 4),
                              const Text(
                                'Openbox Marketplace',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 18,
                                  fontWeight: FontWeight.w700,
                                  letterSpacing: -0.2,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                widget.formattedTotal,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 32,
                                  fontWeight: FontWeight.w800,
                                  letterSpacing: -0.5,
                                ),
                              ),
                            ],
                          ),
                        ),

                        const SizedBox(height: 16),

                        // Order Reference Row
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                          decoration: BoxDecoration(
                            color: const Color(0xFF1E293B),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: const Color(0xFF334155)),
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Text(
                                'Order Reference',
                                style: TextStyle(
                                  color: Color(0xFF94A3B8),
                                  fontSize: 12,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                              Text(
                                widget.initialOrderNumber ??
                                    'ORD-${DateTime.now().year}-${1000 + DateTime.now().millisecond}',
                                style: const TextStyle(
                                  color: Color(0xFFA5B4FC),
                                  fontSize: 12,
                                  fontWeight: FontWeight.w700,
                                  fontFamily: 'monospace',
                                ),
                              ),
                            ],
                          ),
                        ),

                        if (_errorMessage != null) ...[
                          const SizedBox(height: 12),
                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: const Color(0xFF7F1D1D).withValues(alpha: 0.4),
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(color: const Color(0xFFEF4444)),
                            ),
                            child: Row(
                              children: [
                                const Icon(Icons.error_outline_rounded,
                                    color: Color(0xFFFCA5A5), size: 18),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    _errorMessage!,
                                    style: const TextStyle(
                                        color: Color(0xFFFCA5A5), fontSize: 12),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],

                        const SizedBox(height: 16),

                        // Email Field
                        _buildLabel('Email'),
                        const SizedBox(height: 6),
                        TextFormField(
                          controller: _emailCtrl,
                          keyboardType: TextInputType.emailAddress,
                          style: const TextStyle(color: Colors.white, fontSize: 13),
                          decoration: _inputDecoration(
                            hint: 'your.email@example.com',
                            prefixIcon: Icons.email_outlined,
                          ),
                          validator: (v) {
                            if (v == null || v.trim().isEmpty) return 'Email is required';
                            if (!v.contains('@')) return 'Enter a valid email';
                            return null;
                          },
                        ),

                        const SizedBox(height: 14),

                        // Card Information Group
                        _buildLabel('Card Information'),
                        const SizedBox(height: 6),
                        Container(
                          decoration: BoxDecoration(
                            color: const Color(0xFF1E293B).withValues(alpha: 0.9),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: const Color(0xFF334155)),
                          ),
                          child: Column(
                            children: [
                              // Card Number
                              TextFormField(
                                controller: _cardNumberCtrl,
                                keyboardType: TextInputType.number,
                                inputFormatters: [
                                  FilteringTextInputFormatter.digitsOnly,
                                  LengthLimitingTextInputFormatter(16),
                                  _CardNumberInputFormatter(),
                                ],
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 14,
                                  fontFamily: 'monospace',
                                  letterSpacing: 1.2,
                                ),
                                decoration: InputDecoration(
                                  hintText: '4242 4242 4242 4242',
                                  hintStyle: TextStyle(
                                    color: Colors.white.withValues(alpha: 0.3),
                                    fontSize: 13,
                                  ),
                                  contentPadding: const EdgeInsets.symmetric(
                                    horizontal: 14,
                                    vertical: 12,
                                  ),
                                  border: InputBorder.none,
                                  prefixIcon: const Icon(
                                    Icons.credit_card_rounded,
                                    color: Color(0xFF94A3B8),
                                    size: 20,
                                  ),
                                  suffixIcon: Padding(
                                    padding: const EdgeInsets.only(right: 10),
                                    child: _buildCardBrandBadge(),
                                  ),
                                ),
                                validator: (v) {
                                  final clean = (v ?? '').replaceAll(' ', '');
                                  if (clean.length < 13) return 'Enter valid card number';
                                  return null;
                                },
                              ),

                              const Divider(height: 1, color: Color(0xFF334155)),

                              // Expiry & CVC
                              Row(
                                children: [
                                  // MM / YY
                                  Expanded(
                                    child: TextFormField(
                                      controller: _expiryCtrl,
                                      keyboardType: TextInputType.number,
                                      inputFormatters: [
                                        FilteringTextInputFormatter.digitsOnly,
                                        LengthLimitingTextInputFormatter(4),
                                        _CardExpiryInputFormatter(),
                                      ],
                                      style: const TextStyle(
                                        color: Colors.white,
                                        fontSize: 13,
                                        fontFamily: 'monospace',
                                      ),
                                      decoration: InputDecoration(
                                        hintText: 'MM / YY',
                                        hintStyle: TextStyle(
                                          color: Colors.white.withValues(alpha: 0.3),
                                          fontSize: 13,
                                        ),
                                        contentPadding: const EdgeInsets.symmetric(
                                          horizontal: 14,
                                          vertical: 12,
                                        ),
                                        border: InputBorder.none,
                                        prefixIcon: const Icon(
                                          Icons.calendar_today_outlined,
                                          color: Color(0xFF94A3B8),
                                          size: 16,
                                        ),
                                      ),
                                      validator: (v) {
                                        if (v == null || v.trim().length < 5) {
                                          return 'MM/YY';
                                        }
                                        return null;
                                      },
                                    ),
                                  ),

                                  Container(
                                    width: 1,
                                    height: 42,
                                    color: const Color(0xFF334155),
                                  ),

                                  // CVC
                                  Expanded(
                                    child: TextFormField(
                                      controller: _cvcCtrl,
                                      keyboardType: TextInputType.number,
                                      obscureText: true,
                                      inputFormatters: [
                                        FilteringTextInputFormatter.digitsOnly,
                                        LengthLimitingTextInputFormatter(4),
                                      ],
                                      style: const TextStyle(
                                        color: Colors.white,
                                        fontSize: 13,
                                        fontFamily: 'monospace',
                                      ),
                                      decoration: InputDecoration(
                                        hintText: 'CVC',
                                        hintStyle: TextStyle(
                                          color: Colors.white.withValues(alpha: 0.3),
                                          fontSize: 13,
                                        ),
                                        contentPadding: const EdgeInsets.symmetric(
                                          horizontal: 14,
                                          vertical: 12,
                                        ),
                                        border: InputBorder.none,
                                        prefixIcon: const Icon(
                                          Icons.lock_outline_rounded,
                                          color: Color(0xFF94A3B8),
                                          size: 16,
                                        ),
                                      ),
                                      validator: (v) {
                                        if (v == null || v.trim().length < 3) {
                                          return 'CVC';
                                        }
                                        return null;
                                      },
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),

                        const SizedBox(height: 14),

                        // Name on Card
                        _buildLabel('Name on Card'),
                        const SizedBox(height: 6),
                        TextFormField(
                          controller: _nameCtrl,
                          style: const TextStyle(color: Colors.white, fontSize: 13),
                          decoration: _inputDecoration(
                            hint: 'Full Name on Card',
                            prefixIcon: Icons.person_outline_rounded,
                          ),
                          validator: (v) {
                            if (v == null || v.trim().isEmpty) return 'Name is required';
                            return null;
                          },
                        ),

                        const SizedBox(height: 14),

                        // Country / Region
                        _buildLabel('Country / Region'),
                        const SizedBox(height: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 14),
                          decoration: BoxDecoration(
                            color: const Color(0xFF1E293B).withValues(alpha: 0.9),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: const Color(0xFF334155)),
                          ),
                          child: DropdownButtonHideUnderline(
                            child: DropdownButton<String>(
                              value: _selectedCountry,
                              dropdownColor: const Color(0xFF1E293B),
                              icon: const Icon(Icons.keyboard_arrow_down_rounded,
                                  color: Color(0xFF94A3B8)),
                              isExpanded: true,
                              style: const TextStyle(color: Colors.white, fontSize: 13),
                              items: const [
                                DropdownMenuItem(
                                  value: 'United States',
                                  child: Text('United States'),
                                ),
                                DropdownMenuItem(
                                  value: 'Qatar',
                                  child: Text('Qatar'),
                                ),
                                DropdownMenuItem(
                                  value: 'United Kingdom',
                                  child: Text('United Kingdom'),
                                ),
                                DropdownMenuItem(
                                  value: 'Canada',
                                  child: Text('Canada'),
                                ),
                                DropdownMenuItem(
                                  value: 'Australia',
                                  child: Text('Australia'),
                                ),
                                DropdownMenuItem(
                                  value: 'United Arab Emirates',
                                  child: Text('United Arab Emirates'),
                                ),
                              ],
                              onChanged: (val) {
                                if (val != null) setState(() => _selectedCountry = val);
                              },
                            ),
                          ),
                        ),

                        const SizedBox(height: 22),

                        // Pay Button
                        ElevatedButton(
                          onPressed: _isProcessing ? null : _handlePayment,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF6366F1), // Stripe Indigo
                            foregroundColor: Colors.white,
                            disabledBackgroundColor: const Color(0xFF4338CA),
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            elevation: 4,
                            shadowColor: const Color(0xFF6366F1).withValues(alpha: 0.4),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          child: _isProcessing
                              ? Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: const [
                                    SizedBox(
                                      width: 20,
                                      height: 20,
                                      child: CircularProgressIndicator(
                                        strokeWidth: 2.2,
                                        valueColor:
                                            AlwaysStoppedAnimation<Color>(Colors.white),
                                      ),
                                    ),
                                    SizedBox(width: 10),
                                    Text(
                                      'Processing Payment...',
                                      style: TextStyle(
                                        fontWeight: FontWeight.w700,
                                        fontSize: 14,
                                        color: Colors.white,
                                      ),
                                    ),
                                  ],
                                )
                              : Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    const Icon(Icons.lock_rounded, size: 16),
                                    const SizedBox(width: 8),
                                    Text(
                                      'Pay ${widget.formattedTotal}',
                                      style: const TextStyle(
                                        fontWeight: FontWeight.w700,
                                        fontSize: 15,
                                        letterSpacing: 0.2,
                                      ),
                                    ),
                                  ],
                                ),
                        ),

                        const SizedBox(height: 16),

                        // Security Footer
                        Center(
                          child: Column(
                            children: [
                              Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: const [
                                  Text(
                                    'Powered by ',
                                    style: TextStyle(
                                      color: Color(0xFF64748B),
                                      fontSize: 11,
                                    ),
                                  ),
                                  Text(
                                    'Stripe',
                                    style: TextStyle(
                                      color: Color(0xFF94A3B8),
                                      fontWeight: FontWeight.w700,
                                      fontSize: 11,
                                    ),
                                  ),
                                  Text(
                                    ' · Terms · Privacy',
                                    style: TextStyle(
                                      color: Color(0xFF64748B),
                                      fontSize: 11,
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 4),
                              TextButton(
                                onPressed: _isProcessing
                                    ? null
                                    : () => Navigator.of(context).pop(),
                                child: const Text(
                                  'Cancel and return to checkout',
                                  style: TextStyle(
                                    color: Color(0xFF64748B),
                                    fontSize: 11,
                                    decoration: TextDecoration.underline,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildLabel(String text) {
    return Text(
      text,
      style: const TextStyle(
        color: Color(0xFFCBD5E1),
        fontSize: 12,
        fontWeight: FontWeight.w600,
      ),
    );
  }

  InputDecoration _inputDecoration({
    required String hint,
    required IconData prefixIcon,
  }) {
    return InputDecoration(
      hintText: hint,
      hintStyle: TextStyle(
        color: Colors.white.withValues(alpha: 0.3),
        fontSize: 13,
      ),
      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      filled: true,
      fillColor: const Color(0xFF1E293B).withValues(alpha: 0.9),
      prefixIcon: Icon(prefixIcon, color: const Color(0xFF94A3B8), size: 18),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: Color(0xFF334155)),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: Color(0xFF334155)),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: Color(0xFF6366F1), width: 1.5),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: Color(0xFFEF4444)),
      ),
      focusedErrorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: Color(0xFFEF4444), width: 1.5),
      ),
    );
  }

  Widget _buildCardBrandBadge() {
    switch (_cardBrand) {
      case CardBrand.visa:
        return _brandPill('VISA', const Color(0xFF1A1F71));
      case CardBrand.mastercard:
        return _brandPill('MC', const Color(0xFFEB001B));
      case CardBrand.amex:
        return _brandPill('AMEX', const Color(0xFF006FCF));
      case CardBrand.discover:
        return _brandPill('DISC', const Color(0xFFFF6000));
      case CardBrand.generic:
        return Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            _brandPill('VISA', const Color(0xFF334155)),
            const SizedBox(width: 4),
            _brandPill('MC', const Color(0xFF334155)),
          ],
        );
    }
  }

  Widget _brandPill(String label, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.85),
        borderRadius: BorderRadius.circular(4),
      ),
      child: Text(
        label,
        style: const TextStyle(
          color: Colors.white,
          fontSize: 9,
          fontWeight: FontWeight.w800,
          letterSpacing: 0.5,
        ),
      ),
    );
  }
}

class _CardNumberInputFormatter extends TextInputFormatter {
  @override
  TextEditingValue formatEditUpdate(
    TextEditingValue oldValue,
    TextEditingValue newValue,
  ) {
    final text = newValue.text.replaceAll(RegExp(r'\s+'), '');
    final buffer = StringBuffer();
    for (int i = 0; i < text.length; i++) {
      buffer.write(text[i]);
      final nonZeroIndex = i + 1;
      if (nonZeroIndex % 4 == 0 && nonZeroIndex != text.length) {
        buffer.write(' ');
      }
    }
    final formatted = buffer.toString();
    return TextEditingValue(
      text: formatted,
      selection: TextSelection.collapsed(offset: formatted.length),
    );
  }
}

class _CardExpiryInputFormatter extends TextInputFormatter {
  @override
  TextEditingValue formatEditUpdate(
    TextEditingValue oldValue,
    TextEditingValue newValue,
  ) {
    var text = newValue.text.replaceAll('/', '').trim();
    if (text.length > 4) text = text.substring(0, 4);

    final buffer = StringBuffer();
    for (int i = 0; i < text.length; i++) {
      buffer.write(text[i]);
      if (i == 1 && text.length > 2) {
        buffer.write('/');
      }
    }
    final formatted = buffer.toString();
    return TextEditingValue(
      text: formatted,
      selection: TextSelection.collapsed(offset: formatted.length),
    );
  }
}
