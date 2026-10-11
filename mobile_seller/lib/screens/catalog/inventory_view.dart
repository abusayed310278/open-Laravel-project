import 'package:flutter/material.dart';

import '../../core/network/api_client.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../core/widgets/empty_view.dart';
import '../../core/widgets/loading_view.dart';

class InventoryView extends StatefulWidget {
  const InventoryView({super.key});

  @override
  State<InventoryView> createState() => _InventoryViewState();
}

class _InventoryViewState extends State<InventoryView> {
  final ApiClient _apiClient = ApiClient();
  final TextEditingController _searchController = TextEditingController();

  bool _isLoading = true;
  String? _errorMessage;
  List<dynamic> _items = [];
  Map<String, dynamic> _summary = {};

  @override
  void initState() {
    super.initState();
    _fetchInventory();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _fetchInventory([String? search]) async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final query = search != null && search.trim().isNotEmpty ? {'search': search.trim()} : null;
      final response = await _apiClient.get('/inventory', queryParams: query);
      if (mounted) {
        setState(() {
          if (response is Map<String, dynamic>) {
            _items = (response['inventory'] as List?) ?? [];
            _summary = (response['summary'] as Map<String, dynamic>?) ?? {};
          }
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _errorMessage = e.toString();
          _isLoading = false;
        });
      }
    }
  }

  void _showUpdateStockDialog(Map<String, dynamic> product) {
    final qtyController = TextEditingController(text: product['quantity']?.toString() ?? '0');
    final priceController = TextEditingController(text: product['price']?.toString() ?? '0.00');

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text('Adjust Stock: ${product['title']}'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('SKU: ${product['sku']}', style: const TextStyle(fontSize: 12, color: AppColors.slate500)),
            const SizedBox(height: AppSpacing.md),
            TextField(
              controller: qtyController,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(labelText: 'Available Quantity (Units)'),
            ),
            const SizedBox(height: AppSpacing.md),
            TextField(
              controller: priceController,
              keyboardType: const TextInputType.numberWithOptions(decimal: true),
              decoration: const InputDecoration(labelText: 'Unit Price (QAR)'),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.of(ctx).pop(), child: const Text('Cancel')),
          ElevatedButton(
            onPressed: () async {
              final newQty = int.tryParse(qtyController.text.trim()) ?? 0;
              final newPrice = double.tryParse(priceController.text.trim());
              Navigator.of(ctx).pop();

              try {
                await _apiClient.patch(
                  '/inventory/${product['id']}',
                  body: {'quantity': newQty, if (newPrice != null) 'price': newPrice},
                );
                _fetchInventory(_searchController.text);
                if (mounted) {
                  AppSnackBar.success(context, 'Inventory updated successfully!');
                }
              } catch (e) {
                if (mounted) {
                  AppSnackBar.error(context, 'Failed to update: $e');
                }
              }
            },
            child: const Text('Save Stock'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // 1. Search Bar
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.sm),
          child: TextField(
            controller: _searchController,
            onSubmitted: (val) => _fetchInventory(val),
            decoration: InputDecoration(
              hintText: 'Search SKU or product title...',
              prefixIcon: const Icon(Icons.search, size: 20, color: AppColors.slate400),
              suffixIcon: _searchController.text.isNotEmpty
                  ? IconButton(
                      icon: const Icon(Icons.clear, size: 18, color: AppColors.slate400),
                      onPressed: () {
                        _searchController.clear();
                        _fetchInventory();
                      },
                    )
                  : null,
            ),
          ),
        ),

        // 2. Metrics Summary Row
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.xs),
          child: Row(
            children: [
              _buildMiniMetric('Total', '${_summary['total_products'] ?? 0}', AppColors.ink),
              const SizedBox(width: 8),
              _buildMiniMetric('In Stock', '${_summary['in_stock'] ?? 0}', AppColors.success),
              const SizedBox(width: 8),
              _buildMiniMetric('Low Stock', '${_summary['low_stock'] ?? 0}', AppColors.warning),
              const SizedBox(width: 8),
              _buildMiniMetric('Out of Stock', '${_summary['out_of_stock'] ?? 0}', AppColors.danger),
            ],
          ),
        ),
        const SizedBox(height: AppSpacing.xs),

        // 3. Inventory List
        Expanded(
          child: RefreshIndicator(
            onRefresh: () => _fetchInventory(_searchController.text),
            color: AppColors.brand500,
            child: _isLoading
                ? const LoadingView(message: 'Loading inventory...')
                : _errorMessage != null
                    ? Center(child: Text(_errorMessage!, style: const TextStyle(color: AppColors.danger)))
                    : _items.isEmpty
                        ? const EmptyView(
                            icon: Icons.inventory_2_outlined,
                            title: 'No Inventory Records',
                            subtitle: 'Add products to your catalog to track stock levels.',
                          )
                        : ListView.separated(
                            padding: const EdgeInsets.all(AppSpacing.lg),
                            itemCount: _items.length,
                            separatorBuilder: (_, __) => const SizedBox(height: AppSpacing.md),
                            itemBuilder: (context, index) {
                              final item = _items[index] as Map<String, dynamic>;
                              return _buildInventoryCard(item);
                            },
                          ),
          ),
        ),
      ],
    );
  }

  Widget _buildMiniMetric(String label, String value, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 4),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(AppRadius.md),
          border: Border.all(color: AppColors.slate200),
        ),
        child: Column(
          children: [
            Text(value, style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: color)),
            const SizedBox(height: 2),
            Text(label, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppColors.slate500)),
          ],
        ),
      ),
    );
  }

  Widget _buildInventoryCard(Map<String, dynamic> item) {
    final qty = item['quantity'] is int ? item['quantity'] : int.tryParse(item['quantity']?.toString() ?? '0') ?? 0;
    final stockStatus = item['stock_status']?.toString() ?? 'in_stock';
    final price = item['price'] != null ? '${item['price']} QAR' : '0.00 QAR';

    Color badgeColor = AppColors.success;
    String badgeText = '$qty in stock';
    if (stockStatus == 'low_stock') {
      badgeColor = AppColors.warning;
      badgeText = '$qty left (Low)';
    } else if (stockStatus == 'out_of_stock' || qty <= 0) {
      badgeColor = AppColors.danger;
      badgeText = 'Out of Stock';
    }

    return Container(
      padding: const EdgeInsets.all(AppSpacing.md),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.md),
        border: Border.all(color: AppColors.slate200),
      ),
      child: Row(
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: AppColors.slate100,
              borderRadius: BorderRadius.circular(AppRadius.sm),
            ),
            child: const Icon(Icons.tag, size: 22, color: AppColors.slate500),
          ),
          const SizedBox(width: AppSpacing.md),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item['title']?.toString() ?? '',
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.ink),
                ),
                const SizedBox(height: 3),
                Row(
                  children: [
                    Text('SKU: ${item['sku'] ?? 'N/A'}', style: const TextStyle(fontSize: 11, color: AppColors.slate500)),
                    const SizedBox(width: 8),
                    Text('• $price', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.ink)),
                  ],
                ),
                const SizedBox(height: 4),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                  decoration: BoxDecoration(
                    color: badgeColor.withOpacity(0.12),
                    borderRadius: BorderRadius.circular(AppRadius.sm),
                  ),
                  child: Text(
                    badgeText,
                    style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: badgeColor),
                  ),
                ),
              ],
            ),
          ),
          IconButton(
            icon: const Icon(Icons.edit_note_rounded, size: 24, color: AppColors.brand600),
            tooltip: 'Adjust Stock',
            onPressed: () => _showUpdateStockDialog(item),
          ),
        ],
      ),
    );
  }
}
