import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../core/widgets/empty_view.dart';
import '../../core/widgets/loading_view.dart';
import '../../core/widgets/status_badge.dart';
import '../../providers/seller_product_provider.dart';
import 'add_product_screen.dart';

class ProductsScreen extends StatefulWidget {
  final bool embedded;
  const ProductsScreen({super.key, this.embedded = false});

  @override
  State<ProductsScreen> createState() => _ProductsScreenState();
}

class _ProductsScreenState extends State<ProductsScreen> {
  final _searchController = TextEditingController();
  final _currencyFormat = NumberFormat('#,##0.00', 'en_US');

  static const List<Map<String, String>> _statusTabs = [
    {'key': 'all', 'label': 'All'},
    {'key': 'published', 'label': 'Published'},
    {'key': 'draft', 'label': 'Draft'},
    {'key': 'pending_approval', 'label': 'Under Review'},
    {'key': 'rejected', 'label': 'Rejected'},
  ];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<SellerProductProvider>().fetchProducts();
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final productProvider = context.watch<SellerProductProvider>();

    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: widget.embedded
          ? null
          : AppBar(
              title: const Text('Product Listings'),
              actions: [
                IconButton(
                  icon: const Icon(Icons.add_circle, color: AppColors.brand500, size: 28),
                  onPressed: () {
                    Navigator.of(context).push(
                      MaterialPageRoute(builder: (_) => const AddProductScreen()),
                    );
                  },
                ),
              ],
            ),
      body: Column(
        children: [
          // 1. Search Bar
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.sm),
            child: TextField(
              controller: _searchController,
              onSubmitted: (val) => productProvider.searchProducts(val),
              decoration: InputDecoration(
                hintText: 'Search products by title or SKU...',
                prefixIcon: const Icon(Icons.search, size: 20, color: AppColors.slate400),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear, size: 18, color: AppColors.slate400),
                        onPressed: () {
                          _searchController.clear();
                          productProvider.searchProducts('');
                        },
                      )
                    : null,
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                fillColor: AppColors.surface,
              ),
            ),
          ),

          // 2. Status Segment Tabs
          SizedBox(
            height: 44,
            child: ListView.separated(
              padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
              scrollDirection: Axis.horizontal,
              itemCount: _statusTabs.length,
              separatorBuilder: (_, __) => const SizedBox(width: 8),
              itemBuilder: (context, index) {
                final tab = _statusTabs[index];
                final key = tab['key']!;
                final label = tab['label']!;
                final isSelected = productProvider.selectedStatus == key;
                final count = productProvider.counts[key] ?? 0;

                return GestureDetector(
                  onTap: () => productProvider.setStatus(key),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                    decoration: BoxDecoration(
                      color: isSelected ? AppColors.brand500 : AppColors.surface,
                      borderRadius: BorderRadius.circular(AppRadius.md),
                      border: Border.all(
                        color: isSelected ? AppColors.brand500 : AppColors.slate200,
                      ),
                    ),
                    alignment: Alignment.center,
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          label,
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                            color: isSelected ? Colors.white : AppColors.slate700,
                          ),
                        ),
                        if (count > 0) ...[
                          const SizedBox(width: 6),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(
                              color: isSelected ? Colors.white.withOpacity(0.25) : AppColors.slate100,
                              borderRadius: BorderRadius.circular(AppRadius.sm),
                            ),
                            child: Text(
                              '$count',
                              style: TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.w700,
                                color: isSelected ? Colors.white : AppColors.slate600,
                              ),
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
          const SizedBox(height: AppSpacing.sm),

          // 3. Products List View
          Expanded(
            child: RefreshIndicator(
              onRefresh: () => productProvider.fetchProducts(),
              color: AppColors.brand500,
              child: productProvider.isLoading && productProvider.products.isEmpty
                  ? const LoadingView(message: 'Loading listings...')
                  : productProvider.products.isEmpty
                      ? const EmptyView(
                          icon: Icons.inventory_2_outlined,
                          title: 'No Products Found',
                          subtitle: 'Create a new listing to start selling.',
                        )
                      : ListView.separated(
                          padding: const EdgeInsets.all(AppSpacing.lg),
                          itemCount: productProvider.products.length,
                          separatorBuilder: (_, __) => const SizedBox(height: AppSpacing.md),
                          itemBuilder: (context, index) {
                            final product = productProvider.products[index];
                            return _buildProductCard(context, product, productProvider);
                          },
                        ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildProductCard(BuildContext context, dynamic product, SellerProductProvider provider) {
    return Container(
      padding: const EdgeInsets.all(AppSpacing.md),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.slate200.withOpacity(0.8)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.02),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 68,
                height: 68,
                decoration: BoxDecoration(
                  color: AppColors.slate100,
                  borderRadius: BorderRadius.circular(AppRadius.md),
                ),
                clipBehavior: Clip.antiAlias,
                child: product.thumbnail != null
                    ? Image.network(
                        product.thumbnail!,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => const Icon(Icons.inventory_2, color: AppColors.slate400, size: 28),
                      )
                    : const Icon(Icons.inventory_2, color: AppColors.slate400, size: 28),
              ),
              const SizedBox(width: AppSpacing.md),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      product.title,
                      style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.ink),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        if (product.categoryName != null) ...[
                          Text(
                            product.categoryName!,
                            style: const TextStyle(fontSize: 11, color: AppColors.slate500),
                          ),
                          const SizedBox(width: 6),
                          const Text('•', style: TextStyle(color: AppColors.slate300)),
                          const SizedBox(width: 6),
                        ],
                        Text(
                          'Stock: ${product.quantity}',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: product.quantity > 0 ? AppColors.slate600 : AppColors.danger,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        Text(
                          '${_currencyFormat.format(product.price)} QAR',
                          style: const TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.w800,
                            color: AppColors.brand600,
                          ),
                        ),
                        if (product.comparePrice != null) ...[
                          const SizedBox(width: 6),
                          Text(
                            '${_currencyFormat.format(product.comparePrice)} QAR',
                            style: const TextStyle(
                              fontSize: 11,
                              color: AppColors.slate400,
                              decoration: TextDecoration.lineThrough,
                            ),
                          ),
                        ],
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
          const Divider(height: 20, color: AppColors.slate100),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  StatusBadge(status: product.publicationStatus ?? product.status, isSmall: true),
                  if (product.condition != null) ...[
                    const SizedBox(width: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppColors.slate100,
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        product.conditionLabel ?? product.condition,
                        style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppColors.slate600),
                      ),
                    ),
                  ],
                ],
              ),
              Row(
                children: [
                  const Text('Live', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.slate600)),
                  Switch.adaptive(
                    value: product.isPublished,
                    activeColor: AppColors.brand500,
                    onChanged: (_) async {
                      final success = await provider.togglePublish(product.id);
                      if (!success && context.mounted) {
                        AppSnackBar.error(
                          context,
                          provider.errorMessage ?? 'Unable to publish listing.',
                        );
                      }
                    },
                  ),
                  IconButton(
                    icon: const Icon(Icons.delete_outline, size: 20, color: AppColors.slate400),
                    onPressed: () => _confirmDelete(context, product.id, product.title, provider),
                  ),
                ],
              ),
            ],
          ),
        ],
      ),
    );
  }

  void _confirmDelete(BuildContext context, int id, String title, SellerProductProvider provider) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Delete Listing'),
        content: Text('Are you sure you want to permanently delete "$title"?'),
        actions: [
          TextButton(onPressed: () => Navigator.of(ctx).pop(), child: const Text('Cancel')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.danger),
            onPressed: () async {
              Navigator.of(ctx).pop();
              await provider.deleteProduct(id);
            },
            child: const Text('Delete'),
          ),
        ],
      ),
    );
  }
}
