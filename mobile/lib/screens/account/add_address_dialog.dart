import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../data/models/address.dart';
import '../../providers/address_provider.dart';
import '../../providers/auth_provider.dart';

class AddAddressModal extends StatefulWidget {
  const AddAddressModal({super.key, this.initialAddress});

  final Address? initialAddress;

  static Future<void> show(BuildContext context, {Address? initialAddress}) {
    return showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => AddAddressModal(initialAddress: initialAddress),
    );
  }

  @override
  State<AddAddressModal> createState() => _AddAddressModalState();
}

class _AddAddressModalState extends State<AddAddressModal> {
  late final TextEditingController _labelController;
  late final TextEditingController _recipientController;
  late final TextEditingController _line1Controller;
  late final TextEditingController _cityController;
  late final TextEditingController _phoneController;
  bool _isDefault = false;

  @override
  void initState() {
    super.initState();
    final addr = widget.initialAddress;

    if (addr != null) {
      _labelController = TextEditingController(text: addr.label);
      _recipientController = TextEditingController(text: addr.recipient);
      _line1Controller = TextEditingController(text: addr.line1);
      _cityController = TextEditingController(text: addr.city);
      _phoneController = TextEditingController(text: addr.phone);
      _isDefault = addr.isDefault;
    } else {
      final user = context.read<AuthProvider>().user;
      _labelController = TextEditingController(text: 'Home');
      _recipientController = TextEditingController(text: user?.name ?? '');
      _line1Controller = TextEditingController();
      _cityController = TextEditingController(text: 'Doha, Qatar');
      _phoneController = TextEditingController(text: user?.phone ?? '');
      _isDefault = false;
    }
  }

  @override
  void dispose() {
    _labelController.dispose();
    _recipientController.dispose();
    _line1Controller.dispose();
    _cityController.dispose();
    _phoneController.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    final label = _labelController.text.trim();
    final recipient = _recipientController.text.trim();
    final line1 = _line1Controller.text.trim();
    final city = _cityController.text.trim();
    final phone = _phoneController.text.trim();

    if (recipient.isEmpty || line1.isEmpty || phone.isEmpty) {
      AppSnackbar.showError(context, 'Please fill in recipient name, address, and phone number');
      return;
    }

    final addrProvider = context.read<AddressProvider>();

    if (widget.initialAddress != null) {
      await addrProvider.updateAddress(
        id: widget.initialAddress!.id,
        label: label,
        recipient: recipient,
        line1: line1,
        city: city.isEmpty ? 'Doha, Qatar' : city,
        phone: phone,
        isDefault: _isDefault,
      );
      if (!mounted) return;
      AppSnackbar.showSuccess(context, 'Address updated successfully!');
    } else {
      await addrProvider.addAddress(
        label: label,
        recipient: recipient,
        line1: line1,
        city: city.isEmpty ? 'Doha, Qatar' : city,
        phone: phone,
        isDefault: _isDefault,
      );
      if (!mounted) return;
      AppSnackbar.showSuccess(context, 'New address added successfully!');
    }

    if (mounted) {
      Navigator.of(context).pop();
    }
  }

  @override
  Widget build(BuildContext context) {
    final bottomInset = MediaQuery.of(context).viewInsets.bottom;
    final isEditing = widget.initialAddress != null;

    return Container(
      decoration: const BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.vertical(top: Radius.circular(AppRadius.lg)),
      ),
      padding: EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.lg, AppSpacing.lg, AppSpacing.lg + bottomInset),
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Text(
                  isEditing ? 'Edit Address' : 'Add New Address',
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: AppColors.ink),
                ),
                const Spacer(),
                IconButton(icon: const Icon(Icons.close_rounded), onPressed: () => Navigator.of(context).pop()),
              ],
            ),
            const SizedBox(height: AppSpacing.md),
            const Text('Address Label', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.slate700)),
            const SizedBox(height: 4),
            TextField(
              controller: _labelController,
              decoration: const InputDecoration(hintText: 'e.g. Home, Office, Villa'),
            ),
            const SizedBox(height: AppSpacing.md),
            const Text('Recipient Full Name', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.slate700)),
            const SizedBox(height: 4),
            TextField(
              controller: _recipientController,
              decoration: const InputDecoration(hintText: 'e.g. Sarah Ahmed', prefixIcon: Icon(Icons.person_outline_rounded)),
            ),
            const SizedBox(height: AppSpacing.md),
            const Text('Address Line (Street, Villa/Apt #)', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.slate700)),
            const SizedBox(height: 4),
            TextField(
              controller: _line1Controller,
              decoration: const InputDecoration(hintText: 'e.g. Zone 45, Street 12, Villa 7', prefixIcon: Icon(Icons.location_on_outlined)),
            ),
            const SizedBox(height: AppSpacing.md),
            const Text('City / Area', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.slate700)),
            const SizedBox(height: 4),
            TextField(
              controller: _cityController,
              decoration: const InputDecoration(hintText: 'e.g. Doha, Qatar', prefixIcon: Icon(Icons.map_outlined)),
            ),
            const SizedBox(height: AppSpacing.md),
            const Text('Contact Phone', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.slate700)),
            const SizedBox(height: 4),
            TextField(
              controller: _phoneController,
              keyboardType: TextInputType.phone,
              decoration: const InputDecoration(hintText: 'e.g. +974 5555 1234', prefixIcon: Icon(Icons.phone_outlined)),
            ),
            const SizedBox(height: AppSpacing.sm),
            GestureDetector(
              onTap: () => setState(() => _isDefault = !_isDefault),
              child: Row(
                children: [
                  SizedBox(
                    height: 24,
                    width: 24,
                    child: Checkbox(
                      value: _isDefault,
                      onChanged: (val) => setState(() => _isDefault = val ?? false),
                    ),
                  ),
                  const SizedBox(width: AppSpacing.sm),
                  const Text('Set as default delivery address', style: TextStyle(fontSize: 13, color: AppColors.ink)),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.md),
            SizedBox(
              width: double.infinity,
              height: 48,
              child: ElevatedButton(
                onPressed: _save,
                child: Text(isEditing ? 'Update Address' : 'Save Address'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
