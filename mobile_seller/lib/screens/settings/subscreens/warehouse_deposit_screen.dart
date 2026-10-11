import 'package:flutter/material.dart';

import '../../../core/network/api_client.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_spacing.dart';
import '../../../core/widgets/custom_button.dart';
import '../../../core/widgets/empty_view.dart';
import '../../../core/widgets/loading_view.dart';

class WarehouseDepositScreen extends StatefulWidget {
  const WarehouseDepositScreen({super.key});

  @override
  State<WarehouseDepositScreen> createState() => _WarehouseDepositScreenState();
}

class _WarehouseDepositScreenState extends State<WarehouseDepositScreen> {
  final ApiClient _apiClient = ApiClient();
  bool _isLoading = true;
  List<dynamic> _deposits = [];
  List<dynamic> _eligibleProducts = [];
  List<dynamic> _warehouses = [];

  @override
  void initState() {
    super.initState();
    _fetchDeposits();
  }

  Future<void> _fetchDeposits() async {
    setState(() => _isLoading = true);

    try {
      final response = await _apiClient.get('/warehouse-deposits');
      if (mounted && response is Map<String, dynamic>) {
        setState(() {
          _deposits = (response['deposits'] as List?) ?? [];
          _eligibleProducts = (response['eligible_products'] as List?) ?? [];
          _warehouses = (response['warehouses'] as List?) ?? [];
          _isLoading = false;
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  void _showNewDepositModal() {
    if (_eligibleProducts.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('No verified products eligible for warehouse deposit yet.'), backgroundColor: AppColors.warning),
      );
      return;
    }

    int selectedProductId = _eligibleProducts.first['id'] as int;
    int selectedWarehouseId = _warehouses.isNotEmpty ? (_warehouses.first['id'] as int) : 1;
    final qtyController = TextEditingController(text: '1');

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) => Container(
          padding: EdgeInsets.fromLTRB(AppSpacing.xl, AppSpacing.lg, AppSpacing.xl, MediaQuery.of(context).viewInsets.bottom + AppSpacing.xl),
          decoration: const BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.vertical(top: Radius.circular(AppRadius.lg)),
          ),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Deposit Consignment to Hub', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.ink)),
                    IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.of(ctx).pop()),
                  ],
                ),
                const SizedBox(height: AppSpacing.md),
                DropdownButtonFormField<int>(
                  value: selectedProductId,
                  decoration: const InputDecoration(labelText: 'Select Product'),
                  items: _eligibleProducts
                      .map((p) => DropdownMenuItem<int>(
                            value: p['id'] as int,
                            child: Text('${p['title']} (SKU: ${p['sku']})', style: const TextStyle(fontSize: 13)),
                          ))
                      .toList(),
                  onChanged: (val) {
                    if (val != null) setModalState(() => selectedProductId = val);
                  },
                ),
                const SizedBox(height: AppSpacing.md),
                if (_warehouses.isNotEmpty) ...[
                  DropdownButtonFormField<int>(
                    value: selectedWarehouseId,
                    decoration: const InputDecoration(labelText: 'Destination Warehouse'),
                    items: _warehouses
                        .map((w) => DropdownMenuItem<int>(
                              value: w['id'] as int,
                              child: Text(w['name']?.toString() ?? '', style: const TextStyle(fontSize: 13)),
                            ))
                        .toList(),
                    onChanged: (val) {
                      if (val != null) setModalState(() => selectedWarehouseId = val);
                    },
                  ),
                  const SizedBox(height: AppSpacing.md),
                ],
                TextField(
                  controller: qtyController,
                  keyboardType: TextInputType.number,
                  decoration: const InputDecoration(labelText: 'Quantity (Units)'),
                ),
                const SizedBox(height: AppSpacing.xl),
                CustomButton(
                  text: 'Submit Deposit Request',
                  onPressed: () async {
                    final qty = int.tryParse(qtyController.text.trim()) ?? 1;
                    Navigator.of(ctx).pop();

                    try {
                      await _apiClient.post(
                        '/warehouse-deposits',
                        body: {
                          'product_id': selectedProductId,
                          'warehouse_id': selectedWarehouseId,
                          'quantity': qty,
                        },
                      );
                      _fetchDeposits();
                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Deposit request created!'), backgroundColor: AppColors.success),
                        );
                      }
                    } catch (e) {
                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(content: Text('Request failed: $e'), backgroundColor: AppColors.danger),
                        );
                      }
                    }
                  },
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        title: const Text('Warehouse Deposits'),
        actions: [
          IconButton(
            icon: const Icon(Icons.add_business_outlined, color: AppColors.brand500),
            tooltip: 'Deposit Consignment',
            onPressed: _showNewDepositModal,
          ),
        ],
      ),
      body: _isLoading
          ? const LoadingView(message: 'Loading warehouse deposits...')
          : RefreshIndicator(
              onRefresh: _fetchDeposits,
              color: AppColors.brand500,
              child: _deposits.isEmpty
                  ? EmptyView(
                      icon: Icons.warehouse_outlined,
                      title: 'No Warehouse Consignments',
                      subtitle: 'Store your products in Openbox fulfillment hubs for faster customer delivery.',
                      actionLabel: '+ Request Deposit',
                      onAction: _showNewDepositModal,
                    )
                  : ListView.separated(
                      padding: const EdgeInsets.all(AppSpacing.lg),
                      itemCount: _deposits.length,
                      separatorBuilder: (_, __) => const SizedBox(height: AppSpacing.md),
                      itemBuilder: (context, index) {
                        final d = _deposits[index] as Map<String, dynamic>;
                        final status = d['status']?.toString() ?? 'pending';

                        Color statusColor = AppColors.brand600;
                        if (status == 'received' || status == 'deposited') statusColor = AppColors.success;
                        if (status == 'rejected') statusColor = AppColors.danger;

                        return Container(
                          padding: const EdgeInsets.all(AppSpacing.lg),
                          decoration: BoxDecoration(
                            color: AppColors.surface,
                            borderRadius: BorderRadius.circular(AppRadius.md),
                            border: Border.all(color: AppColors.slate200),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    d['tracking_number']?.toString() ?? '',
                                    style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.brand600),
                                  ),
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                    decoration: BoxDecoration(
                                      color: statusColor.withOpacity(0.12),
                                      borderRadius: BorderRadius.circular(AppRadius.pill),
                                    ),
                                    child: Text(
                                      status.toUpperCase(),
                                      style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: statusColor),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 6),
                              Text(
                                d['product_title']?.toString() ?? 'Product',
                                style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.ink),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                'Hub: ${d['warehouse_name']} • Qty: ${d['quantity']} units',
                                style: const TextStyle(fontSize: 12, color: AppColors.slate600),
                              ),
                              const Divider(height: 18, color: AppColors.slate100),
                              Text(
                                'Submitted: ${d['created_at'] ?? 'Recently'}',
                                style: const TextStyle(fontSize: 11, color: AppColors.slate400),
                              ),
                            ],
                          ),
                        );
                      },
                    ),
            ),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: AppColors.brand500,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add),
        label: const Text('New Deposit', style: TextStyle(fontWeight: FontWeight.w700)),
        onPressed: _showNewDepositModal,
      ),
    );
  }
}
