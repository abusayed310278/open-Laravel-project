import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/utils/formatters.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../core/widgets/empty_state.dart';
import '../../navigation/app_shell.dart';
import '../../providers/auth_provider.dart';
import '../../providers/cart_provider.dart';
import '../auth/login_screen.dart';
import '../checkout/checkout_screen.dart';

class CartScreen extends StatefulWidget {
  const CartScreen({super.key});

  @override
  State<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends State<CartScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => context.read<CartProvider>().fetchCart());
  }

  @override
  Widget build(BuildContext context) {
    final cartProvider = context.watch<CartProvider>();
    final items = cartProvider.items;

    return Scaffold(
      appBar: AppBar(title: const Text('My Cart')),
      body: items.isEmpty
          ? EmptyState(
              icon: Icons.shopping_bag_outlined,
              title: 'Your cart is empty',
              message: 'Browse the catalog and add items you like to your cart.',
              actionLabel: 'Start shopping',
              onAction: () => Navigator.of(context).pushAndRemoveUntil(
                MaterialPageRoute(builder: (_) => AppShell()),
                (route) => false,
              ),
            )
          : Column(
              children: [
                Expanded(
                  child: ListView.separated(
                    padding: const EdgeInsets.all(AppSpacing.lg),
                    itemCount: items.length,
                    separatorBuilder: (_, _) => const SizedBox(height: AppSpacing.md),
                    itemBuilder: (context, i) {
                      final item = items[i];
                      return Container(
                        padding: const EdgeInsets.all(AppSpacing.md),
                        decoration: BoxDecoration(
                          color: AppColors.surface,
                          border: Border.all(color: AppColors.slate200),
                          borderRadius: BorderRadius.circular(AppRadius.md),
                        ),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            ClipRRect(
                              borderRadius: BorderRadius.circular(AppRadius.sm),
                              child: Image.network(
                                item.product.imageUrl,
                                width: 72,
                                height: 72,
                                fit: BoxFit.cover,
                                errorBuilder: (_, __, ___) => const Icon(Icons.image_not_supported),
                              ),
                            ),
                            const SizedBox(width: AppSpacing.md),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(item.product.title, maxLines: 2, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                                  const SizedBox(height: 4),
                                  Text(formatPrice(item.product.price), style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14, color: AppColors.ink)),
                                  const SizedBox(height: AppSpacing.sm),
                                  Row(
                                    children: [
                                      _QtyButton(
                                        icon: Icons.remove_rounded,
                                        onTap: () => context.read<CartProvider>().updateQuantity(item.product.id, item.quantity - 1),
                                      ),
                                      SizedBox(width: 32, child: Text('${item.quantity}', textAlign: TextAlign.center, style: const TextStyle(fontWeight: FontWeight.w700))),
                                      _QtyButton(
                                        icon: Icons.add_rounded,
                                        onTap: () => context.read<CartProvider>().updateQuantity(item.product.id, item.quantity + 1),
                                      ),
                                      const Spacer(),
                                      IconButton(
                                        icon: const Icon(Icons.delete_outline_rounded, size: 20, color: AppColors.slate400),
                                        onPressed: () {
                                          final product = item.product;
                                          context.read<CartProvider>().removeFromCart(product.id);
                                          AppSnackbar.show(
                                            context,
                                            '${product.title} removed from cart',
                                            icon: Icons.delete_outline_rounded,
                                          );
                                        },
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      );
                    },
                  ),
                ),
                _CartSummary(subtotal: cartProvider.subtotal),
              ],
            ),
    );
  }
}

class _QtyButton extends StatelessWidget {
  const _QtyButton({required this.icon, required this.onTap, this.enabled = true});
  final IconData icon;
  final VoidCallback onTap;
  final bool enabled;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: enabled ? onTap : null,
        borderRadius: BorderRadius.circular(6),
        child: Container(
          width: 34,
          height: 34,
          alignment: Alignment.center,
          decoration: BoxDecoration(
            border: Border.all(color: AppColors.slate200),
            borderRadius: BorderRadius.circular(6),
            color: enabled ? AppColors.surface : AppColors.slate50,
          ),
          child: Icon(
            icon,
            size: 16,
            color: enabled ? AppColors.ink : AppColors.slate300,
          ),
        ),
      ),
    );
  }
}

class _CartSummary extends StatelessWidget {
  const _CartSummary({required this.subtotal});
  final double subtotal;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(AppSpacing.lg),
      decoration: const BoxDecoration(
        color: AppColors.surface,
        border: Border(top: BorderSide(color: AppColors.slate200)),
      ),
      child: SafeArea(
        top: false,
        child: Column(
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('Subtotal', style: TextStyle(fontSize: 13, color: AppColors.slate500)),
                Text(formatPrice(subtotal), style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
              ],
            ),
            const SizedBox(height: 4),
            const Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('Shipping', style: TextStyle(fontSize: 13, color: AppColors.slate500)),
                Text('Calculated at checkout', style: TextStyle(fontSize: 12, color: AppColors.slate400)),
              ],
            ),
            const SizedBox(height: AppSpacing.md),
            ElevatedButton(
              onPressed: () {
                final authProvider = context.read<AuthProvider>();
                if (!authProvider.isAuthenticated) {
                  AppSnackbar.info(context, 'Please sign in to proceed to checkout');
                  Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => const LoginScreen()),
                  );
                  return;
                }
                Navigator.of(context).push(MaterialPageRoute(builder: (_) => const CheckoutScreen()));
              },
              child: Text('Checkout · ${formatPrice(subtotal)}'),
            ),
          ],
        ),
      ),
    );
  }
}
