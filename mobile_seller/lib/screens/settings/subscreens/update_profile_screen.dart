import 'dart:io';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';

import '../../../core/network/api_client.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_spacing.dart';
import '../../../core/widgets/app_snackbar.dart';
import '../../../core/widgets/custom_button.dart';
import '../../../core/widgets/custom_text_field.dart';
import '../../../providers/seller_auth_provider.dart';

class UpdateProfileScreen extends StatefulWidget {
  const UpdateProfileScreen({super.key});

  @override
  State<UpdateProfileScreen> createState() => _UpdateProfileScreenState();
}

class _UpdateProfileScreenState extends State<UpdateProfileScreen> {
  final ApiClient _apiClient = ApiClient();
  final _formKey = GlobalKey<FormState>();
  final ImagePicker _picker = ImagePicker();

  late TextEditingController _nameController;
  late TextEditingController _companyController;
  late TextEditingController _locationController;
  late TextEditingController _emailController;
  late TextEditingController _phoneController;

  XFile? _selectedPhoto;
  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    final user = context.read<SellerAuthProvider>().user;
    _nameController = TextEditingController(text: user?.name ?? '');
    _companyController = TextEditingController(text: user?.company ?? user?.storeName ?? '');
    _locationController = TextEditingController(text: user?.location ?? user?.address ?? '');
    _emailController = TextEditingController(text: user?.email ?? '');
    _phoneController = TextEditingController(text: user?.phone ?? '');
  }

  @override
  void dispose() {
    _nameController.dispose();
    _companyController.dispose();
    _locationController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    super.dispose();
  }

  Future<void> _pickPhoto() async {
    try {
      final picked = await _picker.pickImage(source: ImageSource.gallery, imageQuality: 85);
      if (picked != null) {
        setState(() => _selectedPhoto = picked);
      }
    } catch (_) {}
  }

  Future<void> _handleSave() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isSaving = true);

    try {
      final fields = <String, String>{
        'name': _nameController.text.trim(),
        'email': _emailController.text.trim(),
        'company': _companyController.text.trim(),
        'location': _locationController.text.trim(),
        'phone': _phoneController.text.trim(),
      };

      if (_selectedPhoto != null) {
        final file = await http.MultipartFile.fromPath('photo', _selectedPhoto!.path);
        await _apiClient.multipart(
          '/profile',
          fields: fields,
          files: [file],
        );
      } else {
        await _apiClient.post(
          '/profile',
          body: fields,
        );
      }

      if (!mounted) return;
      await context.read<SellerAuthProvider>().refreshProfile();

      if (mounted) {
        AppSnackBar.success(context, 'Profile updated successfully!');
      }
    } catch (e) {
      if (mounted) {
        AppSnackBar.error(context, 'Failed to update: $e');
      }
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<SellerAuthProvider>().user;

    return Scaffold(
      backgroundColor: AppColors.surface,
      appBar: AppBar(title: const Text('Update Profile')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppSpacing.lg),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // ─── Choose Photo Section ───
              Center(
                child: Stack(
                  clipBehavior: Clip.none,
                  children: [
                    Container(
                      width: 96,
                      height: 96,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: AppColors.slate100,
                        border: Border.all(color: AppColors.slate200, width: 2),
                      ),
                      child: ClipOval(
                        child: _selectedPhoto != null
                            ? Image.file(
                                File(_selectedPhoto!.path),
                                fit: BoxFit.cover,
                                width: 96,
                                height: 96,
                              )
                            : (user?.logoUrl != null
                                ? Image.network(
                                    user!.logoUrl!,
                                    fit: BoxFit.cover,
                                    width: 96,
                                    height: 96,
                                    errorBuilder: (_, _, _) => _buildInitialFallback(user.name),
                                  )
                                : _buildInitialFallback(user?.name)),
                      ),
                    ),
                    Positioned(
                      bottom: 0,
                      right: 0,
                      child: InkWell(
                        onTap: _pickPhoto,
                        borderRadius: BorderRadius.circular(20),
                        child: Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: AppColors.brand500,
                            shape: BoxShape.circle,
                            border: Border.all(color: Colors.white, width: 2),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withValues(alpha: 0.12),
                                blurRadius: 4,
                                offset: const Offset(0, 2),
                              ),
                            ],
                          ),
                          child: const Icon(Icons.camera_alt_rounded, size: 16, color: Colors.white),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 8),
              Center(
                child: TextButton.icon(
                  onPressed: _pickPhoto,
                  icon: const Icon(Icons.photo_library_outlined, size: 16, color: AppColors.brand600),
                  label: const Text(
                    'Choose Photo',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.brand600),
                  ),
                ),
              ),

              const SizedBox(height: AppSpacing.lg),
              const Text('Account Details', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.ink)),
              const SizedBox(height: AppSpacing.md),

              CustomTextField(
                controller: _nameController,
                label: 'Full Name',
                validator: (v) => v == null || v.trim().isEmpty ? 'Required' : null,
              ),
              const SizedBox(height: AppSpacing.md),

              CustomTextField(
                controller: _companyController,
                label: 'Company / Business Name',
                hint: 'e.g. My Store Trading LLC',
              ),
              const SizedBox(height: AppSpacing.md),

              CustomTextField(
                controller: _locationController,
                label: 'Location / Operating Address',
                hint: 'e.g. Doha, Qatar',
              ),
              const SizedBox(height: AppSpacing.md),

              CustomTextField(
                controller: _emailController,
                label: 'Email Address',
                keyboardType: TextInputType.emailAddress,
                hint: 'e.g. seller@example.com',
                validator: (v) {
                  if (v != null && v.trim().isNotEmpty && !v.contains('@')) {
                    return 'Enter a valid email address';
                  }
                  return null;
                },
              ),
              const SizedBox(height: AppSpacing.md),

              CustomTextField(
                controller: _phoneController,
                label: 'Phone Number',
                hint: '+974 0000 0000',
              ),
              const SizedBox(height: AppSpacing.xxl),

              CustomButton(
                text: 'Save Profile Changes',
                isLoading: _isSaving,
                onPressed: _handleSave,
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildInitialFallback(String? name) {
    return Center(
      child: Text(
        name != null && name.isNotEmpty ? name[0].toUpperCase() : 'U',
        style: const TextStyle(fontSize: 34, fontWeight: FontWeight.w700, color: AppColors.brand700),
      ),
    );
  }
}
