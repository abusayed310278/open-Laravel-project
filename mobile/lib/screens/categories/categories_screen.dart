import 'package:flutter/material.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../data/mock/mock_data.dart';
import '../product/product_list_screen.dart';

class CategoriesScreen extends StatelessWidget {
  const CategoriesScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Categories')),
      body: GridView.builder(
        padding: const EdgeInsets.all(AppSpacing.lg),
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 2,
          mainAxisSpacing: AppSpacing.md,
          crossAxisSpacing: AppSpacing.md,
          childAspectRatio: 1.3,
        ),
        itemCount: MockData.categories.length,
        itemBuilder: (context, i) {
          final category = MockData.categories[i];
          final categoryProducts = MockData.getProductsForCategory(category.id, category.name);
          final count = categoryProducts.length;
          return GestureDetector(
            onTap: () => Navigator.of(context).push(
              MaterialPageRoute(
                builder: (_) => ProductListScreen(
                  title: category.name,
                  products: categoryProducts,
                ),
              ),
            ),
            child: Container(
              padding: const EdgeInsets.all(AppSpacing.lg),
              decoration: BoxDecoration(
                color: AppColors.surface,
                border: Border.all(color: AppColors.slate200),
                borderRadius: BorderRadius.circular(AppRadius.lg),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(color: AppColors.brand50, borderRadius: BorderRadius.circular(12)),
                    child: Icon(category.icon, color: AppColors.brand700, size: 22),
                  ),
                  const Spacer(),
                  Text(category.name, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14, color: AppColors.ink)),
                  const SizedBox(height: 2),
                  Text('$count items', style: const TextStyle(fontSize: 11, color: AppColors.slate500)),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}
