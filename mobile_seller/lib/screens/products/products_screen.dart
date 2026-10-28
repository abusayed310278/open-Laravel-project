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
              separatorBuilder: (_, _) => const SizedBox(width: 8),
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
                              color: isSelected ? Colors.white.withValues(alpha: 0.25) : AppColors.slate100,
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
                          separatorBuilder: (_, _) => const SizedBox(height: AppSpacing.md),
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
    final qty = product.quantity is int ? product.quantity : int.tryParse(product.quantity.toString()) ?? 0;

    Color stockColor = AppColors.success;
    Color stockBg = AppColors.successBg;
    String stockText = '$qty in stock';
    if (qty <= 0) {
      stockColor = AppColors.danger;
      stockBg = AppColors.dangerBg;
      stockText = 'Out of Stock';
    } else if (qty <= 5) {
      stockColor = AppColors.warning;
      stockBg = AppColors.warningBg;
      stockText = '$qty left (Low)';
    }

    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.slate200.withValues(alpha: 0.85)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(AppSpacing.md),
        child: Column(
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Product Thumbnail
                Container(
                  width: 74,
                  height: 74,
                  decoration: BoxDecoration(
                    color: AppColors.slate100,
                    borderRadius: BorderRadius.circular(AppRadius.md),
                    border: Border.all(color: AppColors.slate200.withValues(alpha: 0.8)),
                  ),
                  clipBehavior: Clip.antiAlias,
                  child: product.thumbnail != null && product.thumbnail.toString().isNotEmpty
                      ? Image.network(
                          product.thumbnail!,
                          fit: BoxFit.cover,
                          loadingBuilder: (context, child, loadingProgress) {
                            if (loadingProgress == null) return child;
                            return Container(
                              color: AppColors.slate100,
                              alignment: Alignment.center,
                              child: const SizedBox(
                                width: 18,
                                height: 18,
                                child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.brand500),
                              ),
                            );
                          },
                          errorBuilder: (_, _, _) => Container(
                            color: AppColors.slate100,
                            alignment: Alignment.center,
                            child: const Icon(Icons.inventory_2_outlined, color: AppColors.slate400, size: 28),
                          ),
                        )
                      : Container(
                          color: AppColors.slate100,
                          alignment: Alignment.center,
                          child: const Icon(Icons.inventory_2_outlined, color: AppColors.slate400, size: 28),
                        ),
                ),
                const SizedBox(width: AppSpacing.md),

                // Product Information
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        product.title,
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          color: AppColors.ink,
                          height: 1.25,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 5),
                      Row(
                        children: [
                          if (product.categoryName != null) ...[
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: AppColors.slate100,
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                product.categoryName!,
                                style: const TextStyle(
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.w600,
                                  color: AppColors.slate600,
                                ),
                              ),
                            ),
                            const SizedBox(width: 6),
                          ],
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(
                              color: stockBg,
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Container(
                                  width: 5,
                                  height: 5,
                                  decoration: BoxDecoration(color: stockColor, shape: BoxShape.circle),
                                ),
                                const SizedBox(width: 4),
                                Text(
                                  stockText,
                                  style: TextStyle(
                                    fontSize: 10.5,
                                    fontWeight: FontWeight.w700,
                                    color: stockColor,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.baseline,
                        textBaseline: TextBaseline.alphabetic,
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
            const SizedBox(height: AppSpacing.md),
            Container(
              padding: const EdgeInsets.only(top: AppSpacing.sm),
              decoration: const BoxDecoration(
                border: Border(top: BorderSide(color: AppColors.slate100, width: 1)),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: Wrap(
                      spacing: 6,
                      runSpacing: 4,
                      crossAxisAlignment: WrapCrossAlignment.center,
                      children: [
                        StatusBadge(status: product.publicationStatus ?? product.status ?? 'draft', isSmall: true),
                        if (product.condition != null)
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                            decoration: BoxDecoration(
                              color: AppColors.slate100,
                              borderRadius: BorderRadius.circular(AppRadius.sm),
                            ),
                            child: Text(
                              product.conditionLabel ?? product.condition!,
                              style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppColors.slate600),
                            ),
                          ),
                      ],
                    ),
                  ),
                  InkWell(
                    onTap: () => _showUpdateStockDialog(context, product, provider),
                    borderRadius: BorderRadius.circular(AppRadius.sm),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
                      decoration: BoxDecoration(
                        color: AppColors.brand50,
                        borderRadius: BorderRadius.circular(AppRadius.sm),
                        border: Border.all(color: AppColors.brand200),
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.tune_rounded, size: 13, color: AppColors.brand700),
                          SizedBox(width: 4),
                          Text(
                            'Adjust',
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w700,
                              color: AppColors.brand700,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  InkWell(
                    onTap: () => _confirmDelete(context, product.id, product.title, provider),
                    borderRadius: BorderRadius.circular(AppRadius.sm),
                    child: Container(
                      padding: const EdgeInsets.all(5),
                      decoration: BoxDecoration(
                        color: AppColors.dangerBg.withValues(alpha: 0.6),
                        borderRadius: BorderRadius.circular(AppRadius.sm),
                      ),
                      child: const Icon(Icons.delete_outline, size: 18, color: AppColors.danger),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showUpdateStockDialog(BuildContext context, dynamic product, SellerProductProvider provider) {
    final qtyController = TextEditingController(text: product.quantity?.toString() ?? '0');
    final priceController = TextEditingController(text: product.price?.toString() ?? '0.00');

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.lg)),
        titlePadding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.lg, AppSpacing.lg, AppSpacing.xs),
        contentPadding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.sm, AppSpacing.lg, AppSpacing.md),
        actionsPadding: const EdgeInsets.fromLTRB(AppSpacing.lg, 0, AppSpacing.lg, AppSpacing.md),
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Adjust Stock & Price', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700, color: AppColors.ink)),
            const SizedBox(height: 4),
            Text(
              product.title,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500, color: AppColors.slate500),
            ),
          ],
        ),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              TextField(
                controller: qtyController,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(
                  labelText: 'Available Units (Stock)',
                  prefixIcon: Icon(Icons.inventory_2_outlined, size: 20, color: AppColors.slate400),
                ),
              ),
              const SizedBox(height: AppSpacing.md),
              TextField(
                controller: priceController,
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                decoration: const InputDecoration(
                  labelText: 'Unit Price (QAR)',
                  prefixIcon: Icon(Icons.payments_outlined, size: 20, color: AppColors.slate400),
                ),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Cancel', style: TextStyle(color: AppColors.slate600, fontWeight: FontWeight.w600)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.brand600,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
            ),
            onPressed: () async {
              final newQty = int.tryParse(qtyController.text.trim()) ?? 0;
              final newPrice = double.tryParse(priceController.text.trim());
              Navigator.of(ctx).pop();

              final success = await provider.updateStock(
                product.id,
                quantity: newQty,
                price: newPrice,
              );
              if (context.mounted) {
                if (success) {
                  AppSnackBar.success(context, 'Stock & price updated successfully!');
                } else {
                  AppSnackBar.error(context, provider.errorMessage ?? 'Failed to update stock');
                }
              }
            },
            child: const Text('Save Stock', style: TextStyle(fontWeight: FontWeight.w700)),
          ),
        ],
      ),
    );
  }

  void _confirmDelete(BuildContext context, int id, String title, SellerProductProvider provider) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.lg)),
        titlePadding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.lg, AppSpacing.lg, AppSpacing.xs),
        contentPadding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.sm, AppSpacing.lg, AppSpacing.md),
        actionsPadding: const EdgeInsets.fromLTRB(AppSpacing.lg, 0, AppSpacing.lg, AppSpacing.md),
        title: const Text('Delete Listing', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700, color: AppColors.ink)),
        content: Text('Are you sure you want to permanently delete "$title"?', style: const TextStyle(fontSize: 13, color: AppColors.slate600)),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Cancel', style: TextStyle(color: AppColors.slate600, fontWeight: FontWeight.w600)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.danger,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
            ),
            onPressed: () async {
              Navigator.of(ctx).pop();
              await provider.deleteProduct(id);
            },
            child: const Text('Delete', style: TextStyle(fontWeight: FontWeight.w700)),
          ),
        ],
      ),
    );
  }
}
