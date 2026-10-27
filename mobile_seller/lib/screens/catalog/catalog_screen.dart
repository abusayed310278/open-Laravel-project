import 'package:flutter/material.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../products/add_product_screen.dart';
import '../products/products_screen.dart';
import 'inventory_view.dart';

class CatalogScreen extends StatefulWidget {
  final int initialTab;
  const CatalogScreen({super.key, this.initialTab = 0});

  @override
  State<CatalogScreen> createState() => _CatalogScreenState();
}

class _CatalogScreenState extends State<CatalogScreen> {
  late int _selectedTab;

  @override
  void initState() {
    super.initState();
    _selectedTab = widget.initialTab;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        title: const Text('Catalog & Inventory'),
        actions: [
          if (_selectedTab == 0)
            IconButton(
              icon: const Icon(Icons.add_circle, color: AppColors.brand500, size: 28),
              tooltip: 'Add Listing',
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
          // Segmented Tabs Header
          Padding(
            padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.sm, AppSpacing.lg, AppSpacing.sm),
            child: Container(
              padding: const EdgeInsets.all(4),
              decoration: BoxDecoration(
                color: AppColors.slate200.withValues(alpha: 0.6),
                borderRadius: BorderRadius.circular(AppRadius.md),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: GestureDetector(
                      onTap: () => setState(() => _selectedTab = 0),
                      child: Container(
                        padding: const EdgeInsets.symmetric(vertical: 9),
                        decoration: BoxDecoration(
                          color: _selectedTab == 0 ? AppColors.brand600 : Colors.transparent,
                          borderRadius: BorderRadius.circular(AppRadius.sm),
                          boxShadow: _selectedTab == 0
                              ? [BoxShadow(color: AppColors.brand600.withValues(alpha: 0.25), blurRadius: 8, offset: const Offset(0, 2))]
                              : null,
                        ),
                        alignment: Alignment.center,
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.inventory_2_outlined, size: 16, color: _selectedTab == 0 ? Colors.white : AppColors.slate600),
                            const SizedBox(width: 6),
                            Text(
                              'Product Listings',
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: _selectedTab == 0 ? FontWeight.w700 : FontWeight.w600,
                                color: _selectedTab == 0 ? Colors.white : AppColors.slate600,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                  Expanded(
                    child: GestureDetector(
                      onTap: () => setState(() => _selectedTab = 1),
                      child: Container(
                        padding: const EdgeInsets.symmetric(vertical: 9),
                        decoration: BoxDecoration(
                          color: _selectedTab == 1 ? AppColors.brand600 : Colors.transparent,
                          borderRadius: BorderRadius.circular(AppRadius.sm),
                          boxShadow: _selectedTab == 1
                              ? [BoxShadow(color: AppColors.brand600.withValues(alpha: 0.25), blurRadius: 8, offset: const Offset(0, 2))]
                              : null,
                        ),
                        alignment: Alignment.center,
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.tune_rounded, size: 16, color: _selectedTab == 1 ? Colors.white : AppColors.slate600),
                            const SizedBox(width: 6),
                            Text(
                              'Manage Inventory',
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: _selectedTab == 1 ? FontWeight.w700 : FontWeight.w600,
                                color: _selectedTab == 1 ? Colors.white : AppColors.slate600,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),

          // Content body
          Expanded(
            child: _selectedTab == 0
                ? const ProductsScreen(embedded: true)
                : const InventoryView(),
          ),
        ],
      ),
    );
  }
}
