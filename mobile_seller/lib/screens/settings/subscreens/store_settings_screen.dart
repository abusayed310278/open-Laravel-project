import 'dart:convert';
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
import '../../../data/models/seller_user.dart';
import '../../../providers/seller_auth_provider.dart';

class StoreSettingsScreen extends StatefulWidget {
  const StoreSettingsScreen({super.key});

  @override
  State<StoreSettingsScreen> createState() => _StoreSettingsScreenState();
}

class _StoreSettingsScreenState extends State<StoreSettingsScreen> {
  final ApiClient _apiClient = ApiClient();
  final _formKey = GlobalKey<FormState>();
  final ImagePicker _picker = ImagePicker();

  late TextEditingController _storeNameController;
  late TextEditingController _slugController;
  late TextEditingController _descController;
  late TextEditingController _addressController;
  late TextEditingController _cityController;
  late TextEditingController _countryController;
  late TextEditingController _latitudeController;
  late TextEditingController _longitudeController;
  late TextEditingController _phoneController;
  late TextEditingController _websiteController;
  late TextEditingController _timezoneController;

  // Social Links
  late TextEditingController _instagramController;
  late TextEditingController _facebookController;
  late TextEditingController _tiktokController;
  late TextEditingController _youtubeController;
  late TextEditingController _twitterController;

  XFile? _selectedLogo;
  XFile? _selectedCover;
  String? _currentLogoUrl;
  String? _currentCoverUrl;

  bool _isStoreActive = true;
  bool _isSaving = false;
  bool _isLoading = false;

  final List<String> _weekDays = [
    'monday',
    'tuesday',
    'wednesday',
    'thursday',
    'friday',
    'saturday',
    'sunday',
  ];

  late Map<String, Map<String, dynamic>> _businessHours;

  @override
  void initState() {
    super.initState();
    final user = context.read<SellerAuthProvider>().user;

    _storeNameController = TextEditingController(text: user?.storeName ?? '');
    _slugController = TextEditingController(text: user?.storeSlug ?? '');
    _descController = TextEditingController(text: user?.description ?? '');
    _addressController = TextEditingController(text: user?.address ?? user?.location ?? '');
    _cityController = TextEditingController(text: user?.city ?? '');
    _countryController = TextEditingController(text: user?.country ?? 'Qatar');
    _latitudeController = TextEditingController(text: user?.latitude != null ? user!.latitude.toString() : '');
    _longitudeController = TextEditingController(text: user?.longitude != null ? user!.longitude.toString() : '');
    _phoneController = TextEditingController(text: user?.phone ?? '');
    _websiteController = TextEditingController(text: user?.website ?? '');
    _timezoneController = TextEditingController(text: user?.timezone ?? 'Asia/Qatar');
    _isStoreActive = user?.isStoreActive ?? true;
    _currentLogoUrl = user?.logoUrl;
    _currentCoverUrl = user?.coverUrl;

    _instagramController = TextEditingController();
    _facebookController = TextEditingController();
    _tiktokController = TextEditingController();
    _youtubeController = TextEditingController();
    _twitterController = TextEditingController();

    _businessHours = {};
    for (final day in _weekDays) {
      _businessHours[day] = {
        'enabled': true,
        'open': '08:00',
        'close': '17:00',
      };
    }

    if (user != null) {
      _applyUserData(user);
    }

    _loadProfile();
  }

  void _applyUserData(SellerUser user) {
    _storeNameController.text = user.storeName;
    _slugController.text = user.storeSlug;
    _descController.text = user.description ?? '';
    _addressController.text = user.address ?? user.location ?? '';
    _cityController.text = user.city ?? '';
    _countryController.text = user.country ?? 'Qatar';
    _latitudeController.text = user.latitude != null ? user.latitude.toString() : '';
    _longitudeController.text = user.longitude != null ? user.longitude.toString() : '';
    _phoneController.text = user.phone ?? '';
    _websiteController.text = user.website ?? '';
    _timezoneController.text = user.timezone ?? 'Asia/Qatar';
    _isStoreActive = user.isStoreActive;
    _currentLogoUrl = user.logoUrl;
    _currentCoverUrl = user.coverUrl;

    String getSocialUrl(String platform) {
      if (user.socialLinks == null) return '';
      for (final item in user.socialLinks!) {
        if (item is Map && item['platform']?.toString().toLowerCase() == platform) {
          return item['url']?.toString() ?? '';
        }
      }
      return '';
    }

    _instagramController.text = getSocialUrl('instagram');
    _facebookController.text = getSocialUrl('facebook');
    _tiktokController.text = getSocialUrl('tiktok');
    _youtubeController.text = getSocialUrl('youtube');
    _twitterController.text = getSocialUrl('twitter');

    final savedHours = user.businessHours;
    for (final day in _weekDays) {
      final row = savedHours != null && savedHours[day] is Map ? savedHours[day] as Map : null;
      _businessHours[day] = {
        'enabled': row != null && row.containsKey('enabled') ? row['enabled'] == true : true,
        'open': row?['open']?.toString() ?? '08:00',
        'close': row?['close']?.toString() ?? '17:00',
      };
    }
  }

  Future<void> _loadProfile() async {
    setState(() => _isLoading = true);
    try {
      final res = await _apiClient.get('/profile');
      if (res is Map<String, dynamic> && res.containsKey('profile')) {
        final profileMap = res['profile'] as Map<String, dynamic>;
        final fetchedUser = SellerUser.fromJson(profileMap);
        if (mounted) {
          setState(() {
            _applyUserData(fetchedUser);
          });
        }
      }
    } catch (_) {}
    if (mounted) {
      setState(() => _isLoading = false);
    }
  }

  @override
  void dispose() {
    _storeNameController.dispose();
    _slugController.dispose();
    _descController.dispose();
    _addressController.dispose();
    _cityController.dispose();
    _countryController.dispose();
    _latitudeController.dispose();
    _longitudeController.dispose();
    _phoneController.dispose();
    _websiteController.dispose();
    _timezoneController.dispose();
    _instagramController.dispose();
    _facebookController.dispose();
    _tiktokController.dispose();
    _youtubeController.dispose();
    _twitterController.dispose();
    super.dispose();
  }

  Future<void> _pickLogo() async {
    try {
      final picked = await _picker.pickImage(source: ImageSource.gallery, imageQuality: 85);
      if (picked != null) {
        setState(() => _selectedLogo = picked);
      }
    } catch (_) {}
  }

  Future<void> _pickCover() async {
    try {
      final picked = await _picker.pickImage(source: ImageSource.gallery, imageQuality: 85);
      if (picked != null) {
        setState(() => _selectedCover = picked);
      }
    } catch (_) {}
  }

  void _applyDefaultHoursToAll() {
    setState(() {
      for (final day in _weekDays) {
        _businessHours[day] = {
          'enabled': true,
          'open': '08:00',
          'close': '17:00',
        };
      }
    });
    AppSnackBar.info(context, 'Applied 08:00 – 17:00 to all days');
  }

  Future<void> _selectTime(String day, String field) async {
    final currentStr = _businessHours[day]?[field] ?? '08:00';
    final parts = currentStr.split(':');
    final initialHour = parts.isNotEmpty ? int.tryParse(parts[0]) ?? 8 : 8;
    final initialMinute = parts.length > 1 ? int.tryParse(parts[1]) ?? 0 : 0;

    final picked = await showTimePicker(
      context: context,
      initialTime: TimeOfDay(hour: initialHour, minute: initialMinute),
    );

    if (picked != null) {
      final hourStr = picked.hour.toString().padLeft(2, '0');
      final minuteStr = picked.minute.toString().padLeft(2, '0');
      setState(() {
        _businessHours[day]?[field] = '$hourStr:$minuteStr';
      });
    }
  }

  Future<void> _handleSave() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isSaving = true);

    try {
      final socialLinksList = <Map<String, String>>[];
      if (_instagramController.text.trim().isNotEmpty) {
        socialLinksList.add({'platform': 'instagram', 'url': _instagramController.text.trim()});
      }
      if (_facebookController.text.trim().isNotEmpty) {
        socialLinksList.add({'platform': 'facebook', 'url': _facebookController.text.trim()});
      }
      if (_tiktokController.text.trim().isNotEmpty) {
        socialLinksList.add({'platform': 'tiktok', 'url': _tiktokController.text.trim()});
      }
      if (_youtubeController.text.trim().isNotEmpty) {
        socialLinksList.add({'platform': 'youtube', 'url': _youtubeController.text.trim()});
      }
      if (_twitterController.text.trim().isNotEmpty) {
        socialLinksList.add({'platform': 'twitter', 'url': _twitterController.text.trim()});
      }

      final fields = <String, String>{
        'store_name': _storeNameController.text.trim(),
        'store_slug': _slugController.text.trim(),
        'description': _descController.text.trim(),
        'address': _addressController.text.trim(),
        'city': _cityController.text.trim(),
        'country': _countryController.text.trim(),
        'phone': _phoneController.text.trim(),
        'website': _websiteController.text.trim(),
        'timezone': _timezoneController.text.trim(),
        'is_store_active': _isStoreActive ? '1' : '0',
        'business_hours': jsonEncode(_businessHours),
        'social_links': jsonEncode(socialLinksList),
      };

      if (_latitudeController.text.trim().isNotEmpty) {
        fields['latitude'] = _latitudeController.text.trim();
      }
      if (_longitudeController.text.trim().isNotEmpty) {
        fields['longitude'] = _longitudeController.text.trim();
      }

      final files = <http.MultipartFile>[];
      if (_selectedLogo != null) {
        files.add(await http.MultipartFile.fromPath('logo', _selectedLogo!.path));
      }
      if (_selectedCover != null) {
        files.add(await http.MultipartFile.fromPath('cover_image', _selectedCover!.path));
      }

      if (files.isNotEmpty) {
        await _apiClient.multipart(
          '/profile',
          fields: fields,
          files: files,
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
        AppSnackBar.success(context, 'Store settings updated successfully!');
      }
    } catch (e) {
      if (mounted) {
        AppSnackBar.error(context, 'Failed to update: $e');
      }
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  Widget _buildSectionCard({required String title, required Widget child, Widget? trailing}) {
    return Container(
      margin: const EdgeInsets.only(bottom: AppSpacing.lg),
      padding: const EdgeInsets.all(AppSpacing.lg),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.slate200),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                title,
                style: const TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w800,
                  color: AppColors.ink,
                ),
              ),
              ?trailing,
            ],
          ),
          const SizedBox(height: AppSpacing.md),
          child,
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<SellerAuthProvider>().user;
    final activeCover = _currentCoverUrl ?? user?.coverUrl;
    final activeLogo = _currentLogoUrl ?? user?.logoUrl;

    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(title: const Text('Store Settings')),
      body: Stack(
        children: [
          SingleChildScrollView(
            padding: const EdgeInsets.all(AppSpacing.lg),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // ─── 1. Store Identity & Visual Branding Card ───
                  _buildSectionCard(
                    title: 'Store Branding & Identity',
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        // Cover Banner Upload
                        const Text('Cover Photo / Store Banner', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.slate700)),
                        const SizedBox(height: 6),
                        GestureDetector(
                          onTap: _pickCover,
                          child: Container(
                            height: 120,
                            width: double.infinity,
                            decoration: BoxDecoration(
                              color: AppColors.slate900,
                              borderRadius: BorderRadius.circular(AppRadius.md),
                              border: Border.all(color: AppColors.slate200),
                            ),
                            clipBehavior: Clip.antiAlias,
                            child: Stack(
                              fit: StackFit.expand,
                              children: [
                                if (_selectedCover != null)
                                  Image.file(File(_selectedCover!.path), fit: BoxFit.cover)
                                else if (activeCover != null)
                                  Image.network(activeCover, fit: BoxFit.cover, errorBuilder: (_, _, _) => Container(color: AppColors.slate800))
                                else
                                  Container(color: AppColors.slate800),
                                Container(
                                  color: Colors.black.withValues(alpha: 0.35),
                                  alignment: Alignment.center,
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: const [
                                      Icon(Icons.camera_alt_outlined, color: Colors.white, size: 18),
                                      SizedBox(width: 6),
                                      Text('Change Banner', style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w700)),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(height: AppSpacing.md),

                        // Logo Upload
                        const Text('Store Logo', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.slate700)),
                        const SizedBox(height: 6),
                        Row(
                          children: [
                            GestureDetector(
                              onTap: _pickLogo,
                              child: Container(
                                width: 72,
                                height: 72,
                                decoration: BoxDecoration(
                                  color: AppColors.slate100,
                                  borderRadius: BorderRadius.circular(AppRadius.md),
                                  border: Border.all(color: AppColors.slate200),
                                ),
                                clipBehavior: Clip.antiAlias,
                                child: Stack(
                                  fit: StackFit.expand,
                                  children: [
                                    if (_selectedLogo != null)
                                      Image.file(File(_selectedLogo!.path), fit: BoxFit.cover)
                                    else if (activeLogo != null)
                                      Image.network(activeLogo, fit: BoxFit.cover, errorBuilder: (_, _, _) => const Icon(Icons.storefront, color: AppColors.brand500, size: 30))
                                    else
                                      const Icon(Icons.storefront, color: AppColors.brand500, size: 30),
                                    Container(
                                      color: Colors.black.withValues(alpha: 0.25),
                                      alignment: Alignment.bottomRight,
                                      padding: const EdgeInsets.all(4),
                                      child: const Icon(Icons.edit, color: Colors.white, size: 14),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                            const SizedBox(width: AppSpacing.md),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  TextButton.icon(
                                    onPressed: _pickLogo,
                                    icon: const Icon(Icons.photo_library_outlined, size: 16),
                                    label: const Text('Choose Store Logo'),
                                    style: TextButton.styleFrom(
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                      backgroundColor: AppColors.brand50,
                                      foregroundColor: AppColors.brand700,
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  const Text('Square 400×400px recommended', style: TextStyle(fontSize: 11, color: AppColors.slate400)),
                                ],
                              ),
                            ),
                          ],
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
                      hint: 'e.g. my-store-slug',
                    ),
                    const SizedBox(height: AppSpacing.md),

                    CustomTextField(
                      controller: _descController,
                      label: 'Store Bio / Description',
                      hint: 'Tell customers about your shop, specialty and offerings...',
                      maxLines: 3,
                    ),
                  ],
                ),
              ),

              // ─── 2. Location & Contact Details Card ───
              _buildSectionCard(
                title: 'Location & Contact Details',
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    CustomTextField(
                      controller: _addressController,
                      label: 'Physical Store Address',
                      hint: 'e.g. West Bay, Building 14, St 950',
                    ),
                    const SizedBox(height: AppSpacing.md),

                    Row(
                      children: [
                        Expanded(
                          child: CustomTextField(
                            controller: _cityController,
                            label: 'City',
                            hint: 'e.g. Doha',
                          ),
                        ),
                        const SizedBox(width: AppSpacing.md),
                        Expanded(
                          child: CustomTextField(
                            controller: _countryController,
                            label: 'Country',
                            hint: 'e.g. Qatar',
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: AppSpacing.md),

                    Row(
                      children: [
                        Expanded(
                          child: CustomTextField(
                            controller: _latitudeController,
                            label: 'Latitude',
                            keyboardType: const TextInputType.numberWithOptions(decimal: true, signed: true),
                            hint: 'e.g. 25.2867',
                          ),
                        ),
                        const SizedBox(width: AppSpacing.md),
                        Expanded(
                          child: CustomTextField(
                            controller: _longitudeController,
                            label: 'Longitude',
                            keyboardType: const TextInputType.numberWithOptions(decimal: true, signed: true),
                            hint: 'e.g. 51.5333',
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: AppSpacing.md),

                    CustomTextField(
                      controller: _phoneController,
                      label: 'Store Contact Phone',
                      keyboardType: TextInputType.phone,
                      hint: 'e.g. +974 5555 1234',
                    ),
                    const SizedBox(height: AppSpacing.md),

                    CustomTextField(
                      controller: _websiteController,
                      label: 'Store Website URL',
                      keyboardType: TextInputType.url,
                      hint: 'https://mystore.com',
                    ),
                  ],
                ),
              ),

              // ─── 3. Store Visibility Switch Card ───
              _buildSectionCard(
                title: 'Store Public Visibility',
                child: SwitchListTile(
                  contentPadding: EdgeInsets.zero,
                  value: _isStoreActive,
                  activeThumbColor: AppColors.brand500,
                  title: const Text('Store is Publicly Visible', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700)),
                  subtitle: const Text('When disabled, your store and listings are hidden from public view', style: TextStyle(fontSize: 12, color: AppColors.slate500)),
                  onChanged: (val) => setState(() => _isStoreActive = val),
                ),
              ),

              // ─── 4. Operating Hours Card ───
              _buildSectionCard(
                title: 'Business Operating Hours',
                trailing: TextButton.icon(
                  onPressed: _applyDefaultHoursToAll,
                  icon: const Icon(Icons.copy_all_rounded, size: 14),
                  label: const Text('Apply 08-17 All', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700)),
                  style: TextButton.styleFrom(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    backgroundColor: AppColors.brand50,
                    foregroundColor: AppColors.brand700,
                  ),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    CustomTextField(
                      controller: _timezoneController,
                      label: 'Operating Timezone',
                      hint: 'e.g. Asia/Qatar',
                    ),
                    const SizedBox(height: AppSpacing.md),

                    ..._weekDays.map((day) {
                      final item = _businessHours[day] ?? {'enabled': true, 'open': '08:00', 'close': '17:00'};
                      final isEnabled = item['enabled'] == true;
                      final openTime = item['open']?.toString() ?? '08:00';
                      final closeTime = item['close']?.toString() ?? '17:00';

                      return Container(
                        margin: const EdgeInsets.only(bottom: 6),
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                        decoration: BoxDecoration(
                          color: isEnabled ? AppColors.slate50 : AppColors.surface,
                          borderRadius: BorderRadius.circular(AppRadius.sm),
                          border: Border.all(color: AppColors.slate200),
                        ),
                        child: Row(
                          children: [
                            Checkbox(
                              value: isEnabled,
                              activeColor: AppColors.brand500,
                              onChanged: (v) {
                                setState(() {
                                  _businessHours[day]?['enabled'] = v == true;
                                });
                              },
                            ),
                            SizedBox(
                              width: 85,
                              child: Text(
                                day[0].toUpperCase() + day.substring(1),
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w700,
                                  color: isEnabled ? AppColors.ink : AppColors.slate400,
                                ),
                              ),
                            ),
                            const Spacer(),
                            if (isEnabled) ...[
                              InkWell(
                                onTap: () => _selectTime(day, 'open'),
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: Colors.white,
                                    borderRadius: BorderRadius.circular(4),
                                    border: Border.all(color: AppColors.slate300),
                                  ),
                                  child: Text(openTime, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                                ),
                              ),
                              const Padding(
                                padding: EdgeInsets.symmetric(horizontal: 4),
                                child: Text('–', style: TextStyle(color: AppColors.slate400)),
                              ),
                              InkWell(
                                onTap: () => _selectTime(day, 'close'),
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: Colors.white,
                                    borderRadius: BorderRadius.circular(4),
                                    border: Border.all(color: AppColors.slate300),
                                  ),
                                  child: Text(closeTime, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                                ),
                              ),
                            ] else
                              const Text('Closed', style: TextStyle(fontSize: 12, color: AppColors.slate400, fontStyle: FontStyle.italic)),
                          ],
                        ),
                      );
                    }),
                  ],
                ),
              ),

              // ─── 5. Social Media Profiles Card ───
              _buildSectionCard(
                title: 'Social Media Profiles',
                child: Column(
                  children: [
                    CustomTextField(
                      controller: _instagramController,
                      label: 'Instagram',
                      hint: 'https://instagram.com/yourstore',
                      prefixIcon: const Icon(Icons.camera_alt, size: 18, color: AppColors.brand600),
                    ),
                    const SizedBox(height: AppSpacing.md),

                    CustomTextField(
                      controller: _facebookController,
                      label: 'Facebook',
                      hint: 'https://facebook.com/yourstore',
                      prefixIcon: const Icon(Icons.facebook, size: 18, color: AppColors.brand600),
                    ),
                    const SizedBox(height: AppSpacing.md),

                    CustomTextField(
                      controller: _tiktokController,
                      label: 'TikTok',
                      hint: 'https://tiktok.com/@yourstore',
                      prefixIcon: const Icon(Icons.music_note, size: 18, color: AppColors.brand600),
                    ),
                    const SizedBox(height: AppSpacing.md),

                    CustomTextField(
                      controller: _youtubeController,
                      label: 'YouTube',
                      hint: 'https://youtube.com/@yourstore',
                      prefixIcon: const Icon(Icons.play_circle_outline, size: 18, color: AppColors.brand600),
                    ),
                    const SizedBox(height: AppSpacing.md),

                    CustomTextField(
                      controller: _twitterController,
                      label: 'X (Twitter)',
                      hint: 'https://x.com/yourstore',
                      prefixIcon: const Icon(Icons.alternate_email, size: 18, color: AppColors.brand600),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: AppSpacing.md),
              CustomButton(
                text: 'Save Store Settings',
                isLoading: _isSaving,
                onPressed: _handleSave,
              ),
              const SizedBox(height: AppSpacing.xxl),
            ],
          ),
        ),
      ),
      if (_isLoading)
        const Positioned(
          top: 0,
          left: 0,
          right: 0,
          child: LinearProgressIndicator(
            color: AppColors.brand500,
            backgroundColor: AppColors.brand100,
          ),
        ),
    ],
  ),
);
  }
}
