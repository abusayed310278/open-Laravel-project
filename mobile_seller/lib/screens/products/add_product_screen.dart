import 'dart:io';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/custom_text_field.dart';
import '../../data/models/product_meta.dart';
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
  final _shortDescriptionController = TextEditingController();
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

  // Dynamic category attributes state
  final Map<int, int?> _attributeSelectValues = {};
  final Map<int, bool> _attributeBoolValues = {};
  final Map<int, TextEditingController> _attributeTextControllers = {};

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
    _shortDescriptionController.dispose();
    _descriptionController.dispose();
    _priceController.dispose();
    _comparePriceController.dispose();
    _quantityController.dispose();
    _skuController.dispose();
    _shippingFlatRateController.dispose();
    for (final controller in _attributeTextControllers.values) {
      controller.dispose();
    }
    super.dispose();
  }

  TextEditingController _getAttributeTextController(int attrId, [String initialValue = '']) {
    return _attributeTextControllers.putIfAbsent(
      attrId,
      () => TextEditingController(text: initialValue),
    );
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
      AppSnackBar.warning(context, 'Please select a product category');
      return;
    }

    final metaProvider = context.read<SellerProductProvider>();
    final selectedCat = metaProvider.categories.where((c) => c.id == _selectedCategoryId).firstOrNull;
    final categoryAttributes = selectedCat?.attributes ?? [];

    // Validate required category attributes
    for (final attr in categoryAttributes) {
      if (attr.isRequired) {
        if (attr.isSelect) {
          if (_attributeSelectValues[attr.id] == null) {
            AppSnackBar.warning(context, 'Please select "${attr.name}"');
            return;
          }
        } else if (attr.isBoolean) {
          if (_attributeBoolValues[attr.id] == null) {
            AppSnackBar.warning(context, 'Please specify "${attr.name}"');
            return;
          }
        } else {
          final text = _attributeTextControllers[attr.id]?.text.trim();
          if (text == null || text.isEmpty) {
            AppSnackBar.warning(context, 'Please enter "${attr.name}"');
            return;
          }
        }
      }
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

      if (_shortDescriptionController.text.trim().isNotEmpty) {
        fields['short_description'] = _shortDescriptionController.text.trim();
      }

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

      // Add dynamic category attributes
      for (final attr in categoryAttributes) {
        if (attr.isSelect) {
          final selId = _attributeSelectValues[attr.id];
          if (selId != null) {
            fields['attributes[${attr.id}]'] = selId.toString();
          }
        } else if (attr.isBoolean) {
          final b = _attributeBoolValues[attr.id];
          if (b != null) {
            fields['attributes[${attr.id}]'] = b ? '1' : '0';
          }
        } else {
          final text = _attributeTextControllers[attr.id]?.text.trim();
          if (text != null && text.isNotEmpty) {
            fields['attributes[${attr.id}]'] = text;
          }
        }
      }

      final productProvider = context.read<SellerProductProvider>();
      final files = <http.MultipartFile>[];
      for (final img in _selectedImages) {
        files.add(await http.MultipartFile.fromPath('images[]', img.path));
      }

      final success = await productProvider.createProduct(fields: fields, images: files);

      if (!mounted) return;

      if (success) {
        AppSnackBar.success(context, 'Listing created and submitted for review!');
        Navigator.of(context).pop();
      } else {
        AppSnackBar.error(
          context,
          productProvider.errorMessage ?? 'Failed to create listing',
        );
      }
    } catch (e) {
      if (mounted) {
        AppSnackBar.error(context, e.toString());
      }
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final metaProvider = context.watch<SellerProductProvider>();
    final selectedCat = metaProvider.categories.where((c) => c.id == _selectedCategoryId).firstOrNull;
    final categoryAttributes = selectedCat?.attributes ?? [];

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
                          separatorBuilder: (_, _) => const SizedBox(width: 8),
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
                      onChanged: (val) {
                        if (_selectedCategoryId != val) {
                          setState(() {
                            _selectedCategoryId = val;
                            _attributeSelectValues.clear();
                            _attributeBoolValues.clear();
                            for (final c in _attributeTextControllers.values) {
                              c.dispose();
                            }
                            _attributeTextControllers.clear();
                          });
                        }
                      },
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

              // 3. Dynamic Attributes & Specifications
              _buildAttributesSection(categoryAttributes),
              const SizedBox(height: AppSpacing.lg),

              // 4. Pricing & Condition
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

              // 5. Inventory & Shipping
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

              // 6. Description (Short & Detailed Description)
              _buildSectionCard(
                title: 'Description',
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    CustomTextField(
                      controller: _shortDescriptionController,
                      label: 'Short Description',
                      hint: 'Brief summary or key highlights (e.g. 256GB Titanium, includes AppleCare)...',
                      maxLines: 2,
                    ),
                    const SizedBox(height: AppSpacing.md),
                    CustomTextField(
                      controller: _descriptionController,
                      label: 'Detailed Description',
                      hint: 'Provide technical details, warranty, accessories included...',
                      maxLines: 5,
                    ),
                  ],
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

  Widget _buildAttributesSection(List<AttributeOption> attributes) {
    return _buildSectionCard(
      title: 'Attributes & Specifications',
      child: _selectedCategoryId == null
          ? Container(
              padding: const EdgeInsets.all(AppSpacing.md),
              decoration: BoxDecoration(
                color: AppColors.slate50,
                borderRadius: BorderRadius.circular(AppRadius.md),
                border: Border.all(color: AppColors.slate200),
              ),
              child: Row(
                children: const [
                  Icon(Icons.info_outline, size: 20, color: AppColors.slate400),
                  SizedBox(width: AppSpacing.sm),
                  Expanded(
                    child: Text(
                      'Select a category above to load its specific attributes and technical specifications.',
                      style: TextStyle(fontSize: 12, color: AppColors.slate500),
                    ),
                  ),
                ],
              ),
            )
          : attributes.isEmpty
              ? Container(
                  padding: const EdgeInsets.all(AppSpacing.md),
                  decoration: BoxDecoration(
                    color: AppColors.slate50,
                    borderRadius: BorderRadius.circular(AppRadius.md),
                  ),
                  child: Row(
                    children: const [
                      Icon(Icons.check_circle_outline, size: 20, color: AppColors.slate400),
                      SizedBox(width: AppSpacing.sm),
                      Expanded(
                        child: Text(
                          'No custom attributes required for this category.',
                          style: TextStyle(fontSize: 12, color: AppColors.slate500),
                        ),
                      ),
                    ],
                  ),
                )
              : Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: _buildGroupedAttributeWidgets(attributes),
                ),
    );
  }

  List<Widget> _buildGroupedAttributeWidgets(List<AttributeOption> attributes) {
    final Map<String, List<AttributeOption>> grouped = {};
    for (final attr in attributes) {
      final groupName = attr.group ?? 'General Specifications';
      grouped.putIfAbsent(groupName, () => []).add(attr);
    }

    final widgets = <Widget>[];
    int groupIndex = 0;

    grouped.forEach((groupName, groupAttrs) {
      if (groupIndex > 0) {
        widgets.add(const SizedBox(height: AppSpacing.lg));
        widgets.add(const Divider(height: 1, color: AppColors.slate100));
        widgets.add(const SizedBox(height: AppSpacing.md));
      }

      widgets.add(
        Padding(
          padding: const EdgeInsets.only(bottom: 10),
          child: Row(
            children: [
              Container(
                width: 6,
                height: 6,
                decoration: const BoxDecoration(
                  color: AppColors.brand500,
                  shape: BoxShape.circle,
                ),
              ),
              const SizedBox(width: 8),
              Text(
                groupName.toUpperCase(),
                style: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w700,
                  letterSpacing: 0.5,
                  color: AppColors.slate500,
                ),
              ),
            ],
          ),
        ),
      );

      for (int i = 0; i < groupAttrs.length; i++) {
        final attr = groupAttrs[i];
        widgets.add(_buildSingleAttributeField(attr));
        if (i < groupAttrs.length - 1) {
          widgets.add(const SizedBox(height: AppSpacing.md));
        }
      }

      groupIndex++;
    });

    return widgets;
  }

  Widget _buildSingleAttributeField(AttributeOption attr) {
    final labelWidget = Row(
      children: [
        Text(
          attr.name,
          style: const TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w600,
            color: AppColors.slate700,
          ),
        ),
        if (attr.isRequired) ...[
          const SizedBox(width: 4),
          const Text(
            '*',
            style: TextStyle(
              color: AppColors.danger,
              fontWeight: FontWeight.bold,
              fontSize: 13,
            ),
          ),
        ],
      ],
    );

    if (attr.isSelect) {
      return Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          labelWidget,
          const SizedBox(height: 6),
          DropdownButtonFormField<int>(
            value: _attributeSelectValues[attr.id],
            hint: Text(
              'Select ${attr.name}',
              style: const TextStyle(fontSize: 14, color: AppColors.slate400),
            ),
            items: attr.values.map((v) {
              return DropdownMenuItem<int>(
                value: v.id,
                child: Text(v.value),
              );
            }).toList(),
            onChanged: (val) {
              setState(() => _attributeSelectValues[attr.id] = val);
            },
            decoration: const InputDecoration(
              contentPadding: EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            ),
          ),
        ],
      );
    }

    if (attr.isBoolean) {
      final currentVal = _attributeBoolValues[attr.id];
      return Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          labelWidget,
          const SizedBox(height: 6),
          Row(
            children: [
              ChoiceChip(
                label: const Text('Yes'),
                selected: currentVal == true,
                selectedColor: AppColors.brand500,
                labelStyle: TextStyle(
                  color: currentVal == true ? Colors.white : AppColors.slate700,
                  fontWeight: FontWeight.w600,
                  fontSize: 12,
                ),
                onSelected: (_) {
                  setState(() => _attributeBoolValues[attr.id] = true);
                },
              ),
              const SizedBox(width: 8),
              ChoiceChip(
                label: const Text('No'),
                selected: currentVal == false,
                selectedColor: AppColors.brand500,
                labelStyle: TextStyle(
                  color: currentVal == false ? Colors.white : AppColors.slate700,
                  fontWeight: FontWeight.w600,
                  fontSize: 12,
                ),
                onSelected: (_) {
                  setState(() => _attributeBoolValues[attr.id] = false);
                },
              ),
            ],
          ),
        ],
      );
    }

    final controller = _getAttributeTextController(attr.id);
    return CustomTextField(
      controller: controller,
      label: '${attr.name}${attr.isRequired ? ' *' : ''}',
      hint: attr.placeholder ?? (attr.unit != null && attr.unit!.isNotEmpty ? 'e.g. ${attr.unit}' : 'Enter ${attr.name}'),
      keyboardType: attr.isNumber
          ? const TextInputType.numberWithOptions(decimal: true)
          : TextInputType.text,
      suffixIcon: attr.unit != null && attr.unit!.isNotEmpty
          ? Padding(
              padding: const EdgeInsets.only(right: 12),
              child: Center(
                widthFactor: 1,
                child: Text(
                  attr.unit!,
                  style: const TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: AppColors.slate500,
                  ),
                ),
              ),
            )
          : null,
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
