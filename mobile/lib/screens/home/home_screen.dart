import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/product_card.dart';
import '../../core/widgets/section_header.dart';
import '../../data/mock/mock_data.dart';
import '../../data/models/category.dart';
import '../../data/models/product.dart';
import '../../providers/product_provider.dart';
import '../notifications/notifications_screen.dart';
import '../product/product_detail_screen.dart';
import '../product/product_list_screen.dart';
import '../search/search_screen.dart';
import '../wishlist/wishlist_screen.dart';

class HomeScreen extends StatelessWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final productProvider = context.watch<ProductProvider>();
    final liveProducts = productProvider.products;
    final liveCategories = productProvider.categories;
    final displayProducts = liveProducts.isNotEmpty ? liveProducts : MockData.products;
    final displayCategories = liveCategories.isNotEmpty ? liveCategories : MockData.categories;

    return Scaffold(
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: () async {
            await context.read<ProductProvider>().fetchProducts();
            await context.read<ProductProvider>().fetchCategories();
          },
          child: CustomScrollView(
            slivers: [
              SliverToBoxAdapter(child: _TopBar()),
              SliverToBoxAdapter(child: _SearchBar()),
              const SliverToBoxAdapter(child: SizedBox(height: AppSpacing.lg)),
              SliverToBoxAdapter(child: _HeroBanner()),
              const SliverToBoxAdapter(child: SizedBox(height: AppSpacing.xxl)),
              SliverToBoxAdapter(child: _CategoryStrip(categories: displayCategories)),
              const SliverToBoxAdapter(child: SizedBox(height: AppSpacing.xxl)),
              SliverToBoxAdapter(
                child: SectionHeader(
                  title: 'Openbox Certified',
                  actionLabel: 'See all',
                  onAction: () => Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => ProductListScreen(title: 'Openbox Certified', products: displayProducts),
                    ),
                  ),
                ),
              ),
              const SliverToBoxAdapter(child: SizedBox(height: AppSpacing.md)),
              SliverToBoxAdapter(child: _HorizontalProducts(products: displayProducts.take(6).toList())),
              const SliverToBoxAdapter(child: SizedBox(height: AppSpacing.xxl)),
              SliverToBoxAdapter(
                child: SectionHeader(
                  title: "Today's Deals",
                  actionLabel: 'See all',
                  onAction: () => Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => ProductListScreen(title: "Today's Deals", products: displayProducts),
                    ),
                  ),
                ),
              ),
              const SliverToBoxAdapter(child: SizedBox(height: AppSpacing.md)),
              if (productProvider.isLoading && liveProducts.isEmpty)
                const SliverToBoxAdapter(
                  child: Padding(
                    padding: EdgeInsets.all(AppSpacing.xxl),
                    child: Center(
                      child: CircularProgressIndicator(),
                    ),
                  ),
                )
              else
                SliverPadding(
                  padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
                  sliver: SliverGrid(
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 2,
                      mainAxisSpacing: AppSpacing.md,
                      crossAxisSpacing: AppSpacing.md,
                      childAspectRatio: 0.62,
                    ),
                    delegate: SliverChildBuilderDelegate(
                      (context, i) {
                        final product = displayProducts[i];
                        return ProductCard(
                          product: product,
                          onTap: () => Navigator.of(context).push(
                            MaterialPageRoute(builder: (_) => ProductDetailScreen(product: product)),
                          ),
                        );
                      },
                      childCount: displayProducts.length,
                    ),
                  ),
                ),
              const SliverToBoxAdapter(child: SizedBox(height: AppSpacing.xxxl)),
            ],
          ),
        ),
      ),
    );
  }
}

class _TopBar extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.sm, AppSpacing.lg, 0),
      child: Row(
        children: [
          const Icon(Icons.location_on_outlined, size: 18, color: AppColors.brand600),
          const SizedBox(width: 4),
          const Expanded(
            child: Text.rich(
              TextSpan(
                children: [
                  TextSpan(text: 'Deliver to  ', style: TextStyle(fontSize: 12, color: AppColors.slate500)),
                  TextSpan(text: 'Doha, Qatar', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.ink)),
                ],
              ),
            ),
          ),
          IconButton(
            icon: const Icon(Icons.favorite_border_rounded),
            onPressed: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const WishlistScreen())),
          ),
          IconButton(
            icon: const Icon(Icons.notifications_none_rounded),
            onPressed: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const NotificationsScreen())),
          ),
        ],
      ),
    );
  }
}

class _SearchBar extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
      child: GestureDetector(
        onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const SearchScreen())),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.md),
          decoration: BoxDecoration(
            color: AppColors.slate100,
            borderRadius: BorderRadius.circular(AppRadius.md),
          ),
          child: const Row(
            children: [
              Icon(Icons.search_rounded, color: AppColors.slate400, size: 20),
              SizedBox(width: AppSpacing.sm),
              Text('Search phones, laptops, watches...', style: TextStyle(color: AppColors.slate400, fontSize: 13)),
            ],
          ),
        ),
      ),
    );
  }
}

class _HeroBanner extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
      child: Container(
        height: 150,
        padding: const EdgeInsets.all(AppSpacing.xl),
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            colors: [AppColors.brand600, AppColors.brand400],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(AppRadius.xl),
        ),
        child: Stack(
          children: [
            Positioned(
              right: -10,
              bottom: -20,
              child: Icon(Icons.phone_iphone_rounded, size: 130, color: Colors.white.withValues(alpha: 0.18)),
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Text('Certified Refurbished', style: TextStyle(color: Colors.white70, fontSize: 12, fontWeight: FontWeight.w600)),
                const SizedBox(height: 4),
                const Text(
                  'Up to 30% off\nGrade A devices',
                  style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w800, height: 1.2),
                ),
                const SizedBox(height: AppSpacing.md),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: AppSpacing.md, vertical: 6),
                  decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(AppRadius.pill)),
                  child: const Text('Shop now', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.brand700)),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _CategoryStrip extends StatelessWidget {
  const _CategoryStrip({required this.categories});

  final List<ProductCategory> categories;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 92,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
        itemCount: categories.length,
        separatorBuilder: (_, _) => const SizedBox(width: AppSpacing.lg),
        itemBuilder: (context, i) {
          final category = categories[i];
          return GestureDetector(
            onTap: () {
              final productProvider = context.read<ProductProvider>();
              final liveProds = productProvider.products;
              final filtered = liveProds.where((p) => p.categoryId == category.id).toList();

              Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (_) => ProductListScreen(
                    title: category.name,
                    products: liveProds.isNotEmpty ? filtered : MockData.getProductsForCategory(category.id, category.name),
                  ),
                ),
              );
            },
            child: SizedBox(
              width: 64,
              child: Column(
                children: [
                  Container(
                    width: 56,
                    height: 56,
                    decoration: BoxDecoration(color: AppColors.brand50, borderRadius: BorderRadius.circular(16)),
                    child: category.image.isNotEmpty
                        ? ClipRRect(
                            borderRadius: BorderRadius.circular(16),
                            child: Image.network(
                              category.image,
                              fit: BoxFit.cover,
                              errorBuilder: (_, _, _) => Icon(category.icon, color: AppColors.brand700, size: 26),
                            ),
                          )
                        : Icon(category.icon, color: AppColors.brand700, size: 26),
                  ),
                  const SizedBox(height: AppSpacing.xs),
                  Text(
                    category.name,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.slate700),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}

class _HorizontalProducts extends StatelessWidget {
  const _HorizontalProducts({required this.products});

  final List<Product> products;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 260,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
        itemCount: products.length,
        separatorBuilder: (_, _) => const SizedBox(width: AppSpacing.md),
        itemBuilder: (context, i) {
          final product = products[i];
          return SizedBox(
            width: 160,
            child: ProductCard(
              product: product,
              onTap: () => Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => ProductDetailScreen(product: product)),
              ),
            ),
          );
        },
      ),
    );
  }
}
