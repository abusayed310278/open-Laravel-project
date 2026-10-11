import 'dart:io';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/custom_text_field.dart';
import '../../providers/seller_product_provider.dart';

class AddProductScreen extends StatefulWidget {
  const AddProductScreen({super.key});

  @override
  State<AddProductScreen> createState() => _AddProductScreenState();
}

class _AddProductScreenState extends State<AddProductScreen> {
  final _formKey = GlobalKey<FormState>();
  final _picker = ImagePicker();

  final _titleController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _priceController = TextEditingController();
  final _comparePriceController = TextEditingController();
  final _quantityController = TextEditingController(text: '1');
  final _skuController = TextEditingController();
  final _shippingFlatRateController = TextEditingController();

  int? _selectedCategoryId;
  int? _selectedBrandId;
  String _selectedCondition = 'new';
  String _selectedShippingType = 'free';

  final List<XFile> _selectedImages = [];
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<SellerProductProvider>().fetchProductMeta();
    });
  }

  @override
  void dispose() {
    _titleController.dispose();
    _descriptionController.dispose();
    _priceController.dispose();
    _comparePriceController.dispose();
    _quantityController.dispose();
    _skuController.dispose();
    _shippingFlatRateController.dispose();
    super.dispose();
  }

  Future<void> _pickImages() async {
    try {
      final picked = await _picker.pickMultiImage(imageQuality: 85);
      if (picked.isNotEmpty) {
        setState(() {
          _selectedImages.addAll(picked);
          if (_selectedImages.length > 8) {
            _selectedImages.removeRange(8, _selectedImages.length);
          }
        });
      }
    } catch (_) {}
  }

  Future<void> _handleSubmit() async {
    if (!_formKey.currentState!.validate()) return;

    if (_selectedCategoryId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please select a product category'), backgroundColor: AppColors.danger),
      );
      return;
    }

    setState(() => _isSubmitting = true);

    try {
      final fields = <String, String>{
        'title': _titleController.text.trim(),
        'description': _descriptionController.text.trim(),
        'category_id': _selectedCategoryId.toString(),
        'condition': _selectedCondition,
        'price': _priceController.text.trim(),
        'quantity': _quantityController.text.trim(),
        'shipping_type': _selectedShippingType,
      };

      if (_selectedBrandId != null) {
        fields['brand_id'] = _selectedBrandId.toString();
      }
      if (_comparePriceController.text.trim().isNotEmpty) {
        fields['compare_price'] = _comparePriceController.text.trim();
      }
      if (_skuController.text.trim().isNotEmpty) {
        fields['sku'] = _skuController.text.trim();
      }
      if (_selectedShippingType == 'flat_rate' && _shippingFlatRateController.text.trim().isNotEmpty) {
        fields['shipping_flat_rate'] = _shippingFlatRateController.text.trim();
      }

      final productProvider = context.read<SellerProductProvider>();
      final files = <http.MultipartFile>[];
      for (final img in _selectedImages) {
        files.add(await http.MultipartFile.fromPath('images[]', img.path));
      }

      final success = await productProvider.createProduct(fields: fields, images: files);

      if (!mounted) return;

      if (success) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Listing created and submitted for review!'),
            backgroundColor: AppColors.success,
          ),
        );
        Navigator.of(context).pop();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(productProvider.errorMessage ?? 'Failed to create listing'),
            backgroundColor: AppColors.danger,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString()), backgroundColor: AppColors.danger),
        );
      }
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final metaProvider = context.watch<SellerProductProvider>();

    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        title: const Text('Add New Product'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppSpacing.lg),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // 1. Photos Section
              _buildSectionCard(
                title: 'Product Photos (up to 8)',
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (_selectedImages.isNotEmpty)
                      SizedBox(
                        height: 90,
                        child: ListView.separated(
                          scrollDirection: Axis.horizontal,
                          itemCount: _selectedImages.length + (_selectedImages.length < 8 ? 1 : 0),
                          separatorBuilder: (_, __) => const SizedBox(width: 8),
                          itemBuilder: (context, index) {
                            if (index == _selectedImages.length) {
                              return _buildAddPhotoBtn();
                            }
                            final image = _selectedImages[index];
                            return Stack(
                              children: [
                                Container(
                                  width: 90,
                                  height: 90,
                                  decoration: BoxDecoration(
                                    borderRadius: BorderRadius.circular(AppRadius.md),
                                    border: Border.all(color: AppColors.slate200),
                                  ),
                                  clipBehavior: Clip.antiAlias,
                                  child: Image.file(File(image.path), fit: BoxFit.cover),
                                ),
                                Positioned(
                                  top: 4,
                                  right: 4,
                                  child: GestureDetector(
                                    onTap: () => setState(() => _selectedImages.removeAt(index)),
                                    child: Container(
                                      padding: const EdgeInsets.all(2),
                                      decoration: const BoxDecoration(
                                        color: Colors.red,
                                        shape: BoxShape.circle,
                                      ),
                                      child: const Icon(Icons.close, size: 14, color: Colors.white),
                                    ),
                                  ),
                                ),
                                if (index == 0)
                                  Positioned(
                                    bottom: 4,
                                    left: 4,
                                    child: Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                                      decoration: BoxDecoration(
                                        color: AppColors.brand500,
                                        borderRadius: BorderRadius.circular(4),
                                      ),
                                      child: const Text('Main', style: TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold)),
                                    ),
                                  ),
                              ],
                            );
                          },
                        ),
                      )
                    else
                      InkWell(
                        onTap: _pickImages,
                        borderRadius: BorderRadius.circular(AppRadius.md),
                        child: Container(
                          padding: const EdgeInsets.symmetric(vertical: 28),
                          decoration: BoxDecoration(
                            color: AppColors.slate50,
                            borderRadius: BorderRadius.circular(AppRadius.md),
                            border: Border.all(color: AppColors.slate300, style: BorderStyle.solid),
                          ),
                          alignment: Alignment.center,
                          child: Column(
                            children: const [
                              Icon(Icons.add_photo_alternate_outlined, size: 36, color: AppColors.brand500),
                              SizedBox(height: 8),
                              Text('Tap to select images from gallery', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: AppColors.ink)),
                              SizedBox(height: 2),
                              Text('PNG, JPG up to 4MB per photo', style: TextStyle(fontSize: 11, color: AppColors.slate400)),
                            ],
                          ),
                        ),
                      ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.lg),

              // 2. Basic Information
              _buildSectionCard(
                title: 'Basic Information',
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    CustomTextField(
                      controller: _titleController,
                      label: 'Product Title *',
                      hint: 'e.g. Apple iPhone 15 Pro Max 256GB',
                      validator: (v) => (v == null || v.trim().isEmpty) ? 'Please enter a title' : null,
                    ),
                    const SizedBox(height: AppSpacing.md),
                    const Text('Category *', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.slate700)),
                    const SizedBox(height: 6),
                    DropdownButtonFormField<int>(
                      value: _selectedCategoryId,
                      hint: const Text('Select Category', style: TextStyle(fontSize: 14, color: AppColors.slate400)),
                      items: metaProvider.categories.map((c) {
                        return DropdownMenuItem<int>(value: c.id, child: Text(c.name));
                      }).toList(),
                      onChanged: (val) => setState(() => _selectedCategoryId = val),
                      decoration: const InputDecoration(contentPadding: EdgeInsets.symmetric(horizontal: 14, vertical: 12)),
                    ),
                    const SizedBox(height: AppSpacing.md),
                    const Text('Brand (Optional)', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.slate700)),
                    const SizedBox(height: 6),
                    DropdownButtonFormField<int>(
                      value: _selectedBrandId,
                      hint: const Text('Select Brand', style: TextStyle(fontSize: 14, color: AppColors.slate400)),
                      items: metaProvider.brands.map((b) {
                        return DropdownMenuItem<int>(value: b.id, child: Text(b.name));
                      }).toList(),
                      onChanged: (val) => setState(() => _selectedBrandId = val),
                      decoration: const InputDecoration(contentPadding: EdgeInsets.symmetric(horizontal: 14, vertical: 12)),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.lg),

              // 3. Pricing & Condition
              _buildSectionCard(
                title: 'Pricing & Condition',
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: CustomTextField(
                            controller: _priceController,
                            label: 'Price (QAR) *',
                            hint: '0.00',
                            keyboardType: const TextInputType.numberWithOptions(decimal: true),
                            validator: (v) => (v == null || v.trim().isEmpty) ? 'Required' : null,
                          ),
                        ),
                        const SizedBox(width: AppSpacing.md),
                        Expanded(
                          child: CustomTextField(
                            controller: _comparePriceController,
                            label: 'Compare Price (QAR)',
                            hint: '0.00',
                            keyboardType: const TextInputType.numberWithOptions(decimal: true),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: AppSpacing.md),
                    const Text('Condition Grade *', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.slate700)),
                    const SizedBox(height: 6),
                    Wrap(
                      spacing: 8,
                      children: [
                        _buildConditionChoice('new', 'Brand New'),
                        _buildConditionChoice('grade_a', 'Grade A'),
                        _buildConditionChoice('grade_b', 'Grade B'),
                        _buildConditionChoice('grade_c', 'Grade C'),
                        _buildConditionChoice('used', 'Used'),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.lg),

              // 4. Inventory & Shipping
              _buildSectionCard(
                title: 'Inventory & Shipping',
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: CustomTextField(
                            controller: _quantityController,
                            label: 'Stock Quantity *',
                            hint: '1',
                            keyboardType: TextInputType.number,
                            validator: (v) => (v == null || v.trim().isEmpty) ? 'Required' : null,
                          ),
                        ),
                        const SizedBox(width: AppSpacing.md),
                        Expanded(
                          child: CustomTextField(
                            controller: _skuController,
                            label: 'SKU / Model',
                            hint: 'e.g. IPH-15-256',
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: AppSpacing.md),
                    const Text('Shipping Type *', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.slate700)),
                    const SizedBox(height: 6),
                    DropdownButtonFormField<String>(
                      value: _selectedShippingType,
                      items: const [
                        DropdownMenuItem(value: 'free', child: Text('Free Shipping')),
                        DropdownMenuItem(value: 'flat_rate', child: Text('Flat Rate')),
                        DropdownMenuItem(value: 'calculated', child: Text('Calculated / Standard')),
                      ],
                      onChanged: (val) => setState(() => _selectedShippingType = val ?? 'free'),
                      decoration: const InputDecoration(contentPadding: EdgeInsets.symmetric(horizontal: 14, vertical: 12)),
                    ),
                    if (_selectedShippingType == 'flat_rate') ...[
                      const SizedBox(height: AppSpacing.md),
                      CustomTextField(
                        controller: _shippingFlatRateController,
                        label: 'Flat Rate Fee (QAR) *',
                        hint: 'e.g. 25.00',
                        keyboardType: const TextInputType.numberWithOptions(decimal: true),
                      ),
                    ],
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.lg),

              // 5. Description
              _buildSectionCard(
                title: 'Description',
                child: CustomTextField(
                  controller: _descriptionController,
                  label: 'Detailed Description',
                  hint: 'Provide technical details, warranty, accessories included...',
                  maxLines: 5,
                ),
              ),
              const SizedBox(height: AppSpacing.xxl),

              // Submit Button
              CustomButton(
                text: 'Publish Listing for Review',
                isLoading: _isSubmitting,
                onPressed: _handleSubmit,
              ),
              const SizedBox(height: AppSpacing.xxl),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSectionCard({required String title, required Widget child}) {
    return Container(
      padding: const EdgeInsets.all(AppSpacing.lg),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.slate200.withOpacity(0.8)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.ink)),
          const Divider(height: 20, color: AppColors.slate100),
          child,
        ],
      ),
    );
  }

  Widget _buildAddPhotoBtn() {
    return InkWell(
      onTap: _pickImages,
      borderRadius: BorderRadius.circular(AppRadius.md),
      child: Container(
        width: 90,
        height: 90,
        decoration: BoxDecoration(
          color: AppColors.slate50,
          borderRadius: BorderRadius.circular(AppRadius.md),
          border: Border.all(color: AppColors.slate300, style: BorderStyle.solid),
        ),
        child: const Icon(Icons.add_a_photo_outlined, color: AppColors.slate400, size: 28),
      ),
    );
  }

  Widget _buildConditionChoice(String value, String label) {
    final isSelected = _selectedCondition == value;
    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      onSelected: (_) => setState(() => _selectedCondition = value),
      selectedColor: AppColors.brand500,
      labelStyle: TextStyle(
        color: isSelected ? Colors.white : AppColors.slate700,
        fontWeight: FontWeight.w600,
        fontSize: 12,
      ),
    );
  }
}
