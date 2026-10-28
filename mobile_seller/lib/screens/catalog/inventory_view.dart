import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../../core/network/api_client.dart';
import '../../core/network/api_config.dart';
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
  final NumberFormat _currencyFormat = NumberFormat('#,##0.00', 'en_US');

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
              product['title']?.toString() ?? '',
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
              if (product['sku'] != null && product['sku'].toString().isNotEmpty && product['sku'].toString() != 'N/A') ...[
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: AppColors.slate100,
                    borderRadius: BorderRadius.circular(AppRadius.sm),
                  ),
                  child: Text('SKU: ${product['sku']}', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.slate600)),
                ),
                const SizedBox(height: AppSpacing.md),
              ],
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

              try {
                final body = <String, dynamic>{'quantity': newQty};
                if (newPrice != null) {
                  body['price'] = newPrice;
                }
                await _apiClient.patch(
                  '/inventory/${product['id']}',
                  body: body,
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
            child: const Text('Save Stock', style: TextStyle(fontWeight: FontWeight.w700)),
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
              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              fillColor: AppColors.surface,
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
                            separatorBuilder: (_, _) => const SizedBox(height: AppSpacing.md),
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
        padding: const EdgeInsets.symmetric(vertical: 9, horizontal: 4),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(AppRadius.md),
          border: Border.all(color: AppColors.slate200.withValues(alpha: 0.8)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.02),
              blurRadius: 4,
              offset: const Offset(0, 1),
            ),
          ],
        ),
        child: Column(
          children: [
            Text(value, style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: color)),
            const SizedBox(height: 2),
            Text(label, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppColors.slate500)),
          ],
        ),
      ),
    );
  }

  Widget _buildInventoryCard(Map<String, dynamic> item) {
    final qty = item['quantity'] is int ? item['quantity'] : int.tryParse(item['quantity']?.toString() ?? '0') ?? 0;
    final stockStatus = item['stock_status']?.toString() ?? (qty <= 0 ? 'out_of_stock' : (qty <= 5 ? 'low_stock' : 'in_stock'));
    final priceVal = item['price'] != null ? (double.tryParse(item['price'].toString()) ?? 0.0) : 0.0;
    final price = '${_currencyFormat.format(priceVal)} QAR';
    final thumbnailRaw = item['thumbnail']?.toString();
    final thumbnail = thumbnailRaw != null && thumbnailRaw.isNotEmpty
        ? ApiConfig.sanitizeUrl(thumbnailRaw)
        : null;
    final sku = item['sku']?.toString();
    final categoryName = item['category_name']?.toString();

    Color badgeColor = AppColors.success;
    Color badgeBg = AppColors.successBg;
    String badgeText = '$qty in stock';
    if (stockStatus == 'low_stock') {
      badgeColor = AppColors.warning;
      badgeBg = AppColors.warningBg;
      badgeText = '$qty left (Low)';
    } else if (stockStatus == 'out_of_stock' || qty <= 0) {
      badgeColor = AppColors.danger;
      badgeBg = AppColors.dangerBg;
      badgeText = 'Out of Stock';
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
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        child: InkWell(
          borderRadius: BorderRadius.circular(AppRadius.lg),
          onTap: () => _showUpdateStockDialog(item),
          child: Padding(
            padding: const EdgeInsets.all(AppSpacing.md),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                // Product Thumbnail
                Container(
                  width: 64,
                  height: 64,
                  decoration: BoxDecoration(
                    color: AppColors.slate100,
                    borderRadius: BorderRadius.circular(AppRadius.md),
                    border: Border.all(color: AppColors.slate200.withValues(alpha: 0.8)),
                  ),
                  clipBehavior: Clip.antiAlias,
                  child: thumbnail != null
                      ? Image.network(
                          thumbnail,
                          fit: BoxFit.cover,
                          loadingBuilder: (context, child, loadingProgress) {
                            if (loadingProgress == null) return child;
                            return Container(
                              color: AppColors.slate100,
                              alignment: Alignment.center,
                              child: const SizedBox(
                                width: 16,
                                height: 16,
                                child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.brand500),
                              ),
                            );
                          },
                          errorBuilder: (_, _, _) => Container(
                            color: AppColors.slate100,
                            alignment: Alignment.center,
                            child: const Icon(Icons.inventory_2_outlined, color: AppColors.slate400, size: 24),
                          ),
                        )
                      : Container(
                          color: AppColors.slate100,
                          alignment: Alignment.center,
                          child: const Icon(Icons.inventory_2_outlined, color: AppColors.slate400, size: 24),
                        ),
                ),
                const SizedBox(width: AppSpacing.md),

                // Details Column
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        item['title']?.toString() ?? '',
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          color: AppColors.ink,
                          letterSpacing: -0.2,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          if (sku != null && sku.isNotEmpty && sku != 'N/A') ...[
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                              decoration: BoxDecoration(
                                color: AppColors.slate100,
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                'SKU: $sku',
                                style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppColors.slate600),
                              ),
                            ),
                            const SizedBox(width: 6),
                          ] else if (categoryName != null && categoryName.isNotEmpty) ...[
                            Text(
                              categoryName,
                              style: const TextStyle(fontSize: 11, color: AppColors.slate500),
                            ),
                            const SizedBox(width: 6),
                          ],
                          Text(
                            price,
                            style: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w700,
                              color: AppColors.brand600,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: badgeBg,
                          borderRadius: BorderRadius.circular(AppRadius.pill),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Container(
                              width: 6,
                              height: 6,
                              decoration: BoxDecoration(
                                color: badgeColor,
                                shape: BoxShape.circle,
                              ),
                            ),
                            const SizedBox(width: 5),
                            Text(
                              badgeText,
                              style: TextStyle(
                                fontSize: 10.5,
                                fontWeight: FontWeight.w700,
                                color: badgeColor,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: AppSpacing.sm),

                // Action Adjust Button
                InkWell(
                  onTap: () => _showUpdateStockDialog(item),
                  borderRadius: BorderRadius.circular(AppRadius.md),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                    decoration: BoxDecoration(
                      color: AppColors.brand50,
                      borderRadius: BorderRadius.circular(AppRadius.md),
                      border: Border.all(color: AppColors.brand200),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.tune_rounded, size: 14, color: AppColors.brand700),
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
              ],
            ),
          ),
        ),
      ),
    );
  }
}
