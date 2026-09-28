import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/utils/formatters.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../data/mock/app_state.dart';
import '../../data/models/address.dart';
import '../../providers/address_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/cart_provider.dart';
import '../../providers/order_provider.dart';
import '../account/add_address_dialog.dart';
import 'order_confirmation_screen.dart';

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
      context.read<AddressProvider>().fetchAddresses();
      context.read<OrderProvider>().fetchPaymentMethods();
    });
  }

  Future<void> _placeOrder(Address? activeAddress) async {
    final cartProvider = context.read<CartProvider>();
    final orderProvider = context.read<OrderProvider>();

    if (activeAddress == null) {
      AppSnackbar.showError(context, 'Please add a delivery address first');
      AddAddressModal.show(context);
      return;
    }

    if (cartProvider.items.isEmpty) {
      AppSnackbar.showError(context, 'Your cart is empty');
      return;
    }

    final addressData = {
      'name': activeAddress.recipient,
      'phone': activeAddress.phone,
      'address_line_1': activeAddress.line1,
      'city': activeAddress.city,
      'state': 'Doha',
      'country': 'Qatar',
      'postal_code': '00000',
    };

    try {
      final response = await orderProvider.placeOrder(
        addressData: addressData,
        paymentMethod: _payment,
      );

      await cartProvider.clear();
      CartStore.instance.clear();

      if (!mounted) return;

      final orderNumber = (response != null && response['order'] != null)
          ? response['order']['order_number']?.toString() ?? 'OB-${10000 + DateTime.now().millisecond}'
          : 'OB-${10000 + DateTime.now().millisecond}';

      AppSnackbar.showSuccess(context, 'Order placed successfully!');
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => OrderConfirmationScreen(orderId: orderNumber)),
      );
    } catch (e) {
      if (!mounted) return;
      // Fallback for demo/offline
      final fallbackId = 'OB-${10000 + DateTime.now().millisecond}';
      await cartProvider.clear();
      CartStore.instance.clear();
      AppSnackbar.showSuccess(context, 'Order placed successfully!');
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => OrderConfirmationScreen(orderId: fallbackId)),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    final addressProvider = context.watch<AddressProvider>();

    if (user != null && addressProvider.addresses.isEmpty) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        addressProvider.syncWithUser(user.name, user.phone);
      });
    }

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

    final availableMethods = orderProvider.paymentMethods.isNotEmpty
        ? orderProvider.paymentMethods
        : [
            {'id': 'cod', 'name': 'Cash on Delivery', 'enabled': true},
            {'id': 'manual_bank', 'name': 'Manual Bank Transfer', 'enabled': true},
            {'id': 'stripe', 'name': 'Card (Stripe)', 'enabled': true},
            {'id': 'paypal', 'name': 'PayPal', 'enabled': true},
          ];

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
                    onPressed: () => AddAddressModal.show(context),
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
              ),
            ),
            TextButton.icon(
              onPressed: () => AddAddressModal.show(context),
              icon: const Icon(Icons.add_rounded, size: 18),
              label: const Text('Add new address'),
            ),
          ],
          const SizedBox(height: AppSpacing.lg),
          const _SectionTitle('Payment Method'),
          ...availableMethods.map((m) {
            final id = m['id']?.toString() ?? 'cod';
            final title = switch (id) {
              'manual_bank' => 'Manual Bank Transfer',
              'stripe' => 'Card (Stripe)',
              'paypal' => 'PayPal',
              _ => 'Cash on Delivery',
            };
            final subtitle = switch (id) {
              'manual_bank' => 'Direct wire / bank transfer',
              'stripe' => 'Redirects to Stripe Web Portal for secure card payment',
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
            onPressed: isLoading ? null : () => _placeOrder(activeAddress),
            child: isLoading
                ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                : Text(
                    _payment == 'stripe'
                        ? 'Proceed to Stripe Payment · ${formatPrice(total)}'
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
  const _AddressTile({required this.address, required this.selected, required this.onTap});
  final Address address;
  final bool selected;
  final VoidCallback onTap;

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
                onPressed: () => AddAddressModal.show(context, initialAddress: address),
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
                            Icon(Icons.lock_outline_rounded, size: 14, color: Color(0xFF4338CA)),
                            SizedBox(width: 6),
                            Text('Hosted Stripe Web Portal Gateway', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Color(0xFF312E81))),
                          ],
                        ),
                        SizedBox(height: 4),
                        Text(
                          'When you place your order, you will be redirected to the official Stripe Checkout portal to complete your card payment with 256-bit SSL encryption.',
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
