import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/utils/formatters.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../data/mock/app_state.dart';
import '../../data/models/address.dart';
import '../../providers/address_provider.dart';
import '../../providers/cart_provider.dart';
import '../../providers/order_provider.dart';
import '../account/add_address_dialog.dart';
import '../../core/services/stripe_service.dart';
import 'order_confirmation_screen.dart';
import 'payment_webview_screen.dart';

class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({super.key});

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  Address? _selectedAddress;
  String _payment = 'cod';

  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      if (!mounted) return;
      context.read<AddressProvider>().fetchAddresses();
      final cartItems = context.read<CartProvider>().items.map((i) => {
        'product_id': int.tryParse(i.product.id) ?? i.product.id,
        'quantity': i.quantity,
      }).toList();
      context.read<OrderProvider>().fetchPaymentMethods(items: cartItems.isNotEmpty ? cartItems : null);
    });
  }

  Future<void> _openAddAddressModal({Address? initialAddress}) async {
    await AddAddressModal.show(context, initialAddress: initialAddress);
    if (!mounted) return;
    final addrs = context.read<AddressProvider>().addresses;
    if (addrs.isNotEmpty) {
      setState(() {
        _selectedAddress = context.read<AddressProvider>().defaultAddress ?? addrs.last;
      });
    }
  }

  Future<void> _placeOrder(
    Address? activeAddress, {
    double totalAmount = 0.0,
    String formattedTotal = '',
  }) async {
    debugPrint('--> _placeOrder button tapped! activeAddress=$activeAddress');
    final cartProvider = context.read<CartProvider>();
    final orderProvider = context.read<OrderProvider>();

    if (activeAddress == null) {
      AppSnackbar.showError(context, 'Please add a delivery address first');
      _openAddAddressModal();
      return;
    }

    if (cartProvider.items.isEmpty) {
      AppSnackbar.showError(context, 'Your cart is empty');
      return;
    }

    final cartItems = cartProvider.items.map((i) => {
      'product_id': int.tryParse(i.product.id) ?? i.product.id,
      'quantity': i.quantity,
    }).toList();

    final addressData = {
      'name': activeAddress.recipient,
      'phone': activeAddress.phone,
      'line1': activeAddress.line1,
      'address_line_1': activeAddress.line1,
      'city': activeAddress.city,
      'state': 'Doha',
      'country': 'Qatar',
      'postal_code': '00000',
    };

    final parsedAddressId = int.tryParse(activeAddress.id);

    try {
      final response = await orderProvider.placeOrder(
        addressId: parsedAddressId,
        addressData: addressData,
        paymentMethod: _payment,
        items: cartItems,
      );

      if (!mounted) return;

      final orderNumber = (response != null && response['order'] != null)
          ? response['order']['order_number']?.toString() ?? 'OB-${10000 + DateTime.now().millisecond}'
          : 'OB-${10000 + DateTime.now().millisecond}';

      final dynamic orderId = response != null && response['order'] != null
          ? response['order']['id']
          : null;

      final String? paymentUrl = response != null ? response['payment_url']?.toString() : null;
      final Map<String, dynamic>? stripeData = response != null && response['stripe'] is Map
          ? Map<String, dynamic>.from(response['stripe'])
          : null;

      // Handle Stripe: Show official native Stripe PaymentSheet bottom sheet
      if (_payment == 'stripe') {
        final clientSecret = stripeData != null ? stripeData['client_secret']?.toString() : null;
        final publishableKey = stripeData != null ? stripeData['publishable_key']?.toString() : null;

        if (clientSecret == null || clientSecret.isEmpty) {
          if (!mounted) return;
          AppSnackbar.showError(
            context,
            'Unable to initialize Stripe Payment Sheet. Please verify Stripe configuration on the server.',
          );
          return;
        }

        bool paymentCompleted = false;
        try {
          if (publishableKey != null && publishableKey.isNotEmpty) {
            await StripeService.instance.init(publishableKey: publishableKey);
          }
          paymentCompleted = await StripeService.instance.presentOfficialPaymentSheet(
            clientSecret: clientSecret,
            merchantDisplayName: 'Openbox Marketplace',
          );
        } catch (e) {
          if (!mounted) return;
          AppSnackbar.showError(
            context,
            'Stripe payment failed: ${e.toString().replaceAll("Exception: ", "")}',
          );
          return;
        }

        if (paymentCompleted) {
          if (orderId != null) {
            await orderProvider.confirmStripePayment(orderId);
          }
          await cartProvider.clear();
          CartStore.instance.clear();
          if (!mounted) return;
          AppSnackbar.showSuccess(context, 'Stripe payment completed successfully!');
          Navigator.of(context).pushReplacement(
            MaterialPageRoute(builder: (_) => OrderConfirmationScreen(orderId: orderNumber)),
          );
        } else {
          if (!mounted) return;
          AppSnackbar.showError(context, 'Payment was cancelled. Your order remains pending.');
        }
        return;
      }

      if (paymentUrl != null && paymentUrl.isNotEmpty && _payment == 'paypal') {
        final isPaid = await Navigator.of(context).push<bool>(
          MaterialPageRoute(
            builder: (_) => PaymentWebViewScreen(
              paymentUrl: paymentUrl,
              orderId: orderNumber,
              title: 'PayPal Payment',
            ),
          ),
        );

        if (isPaid == true) {
          await cartProvider.clear();
          CartStore.instance.clear();
          if (!mounted) return;
          AppSnackbar.showSuccess(context, 'Payment completed successfully!');
          Navigator.of(context).pushReplacement(
            MaterialPageRoute(builder: (_) => OrderConfirmationScreen(orderId: orderNumber)),
          );
        } else {
          if (!mounted) return;
          AppSnackbar.showError(context, 'Payment was not completed. Your order remains pending.');
        }
      } else {
        await cartProvider.clear();
        CartStore.instance.clear();

        if (!mounted) return;
        AppSnackbar.showSuccess(context, 'Order placed successfully!');
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (_) => OrderConfirmationScreen(orderId: orderNumber)),
        );
      }
    } catch (e) {
      if (!mounted) return;
      final msg = e.toString().replaceAll('Exception: ', '');
      if (msg.toLowerCase().contains('not enough stock') || msg.toLowerCase().contains('stock')) {
        showDialog(
          context: context,
          builder: (ctx) => AlertDialog(
            title: const Text('Item Out of Stock'),
            content: Text('$msg\n\nPlease remove this item from your cart and select an in-stock product to proceed.'),
            actions: [
              TextButton(
                onPressed: () {
                  Navigator.of(ctx).pop();
                  Navigator.of(context).pop();
                },
                child: const Text('Back to Cart'),
              ),
            ],
          ),
        );
      } else {
        AppSnackbar.showError(context, msg);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final addressProvider = context.watch<AddressProvider>();
    final addresses = addressProvider.addresses;
    final activeAddress = _selectedAddress != null && addresses.any((a) => a.id == _selectedAddress!.id)
        ? _selectedAddress!
        : addressProvider.defaultAddress;

    final cartProvider = context.watch<CartProvider>();
    final orderProvider = context.watch<OrderProvider>();
    final subtotal = cartProvider.subtotal;
    const shipping = 25.0;
    final total = subtotal + shipping;
    final isLoading = orderProvider.isLoading;

    final activePaymentGateways = orderProvider.paymentMethods
        .where((m) => m['enabled'] == true)
        .toList();
    final methodsToShow = activePaymentGateways.isNotEmpty
        ? activePaymentGateways
        : [
            {'id': 'cod', 'name': 'Cash on Delivery', 'enabled': true},
          ];

    if (!methodsToShow.any((m) => m['id'] == _payment)) {
      _payment = methodsToShow.first['id']?.toString() ?? 'cod';
    }

    return Scaffold(
      appBar: AppBar(title: const Text('Checkout')),
      body: ListView(
        padding: const EdgeInsets.all(AppSpacing.lg),
        children: [
          const _SectionTitle('Delivery Address'),
          if (addresses.isEmpty)
            Container(
              padding: const EdgeInsets.all(AppSpacing.lg),
              margin: const EdgeInsets.only(bottom: AppSpacing.md),
              decoration: BoxDecoration(
                color: AppColors.surface,
                border: Border.all(color: AppColors.slate200),
                borderRadius: BorderRadius.circular(AppRadius.md),
              ),
              child: Column(
                children: [
                  const Icon(Icons.location_off_outlined, size: 36, color: AppColors.slate400),
                  const SizedBox(height: AppSpacing.xs),
                  const Text('No delivery address added yet', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: AppColors.ink)),
                  const SizedBox(height: 2),
                  const Text('Please add your shipping address to proceed', style: TextStyle(fontSize: 11, color: AppColors.slate500)),
                  const SizedBox(height: AppSpacing.sm),
                  ElevatedButton.icon(
                    onPressed: () => _openAddAddressModal(),
                    icon: const Icon(Icons.add_location_alt_outlined, size: 16),
                    label: const Text('Add Delivery Address', style: TextStyle(fontSize: 12)),
                    style: ElevatedButton.styleFrom(padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8)),
                  ),
                ],
              ),
            )
          else ...[
            ...addresses.map(
              (address) => _AddressTile(
                address: address,
                selected: activeAddress != null && address.id == activeAddress.id,
                onTap: () => setState(() => _selectedAddress = address),
                onEdit: () => _openAddAddressModal(initialAddress: address),
              ),
            ),
            TextButton.icon(
              onPressed: () => _openAddAddressModal(),
              icon: const Icon(Icons.add_rounded, size: 18),
              label: const Text('Add new address'),
            ),
          ],
          const SizedBox(height: AppSpacing.lg),
          const _SectionTitle('Payment Method'),
          Container(
            margin: const EdgeInsets.only(bottom: AppSpacing.md),
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: BoxDecoration(
              color: orderProvider.isMultiVendor ? AppColors.brand50 : AppColors.surface,
              borderRadius: BorderRadius.circular(AppRadius.sm),
              border: Border.all(color: orderProvider.isMultiVendor ? AppColors.brand200 : AppColors.slate200),
            ),
            child: Row(
              children: [
                Icon(
                  orderProvider.isMultiVendor ? Icons.shield_outlined : Icons.storefront_outlined,
                  size: 16,
                  color: orderProvider.isMultiVendor ? AppColors.brand700 : AppColors.slate500,
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    orderProvider.isMultiVendor
                        ? 'Multi-Store Cart — Processed via Platform Central Gateway'
                        : 'Store Gateway: ${orderProvider.vendorName}',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                      color: orderProvider.isMultiVendor ? AppColors.brand800 : AppColors.slate700,
                    ),
                  ),
                ),
              ],
            ),
          ),
          ...methodsToShow.map((m) {
            final id = m['id']?.toString() ?? 'cod';
            final title = switch (id) {
              'manual_bank' => 'Manual Bank Transfer',
              'stripe' => 'Card (Stripe)',
              'paypal' => 'PayPal',
              _ => 'Cash on Delivery',
            };
            final subtitle = switch (id) {
              'manual_bank' => 'Direct wire / bank transfer',
              'stripe' => 'Fast card payment via Stripe Bottom Sheet',
              'paypal' => 'Pay via your PayPal account or card',
              _ => 'Pay with cash upon delivery',
            };
            final icon = switch (id) {
              'manual_bank' => Icons.account_balance_outlined,
              'stripe' => Icons.credit_card_outlined,
              'paypal' => Icons.account_balance_wallet_outlined,
              _ => Icons.payments_outlined,
            };

            return _PaymentTile(
              id: id,
              title: title,
              subtitle: subtitle,
              icon: icon,
              selected: _payment == id,
              enabled: true,
              onTap: () => setState(() => _payment = id),
            );
          }),
          const SizedBox(height: AppSpacing.lg),
          const _SectionTitle('Order Summary'),
          Container(
            padding: const EdgeInsets.all(AppSpacing.lg),
            decoration: BoxDecoration(
              color: AppColors.surface,
              border: Border.all(color: AppColors.slate200),
              borderRadius: BorderRadius.circular(AppRadius.md),
            ),
            child: Column(
              children: [
                _SummaryRow(label: 'Subtotal (${cartProvider.itemCount} items)', value: formatPrice(subtotal)),
                const SizedBox(height: AppSpacing.sm),
                _SummaryRow(label: 'Shipping', value: formatPrice(shipping)),
                const Padding(padding: EdgeInsets.symmetric(vertical: AppSpacing.sm), child: Divider(height: 1)),
                _SummaryRow(label: 'Total', value: formatPrice(total), isBold: true),
              ],
            ),
          ),
          const SizedBox(height: AppSpacing.xxl),
          ElevatedButton(
            onPressed: isLoading
                ? null
                : () => _placeOrder(
                      activeAddress,
                      totalAmount: total,
                      formattedTotal: formatPrice(total),
                    ),
            child: isLoading
                ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                : Text(
                    _payment == 'stripe'
                        ? 'Proceed to Payment · ${formatPrice(total)}'
                        : _payment == 'paypal'
                            ? 'Proceed to PayPal · ${formatPrice(total)}'
                            : 'Place Order · ${formatPrice(total)}',
                  ),
          ),
        ],
      ),
    );
  }
}

class _SectionTitle extends StatelessWidget {
  const _SectionTitle(this.text);
  final String text;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: AppSpacing.sm),
      child: Text(text, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.ink)),
    );
  }
}

class _AddressTile extends StatelessWidget {
  const _AddressTile({
    required this.address,
    required this.selected,
    required this.onTap,
    this.onEdit,
  });

  final Address address;
  final bool selected;
  final VoidCallback onTap;
  final VoidCallback? onEdit;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: AppSpacing.sm),
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.all(AppSpacing.md),
          decoration: BoxDecoration(
            color: selected ? AppColors.brand50 : AppColors.surface,
            border: Border.all(color: selected ? AppColors.brand500 : AppColors.slate200),
            borderRadius: BorderRadius.circular(AppRadius.md),
          ),
          child: Row(
            children: [
              Icon(selected ? Icons.radio_button_checked_rounded : Icons.radio_button_off_rounded, color: selected ? AppColors.brand600 : AppColors.slate400, size: 20),
              const SizedBox(width: AppSpacing.md),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Text(address.label, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                        if (address.isDefault) ...[
                          const SizedBox(width: 6),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                            decoration: BoxDecoration(color: AppColors.slate100, borderRadius: BorderRadius.circular(4)),
                            child: const Text('Default', style: TextStyle(fontSize: 9, color: AppColors.slate500)),
                          ),
                        ],
                      ],
                    ),
                    const SizedBox(height: 2),
                    Text('${address.recipient} · ${address.line1}, ${address.city}', style: const TextStyle(fontSize: 12, color: AppColors.slate500)),
                    Text(address.phone, style: const TextStyle(fontSize: 12, color: AppColors.slate500)),
                  ],
                ),
              ),
              IconButton(
                icon: const Icon(Icons.edit_outlined, size: 18, color: AppColors.slate500),
                onPressed: onEdit ?? () => AddAddressModal.show(context, initialAddress: address),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _PaymentTile extends StatelessWidget {
  const _PaymentTile({
    required this.id,
    required this.title,
    required this.subtitle,
    required this.icon,
    required this.selected,
    required this.onTap,
    this.enabled = true,
  });

  final String id;
  final String title;
  final String subtitle;
  final IconData icon;
  final bool selected;
  final bool enabled;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: AppSpacing.sm),
      child: Opacity(
        opacity: enabled ? 1 : 0.5,
        child: GestureDetector(
          onTap: enabled ? onTap : null,
          child: Container(
            decoration: BoxDecoration(
              color: selected ? AppColors.brand50.withValues(alpha: 0.2) : AppColors.surface,
              border: Border.all(color: selected ? AppColors.brand500 : AppColors.slate200, width: selected ? 1.5 : 1),
              borderRadius: BorderRadius.circular(AppRadius.md),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Padding(
                  padding: const EdgeInsets.all(AppSpacing.md),
                  child: Row(
                    children: [
                      Icon(icon, color: selected ? AppColors.brand600 : AppColors.slate700, size: 22),
                      const SizedBox(width: AppSpacing.md),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Text(title, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: AppColors.ink)),
                                const Spacer(),
                                if (id == 'stripe') ...[
                                  const _BadgeTag('VISA'),
                                  const SizedBox(width: 3),
                                  const _BadgeTag('MC'),
                                  const SizedBox(width: 3),
                                  const _BadgeTag('AMEX'),
                                ] else if (id == 'paypal') ...[
                                  const _BadgeTag('PAYPAL', color: Colors.blue),
                                ],
                              ],
                            ),
                            const SizedBox(height: 2),
                            Text(subtitle, style: const TextStyle(fontSize: 11, color: AppColors.slate500)),
                          ],
                        ),
                      ),
                      const SizedBox(width: 8),
                      Icon(selected ? Icons.radio_button_checked_rounded : Icons.radio_button_off_rounded, color: selected ? AppColors.brand600 : AppColors.slate400, size: 20),
                    ],
                  ),
                ),
                if (id == 'stripe' && selected)
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(AppSpacing.md),
                    decoration: const BoxDecoration(
                      color: Color(0xFFEEF2FF),
                      border: Border(top: BorderSide(color: Color(0xFFC7D2FE))),
                      borderRadius: BorderRadius.vertical(bottom: Radius.circular(AppRadius.md)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: const [
                        Row(
                          children: [
                            Icon(Icons.credit_card_rounded, size: 14, color: Color(0xFF4338CA)),
                            SizedBox(width: 6),
                            Text('In-App Stripe Payment Sheet', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Color(0xFF312E81))),
                          ],
                        ),
                        SizedBox(height: 4),
                        Text(
                          'A secure in-app Stripe bottom sheet will open to complete your card payment instantly with 256-bit SSL encryption.',
                          style: TextStyle(fontSize: 11, color: Color(0xFF3730A3), height: 1.4),
                        ),
                      ],
                    ),
                  ),
                if (id == 'paypal' && selected)
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(AppSpacing.md),
                    decoration: const BoxDecoration(
                      color: Color(0xFFEFF6FF),
                      border: Border(top: BorderSide(color: Color(0xFFBFDBFE))),
                      borderRadius: BorderRadius.vertical(bottom: Radius.circular(AppRadius.md)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: const [
                        Row(
                          children: [
                            Icon(Icons.account_balance_wallet_outlined, size: 14, color: Color(0xFF1D4ED8)),
                            SizedBox(width: 6),
                            Text('Hosted PayPal Express Gateway', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Color(0xFF1E3A8A))),
                          ],
                        ),
                        SizedBox(height: 4),
                        Text(
                          'You will be redirected to PayPal to complete your payment safely using your PayPal balance or registered credit/debit cards.',
                          style: TextStyle(fontSize: 11, color: Color(0xFF1E40AF), height: 1.4),
                        ),
                      ],
                    ),
                  ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _BadgeTag extends StatelessWidget {
  const _BadgeTag(this.text, {this.color});
  final String text;
  final Color? color;

  @override
  Widget build(BuildContext context) {
    final baseColor = color ?? AppColors.slate700;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
      decoration: BoxDecoration(
        color: baseColor.withValues(alpha: 0.1),
        border: Border.all(color: baseColor.withValues(alpha: 0.2)),
        borderRadius: BorderRadius.circular(4),
      ),
      child: Text(
        text,
        style: TextStyle(fontSize: 9, fontWeight: FontWeight.w800, color: baseColor),
      ),
    );
  }
}



class _SummaryRow extends StatelessWidget {
  const _SummaryRow({required this.label, required this.value, this.isBold = false});
  final String label;
  final String value;
  final bool isBold;

  @override
  Widget build(BuildContext context) {
    final style = TextStyle(
      fontSize: isBold ? 15 : 13,
      fontWeight: isBold ? FontWeight.w800 : FontWeight.w500,
      color: AppColors.ink,
    );
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: style.copyWith(color: isBold ? AppColors.ink : AppColors.slate500)),
        Text(value, style: style),
      ],
    );
  }
}
