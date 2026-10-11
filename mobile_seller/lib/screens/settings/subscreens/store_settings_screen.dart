import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../../core/network/api_client.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_spacing.dart';
import '../../../core/widgets/custom_button.dart';
import '../../../core/widgets/custom_text_field.dart';
import '../../../providers/seller_auth_provider.dart';

class StoreSettingsScreen extends StatefulWidget {
  const StoreSettingsScreen({super.key});

  @override
  State<StoreSettingsScreen> createState() => _StoreSettingsScreenState();
}

class _StoreSettingsScreenState extends State<StoreSettingsScreen> {
  final ApiClient _apiClient = ApiClient();
  final _formKey = GlobalKey<FormState>();

  late TextEditingController _storeNameController;
  late TextEditingController _slugController;
  late TextEditingController _descController;
  late TextEditingController _addressController;
  late TextEditingController _cityController;
  late TextEditingController _countryController;

  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    final user = context.read<SellerAuthProvider>().user;
    _storeNameController = TextEditingController(text: user?.storeName ?? '');
    _slugController = TextEditingController(text: user?.storeSlug ?? '');
    _descController = TextEditingController(text: user?.description ?? '');
    _addressController = TextEditingController(text: user?.address ?? '');
    _cityController = TextEditingController(text: user?.city ?? '');
    _countryController = TextEditingController(text: user?.country ?? 'Qatar');
  }

  @override
  void dispose() {
    _storeNameController.dispose();
    _slugController.dispose();
    _descController.dispose();
    _addressController.dispose();
    _cityController.dispose();
    _countryController.dispose();
    super.dispose();
  }

  Future<void> _handleSave() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isSaving = true);

    try {
      await _apiClient.post(
        '/profile',
        body: {
          'store_name': _storeNameController.text.trim(),
          'store_slug': _slugController.text.trim(),
          'description': _descController.text.trim(),
          'address': _addressController.text.trim(),
          'city': _cityController.text.trim(),
          'country': _countryController.text.trim(),
        },
      );

      await context.read<SellerAuthProvider>().refreshProfile();

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Store settings updated successfully!'), backgroundColor: AppColors.success),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to update: $e'), backgroundColor: AppColors.danger),
        );
      }
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.surface,
      appBar: AppBar(title: const Text('Store Settings')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppSpacing.lg),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Text(
                'Store Identity & Location',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.ink),
              ),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(
                controller: _storeNameController,
                label: 'Store Name',
                validator: (v) => v == null || v.trim().isEmpty ? 'Required' : null,
              ),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(
                controller: _slugController,
                label: 'Store Handle / Slug',
                hint: 'e.g. my-store',
              ),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(
                controller: _descController,
                label: 'Store Description',
                hint: 'Tell customers about your shop and specialty...',
                maxLines: 3,
              ),
              const SizedBox(height: AppSpacing.md),
              CustomTextField(
                controller: _addressController,
                label: 'Physical Address',
              ),
              const SizedBox(height: AppSpacing.md),
              Row(
                children: [
                  Expanded(
                    child: CustomTextField(
                      controller: _cityController,
                      label: 'City',
                    ),
                  ),
                  const SizedBox(width: AppSpacing.md),
                  Expanded(
                    child: CustomTextField(
                      controller: _countryController,
                      label: 'Country',
                    ),
                  ),
                ],
              ),
              const SizedBox(height: AppSpacing.xxl),
              CustomButton(
                text: 'Save Store Settings',
                isLoading: _isSaving,
                onPressed: _handleSave,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
