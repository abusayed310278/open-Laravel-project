import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../../core/network/api_client.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/empty_view.dart';
import '../../core/widgets/loading_view.dart';
import '../products/products_screen.dart';

class CartScreen extends StatefulWidget {
  final void Function(int index)? onNavigateTab;
  const CartScreen({super.key, this.onNavigateTab});

  @override
  State<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends State<CartScreen> {
  final ApiClient _apiClient = ApiClient();
  final NumberFormat _currencyFormat = NumberFormat('#,##0.00', 'en_US');

  bool _isLoading = true;
  String? _errorMessage;
  List<Map<String, dynamic>> _items = [];
  double _subtotal = 0.0;
  double _shipping = 0.0;
  double _total = 0.0;

  @override
  void initState() {
    super.initState();
    _fetchCart();
  }

  Future<void> _fetchCart() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final response = await _apiClient.get('/cart');
      if (!mounted) return;

      if (response is Map<String, dynamic>) {
        final rawItems = response['items'] as List? ?? [];
        setState(() {
          _items = rawItems.map((e) => Map<String, dynamic>.from(e as Map)).toList();
          _subtotal = (response['subtotal'] is num) ? (response['subtotal'] as num).toDouble() : 0.0;
          _shipping = (response['shipping'] is num) ? (response['shipping'] as num).toDouble() : 0.0;
          _total = (response['total'] is num) ? (response['total'] as num).toDouble() : 0.0;
          _isLoading = false;
        });
      } else {
        setState(() {
          _items = [];
          _isLoading = false;
        });
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _errorMessage = e.toString();
        _isLoading = false;
      });
    }
  }

  Future<void> _updateQuantity(int productId, int quantity) async {
    try {
      await _apiClient.put(
        '/cart/update/$productId',
        body: {'quantity': quantity},
      );
      _fetchCart();
    } catch (e) {
      if (!mounted) return;
      AppSnackBar.error(context, e.toString());
    }
  }

  Future<void> _removeItem(int productId) async {
    try {
      await _apiClient.delete('/cart/remove/$productId');
      if (!mounted) return;
      AppSnackBar.success(context, 'Item removed from cart');
      _fetchCart();
    } catch (e) {
      if (!mounted) return;
      AppSnackBar.error(context, e.toString());
    }
  }

  Future<void> _clearCart() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Clear Cart'),
        content: const Text('Are you sure you want to remove all items from your cart?'),
        actions: [
          TextButton(onPressed: () => Navigator.of(ctx).pop(false), child: const Text('Cancel')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.danger),
            onPressed: () => Navigator.of(ctx).pop(true),
            child: const Text('Clear All'),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    try {
      await _apiClient.post('/cart/clear');
      if (!mounted) return;
      AppSnackBar.success(context, 'Cart cleared');
      _fetchCart();
    } catch (e) {
      if (!mounted) return;
      AppSnackBar.error(context, e.toString());
    }
  }

  void _proceedToCheckout() {
    AppSnackBar.info(
      context,
      'Proceeding to secure checkout...',
      title: 'Checkout',
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        title: const Text('Shopping Cart'),
        actions: [
          if (_items.isNotEmpty)
            IconButton(
              icon: const Icon(Icons.delete_sweep_outlined, color: AppColors.danger),
              tooltip: 'Clear Cart',
              onPressed: _clearCart,
            ),
        ],
      ),
      body: _buildBody(),
      bottomNavigationBar: (_items.isNotEmpty && !_isLoading) ? _buildCheckoutBar() : null,
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const LoadingView(message: 'Loading cart...');
    }

    if (_errorMessage != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(AppSpacing.xl),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline_rounded, size: 48, color: AppColors.danger),
              const SizedBox(height: AppSpacing.md),
              Text(
                _errorMessage!,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 14, color: AppColors.slate600),
              ),
              const SizedBox(height: AppSpacing.lg),
              ElevatedButton.icon(
                onPressed: _fetchCart,
                icon: const Icon(Icons.refresh_rounded, size: 18),
                label: const Text('Retry'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.brand500,
                  foregroundColor: Colors.white,
                ),
              ),
            ],
          ),
        ),
      );
    }

    if (_items.isEmpty) {
      return EmptyView(
        icon: Icons.shopping_cart_outlined,
        title: 'Your cart is empty',
        subtitle: 'Looks like you haven\'t added any items to your cart yet.',
        actionLabel: 'Explore Products',
        onAction: () {
          if (widget.onNavigateTab != null) {
            widget.onNavigateTab!(1); // Navigate to Categories
          } else {
            Navigator.of(context).push(
              MaterialPageRoute(builder: (_) => const ProductsScreen()),
            );
          }
        },
      );
    }

    return RefreshIndicator(
      onRefresh: _fetchCart,
      color: AppColors.brand500,
      child: ListView.separated(
        padding: const EdgeInsets.all(AppSpacing.lg),
        itemCount: _items.length,
        separatorBuilder: (context, index) => const SizedBox(height: AppSpacing.md),
        itemBuilder: (context, index) {
          final item = _items[index];
          final product = item['product'] as Map<String, dynamic>? ?? {};
          final productId = (item['product_id'] as num?)?.toInt() ?? 0;
          final quantity = (item['quantity'] as num?)?.toInt() ?? 1;
          final title = product['name']?.toString() ?? product['title']?.toString() ?? 'Product';
          final price = (product['price'] as num?)?.toDouble() ?? 0.0;
          final imageUrl = product['thumbnail_url']?.toString() ?? product['image_url']?.toString();

          return Container(
            padding: const EdgeInsets.all(AppSpacing.md),
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(AppRadius.lg),
              border: Border.all(color: AppColors.slate200.withValues(alpha: 0.8)),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.02),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Product Thumbnail
                Container(
                  width: 72,
                  height: 72,
                  decoration: BoxDecoration(
                    color: AppColors.slate100,
                    borderRadius: BorderRadius.circular(AppRadius.md),
                  ),
                  clipBehavior: Clip.antiAlias,
                  child: (imageUrl != null && imageUrl.isNotEmpty)
                      ? Image.network(
                          imageUrl,
                          fit: BoxFit.cover,
                          errorBuilder: (context, error, stackTrace) => const Icon(Icons.image_not_supported_outlined, color: AppColors.slate400),
                        )
                      : const Icon(Icons.inventory_2_outlined, color: AppColors.slate400),
                ),
                const SizedBox(width: AppSpacing.md),

                // Product Info & Controls
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Expanded(
                            child: Text(
                              title,
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.w700,
                                color: AppColors.ink,
                              ),
                            ),
                          ),
                          IconButton(
                            icon: const Icon(Icons.close, size: 18, color: AppColors.slate400),
                            padding: EdgeInsets.zero,
                            constraints: const BoxConstraints(),
                            onPressed: () => _removeItem(productId),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '${_currencyFormat.format(price)} QAR',
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF000080),
                        ),
                      ),
                      const SizedBox(height: AppSpacing.sm),

                      // Quantity Stepper
                      Row(
                        children: [
                          _buildQuantityButton(
                            icon: Icons.remove,
                            onPressed: quantity > 1
                                ? () => _updateQuantity(productId, quantity - 1)
                                : () => _removeItem(productId),
                          ),
                          Padding(
                            padding: const EdgeInsets.symmetric(horizontal: 14),
                            child: Text(
                              '$quantity',
                              style: const TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.w700,
                                color: AppColors.ink,
                              ),
                            ),
                          ),
                          _buildQuantityButton(
                            icon: Icons.add,
                            onPressed: () => _updateQuantity(productId, quantity + 1),
                          ),
                          const Spacer(),
                          Text(
                            '${_currencyFormat.format(price * quantity)} QAR',
                            style: const TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.w700,
                              color: AppColors.slate600,
                            ),
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
    );
  }

  Widget _buildQuantityButton({required IconData icon, required VoidCallback onPressed}) {
    return InkWell(
      onTap: onPressed,
      borderRadius: BorderRadius.circular(6),
      child: Container(
        width: 28,
        height: 28,
        decoration: BoxDecoration(
          color: AppColors.slate100,
          borderRadius: BorderRadius.circular(6),
          border: Border.all(color: AppColors.slate200),
        ),
        child: Icon(icon, size: 14, color: AppColors.ink),
      ),
    );
  }

  Widget _buildCheckoutBar() {
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
                const Text('Subtotal', style: TextStyle(fontSize: 13, color: AppColors.slate500)),
                Text('${_currencyFormat.format(_subtotal)} QAR', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.ink)),
              ],
            ),
            const SizedBox(height: 4),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('Estimated Shipping', style: TextStyle(fontSize: 13, color: AppColors.slate500)),
                Text(
                  _shipping == 0 ? 'Free' : '${_currencyFormat.format(_shipping)} QAR',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: _shipping == 0 ? AppColors.success : AppColors.ink,
                  ),
                ),
              ],
            ),
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 8),
              child: Divider(color: AppColors.slate200, height: 1),
            ),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Total',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.ink),
                ),
                Text(
                  '${_currencyFormat.format(_total > 0 ? _total : _subtotal)} QAR',
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF000080),
                  ),
                ),
              ],
            ),
            const SizedBox(height: AppSpacing.md),
            CustomButton(
              text: 'Proceed to Checkout',
              backgroundColor: const Color(0xFFF59E0B),
              onPressed: _proceedToCheckout,
            ),
          ],
        ),
      ),
    );
  }
}
