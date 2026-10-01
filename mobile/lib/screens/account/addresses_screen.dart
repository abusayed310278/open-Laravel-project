import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../providers/address_provider.dart';
import 'add_address_dialog.dart';

class AddressesScreen extends StatelessWidget {
  const AddressesScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final addressProvider = context.watch<AddressProvider>();
    final addresses = addressProvider.addresses;

    return Scaffold(
      appBar: AppBar(title: const Text('Addresses')),
      body: addresses.isEmpty
          ? Center(
              child: Padding(
                padding: const EdgeInsets.all(AppSpacing.xl),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.location_off_outlined, size: 56, color: AppColors.slate300),
                    const SizedBox(height: AppSpacing.md),
                    const Text('No Saved Addresses', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16, color: AppColors.ink)),
                    const SizedBox(height: AppSpacing.xs),
                    const Text('Add your delivery address for faster checkout', style: TextStyle(color: AppColors.slate500, fontSize: 13), textAlign: TextAlign.center),
                    const SizedBox(height: AppSpacing.xl),
                    ElevatedButton.icon(
                      onPressed: () => AddAddressModal.show(context),
                      icon: const Icon(Icons.add_rounded, size: 18),
                      label: const Text('Add New Address'),
                    ),
                  ],
                ),
              ),
            )
          : ListView(
              padding: const EdgeInsets.all(AppSpacing.lg),
              children: [
                ...addresses.map(
                  (address) => Container(
                    margin: const EdgeInsets.only(bottom: AppSpacing.md),
                    padding: const EdgeInsets.all(AppSpacing.lg),
                    decoration: BoxDecoration(
                      color: AppColors.surface,
                      border: Border.all(color: address.isDefault ? AppColors.brand500 : AppColors.slate200, width: address.isDefault ? 1.5 : 1),
                      borderRadius: BorderRadius.circular(AppRadius.md),
                      boxShadow: [
                        BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 4, offset: const Offset(0, 2)),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Icon(
                              address.isDefault ? Icons.location_on_rounded : Icons.location_on_outlined,
                              color: address.isDefault ? AppColors.brand600 : AppColors.slate500,
                              size: 20,
                            ),
                            const SizedBox(width: 8),
                            Text(address.label, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15, color: AppColors.ink)),
                            if (address.isDefault) ...[
                              const SizedBox(width: 8),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                decoration: BoxDecoration(color: AppColors.brand50, borderRadius: BorderRadius.circular(6)),
                                child: const Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(Icons.check_circle_rounded, size: 12, color: AppColors.brand700),
                                    SizedBox(width: 4),
                                    Text('Primary', style: TextStyle(fontSize: 11, color: AppColors.brand700, fontWeight: FontWeight.w700)),
                                  ],
                                ),
                              ),
                            ],
                            const Spacer(),
                            // Direct Edit button
                            IconButton(
                              icon: const Icon(Icons.edit_outlined, size: 18, color: AppColors.slate500),
                              tooltip: 'Edit Address',
                              onPressed: () => AddAddressModal.show(context, initialAddress: address),
                            ),
                            // Direct Delete button
                            IconButton(
                              icon: const Icon(Icons.delete_outline_rounded, size: 18, color: AppColors.danger),
                              tooltip: 'Delete Address',
                              onPressed: () {
                                addressProvider.deleteAddress(address.id);
                                AppSnackbar.showSuccess(context, 'Address deleted');
                              },
                            ),
                          ],
                        ),
                        const SizedBox(height: AppSpacing.xs),
                        Text(address.recipient, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.ink)),
                        const SizedBox(height: 2),
                        Text('${address.line1}, ${address.city}', style: const TextStyle(fontSize: 12, color: AppColors.slate500)),
                        const SizedBox(height: 2),
                        Text(address.phone, style: const TextStyle(fontSize: 12, color: AppColors.slate500)),
                        if (!address.isDefault) ...[
                          const SizedBox(height: AppSpacing.sm),
                          const Divider(height: 1, color: AppColors.slate200),
                          const SizedBox(height: AppSpacing.xs),
                          Align(
                            alignment: Alignment.centerLeft,
                            child: TextButton.icon(
                              onPressed: () {
                                addressProvider.setDefault(address.id);
                                AppSnackbar.showSuccess(context, 'Set as primary address');
                              },
                              icon: const Icon(Icons.radio_button_unchecked_rounded, size: 16, color: AppColors.brand600),
                              label: const Text(
                                'Set as Primary Address',
                                style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.brand600),
                              ),
                              style: TextButton.styleFrom(padding: EdgeInsets.zero, minimumSize: Size.zero, tapTargetSize: MaterialTapTargetSize.shrinkWrap),
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: AppSpacing.xs),
                OutlinedButton.icon(
                  onPressed: () => AddAddressModal.show(context),
                  icon: const Icon(Icons.add_rounded, size: 18),
                  label: const Text('Add New Address'),
                  style: OutlinedButton.styleFrom(minimumSize: const Size.fromHeight(48)),
                ),
              ],
            ),
    );
  }
}
