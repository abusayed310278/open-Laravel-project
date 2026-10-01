import 'package:flutter/material.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../core/widgets/empty_state.dart';
import '../../core/widgets/product_card.dart';
import '../../data/models/product.dart';
import 'product_detail_screen.dart';

class ProductListScreen extends StatefulWidget {
  const ProductListScreen({super.key, required this.title, required this.products});

  final String title;
  final List<Product> products;

  @override
  State<ProductListScreen> createState() => _ProductListScreenState();
}

enum _SortOption { relevance, priceLowHigh, priceHighLow, rating }

class _ProductListScreenState extends State<ProductListScreen> {
  _SortOption _sort = _SortOption.relevance;

  List<Product> get _sorted {
    final list = [...widget.products];
    switch (_sort) {
      case _SortOption.priceLowHigh:
        list.sort((a, b) => a.price.compareTo(b.price));
      case _SortOption.priceHighLow:
        list.sort((a, b) => b.price.compareTo(a.price));
      case _SortOption.rating:
        list.sort((a, b) => b.rating.compareTo(a.rating));
      case _SortOption.relevance:
        break;
    }
    return list;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.title),
        actions: [
          IconButton(
            icon: const Icon(Icons.tune_rounded),
            onPressed: () => AppSnackbar.info(context, 'Filters coming soon'),
          ),
        ],
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.sm),
            child: Row(
              children: [
                Text('${widget.products.length} results', style: const TextStyle(fontSize: 12, color: AppColors.slate500)),
                const Spacer(),
                DropdownButton<_SortOption>(
                  value: _sort,
                  underline: const SizedBox.shrink(),
                  style: const TextStyle(fontSize: 12, color: AppColors.ink, fontWeight: FontWeight.w600),
                  items: const [
                    DropdownMenuItem(value: _SortOption.relevance, child: Text('Relevance')),
                    DropdownMenuItem(value: _SortOption.priceLowHigh, child: Text('Price: Low to High')),
                    DropdownMenuItem(value: _SortOption.priceHighLow, child: Text('Price: High to Low')),
                    DropdownMenuItem(value: _SortOption.rating, child: Text('Top rated')),
                  ],
                  onChanged: (v) => setState(() => _sort = v ?? _sort),
                ),
              ],
            ),
          ),
          Expanded(
            child: widget.products.isEmpty
                ? const EmptyState(
                    icon: Icons.inventory_2_outlined,
                    title: 'No products found',
                    message: 'Try browsing a different category or check back later.',
                  )
                : GridView.builder(
                    padding: const EdgeInsets.fromLTRB(AppSpacing.lg, 0, AppSpacing.lg, AppSpacing.xl),
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 2,
                      mainAxisSpacing: AppSpacing.md,
                      crossAxisSpacing: AppSpacing.md,
                      childAspectRatio: 0.62,
                    ),
                    itemCount: _sorted.length,
                    itemBuilder: (context, i) {
                      final product = _sorted[i];
                      return ProductCard(
                        product: product,
                        onTap: () => Navigator.of(context).push(
                          MaterialPageRoute(builder: (_) => ProductDetailScreen(product: product)),
                        ),
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }
}
