import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_spacing.dart';
import '../../core/widgets/empty_state.dart';
import '../../core/widgets/product_card.dart';
import '../../data/mock/app_state.dart';
import '../../data/mock/mock_data.dart';
import '../../data/models/product.dart';
import '../../providers/product_provider.dart';
import '../product/product_detail_screen.dart';

class WishlistScreen extends StatelessWidget {
  const WishlistScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final productProvider = context.watch<ProductProvider>();

    return Scaffold(
      appBar: AppBar(title: const Text('Wishlist')),
      body: AnimatedBuilder(
        animation: WishlistStore.instance,
        builder: (context, _) {
          final allProductsMap = <String, Product>{};

          for (final p in productProvider.products) {
            allProductsMap[p.id] = p;
          }
          for (final p in MockData.products) {
            if (!allProductsMap.containsKey(p.id)) {
              allProductsMap[p.id] = p;
            }
          }

          final saved = allProductsMap.values
              .where((p) => WishlistStore.instance.contains(p.id))
              .toList();

          if (saved.isEmpty) {
            return const EmptyState(
              icon: Icons.favorite_border_rounded,
              title: 'Your wishlist is empty',
              message: 'Tap the heart icon on any product to save it here for later.',
            );
          }
          return GridView.builder(
            padding: const EdgeInsets.all(AppSpacing.lg),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              mainAxisSpacing: AppSpacing.md,
              crossAxisSpacing: AppSpacing.md,
              childAspectRatio: 0.62,
            ),
            itemCount: saved.length,
            itemBuilder: (context, i) => ProductCard(
              product: saved[i],
              onTap: () => Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => ProductDetailScreen(product: saved[i])),
              ),
            ),
          );
        },
      ),
    );
  }
}

