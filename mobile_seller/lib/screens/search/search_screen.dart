import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/empty_state.dart';
import '../../core/widgets/product_card.dart';
import '../../data/models/category.dart';
import '../../data/models/product.dart';
import '../../providers/product_provider.dart';
import '../product/product_detail_screen.dart';

class SearchScreen extends StatefulWidget {
  const SearchScreen({super.key});

  @override
  State<SearchScreen> createState() => _SearchScreenState();
}

class _SearchScreenState extends State<SearchScreen> {
  final _controller = TextEditingController();
  String _query = '';

  static const _recent = ['iPhone 14', 'MacBook Air', 'Galaxy Watch', 'Headphones'];

  List<Product> _getResults(List<Product> products) {
    if (_query.trim().isEmpty) return const [];
    final q = _query.toLowerCase();
    return products.where((p) => p.title.toLowerCase().contains(q)).toList();
  }

  @override
  Widget build(BuildContext context) {
    final productProvider = context.watch<ProductProvider>();
    final results = _getResults(productProvider.products);

    return Scaffold(
      appBar: AppBar(
        titleSpacing: 0,
        title: Padding(
          padding: const EdgeInsets.only(right: AppSpacing.lg),
          child: TextField(
            controller: _controller,
            autofocus: true,
            onChanged: (v) => setState(() => _query = v),
            decoration: InputDecoration(
              hintText: 'Search phones, laptops, watches...',
              prefixIcon: const Icon(Icons.search_rounded, size: 20),
              suffixIcon: _query.isEmpty
                  ? null
                  : IconButton(
                      icon: const Icon(Icons.close_rounded, size: 18),
                      onPressed: () {
                        _controller.clear();
                        setState(() => _query = '');
                      },
                    ),
              contentPadding: const EdgeInsets.symmetric(vertical: 0, horizontal: AppSpacing.lg),
            ),
          ),
        ),
      ),
      body: _query.isEmpty
          ? _SuggestionsView(
              categories: productProvider.categories,
              onTap: (v) => setState(() {
                _controller.text = v;
                _query = v;
              }),
            )
          : _ResultsView(results: results),
    );
  }
}

class _SuggestionsView extends StatelessWidget {
  const _SuggestionsView({required this.onTap, required this.categories});

  final ValueChanged<String> onTap;
  final List<ProductCategory> categories;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(AppSpacing.lg),
      children: [
        const Text('Recent searches', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.ink)),
        const SizedBox(height: AppSpacing.md),
        Wrap(
          spacing: AppSpacing.sm,
          runSpacing: AppSpacing.sm,
          children: _SearchScreenState._recent
              .map((term) => GestureDetector(
                    onTap: () => onTap(term),
                    child: Chip(
                      label: Text(term),
                      avatar: const Icon(Icons.history_rounded, size: 16),
                    ),
                  ))
              .toList(),
        ),
        if (categories.isNotEmpty) ...[
          const SizedBox(height: AppSpacing.xxl),
          const Text('Trending categories', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.ink)),
          const SizedBox(height: AppSpacing.md),
          ...categories.take(5).map(
                (c) => ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: const Icon(Icons.category_outlined, color: AppColors.brand600),
                  title: Text(c.name, style: const TextStyle(fontSize: 13)),
                  trailing: const Icon(Icons.north_west_rounded, size: 16, color: AppColors.slate400),
                  onTap: () => onTap(c.name),
                ),
              ),
        ],
      ],
    );
  }
}

class _ResultsView extends StatelessWidget {
  const _ResultsView({required this.results});
  final List<Product> results;

  @override
  Widget build(BuildContext context) {
    if (results.isEmpty) {
      return const EmptyState(
        icon: Icons.search_off_rounded,
        title: 'No products found',
        message: 'Try checking your spelling or using more general keywords.',
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
      itemCount: results.length,
      itemBuilder: (context, i) => ProductCard(
        product: results[i],
        onTap: () => Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => ProductDetailScreen(product: results[i])),
        ),
      ),
    );
  }
}
