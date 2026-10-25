import 'package:flutter/material.dart';

import '../../core/network/api_client.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';
import '../navigation/main_navigation_screen.dart';
import 'verify_email_otp_screen.dart';

/// Screen representing Step 1 of Onboarding: "Add Your Primary Address".
/// Replicates the web version `onboarding/address.blade.php` design and flow.
class OnboardingAddressScreen extends StatefulWidget {
  final String initialName;
  final String? initialPhone;
  final String userEmail;

  const OnboardingAddressScreen({
    super.key,
    required this.initialName,
    this.initialPhone,
    required this.userEmail,
  });

  @override
  State<OnboardingAddressScreen> createState() => _OnboardingAddressScreenState();
}

class _OnboardingAddressScreenState extends State<OnboardingAddressScreen> {
  final _formKey = GlobalKey<FormState>();
  final _apiClient = ApiClient();

  late final TextEditingController _nameController;
  late final TextEditingController _phoneController;
  final _line1Controller = TextEditingController();
  final _line2Controller = TextEditingController();
  final _countryController = TextEditingController(text: 'Qatar');
  final _stateController = TextEditingController();
  final _cityController = TextEditingController(text: 'Doha');
  final _postalCodeController = TextEditingController();

  bool _isLoading = false;

  static const List<String> _commonCountries = [
    'Qatar',
    'Bangladesh',
    'United Arab Emirates',
    'Saudi Arabia',
    'Kuwait',
    'Bahrain',
    'Oman',
    'United States',
    'United Kingdom',
    'India',
    'Pakistan',
    'Egypt',
  ];

  @override
  void initState() {
    super.initState();
    _nameController = TextEditingController(text: widget.initialName);
    _phoneController = TextEditingController(text: widget.initialPhone ?? '');
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _line1Controller.dispose();
    _line2Controller.dispose();
    _countryController.dispose();
    _stateController.dispose();
    _cityController.dispose();
    _postalCodeController.dispose();
    super.dispose();
  }

  Future<void> _handleSaveAddress() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    try {
      final response = await _apiClient.post(
        '/onboarding/address',
        body: {
          'name': _nameController.text.trim(),
          'recipient': _nameController.text.trim(),
          'phone': _phoneController.text.trim(),
          'line1': _line1Controller.text.trim(),
          'line2': _line2Controller.text.trim().isEmpty ? null : _line2Controller.text.trim(),
          'country': _countryController.text.trim(),
          'state': _stateController.text.trim().isEmpty ? null : _stateController.text.trim(),
          'city': _cityController.text.trim(),
          'postal_code': _postalCodeController.text.trim().isEmpty ? null : _postalCodeController.text.trim(),
          'is_default': true,
          'label': 'Primary',
        },
      );

      if (!mounted) return;

      AppSnackBar.success(context, 'Primary address saved! Verification code sent to your email.');

      final verifiedEmail = (response is Map && response['email'] != null)
          ? response['email'].toString()
          : widget.userEmail;

      // Navigate to Step 2: Email Verification Screen
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(
          builder: (_) => VerifyEmailOtpScreen(
            email: verifiedEmail,
          ),
        ),
      );
    } catch (e) {
      if (!mounted) return;
      AppSnackBar.error(context, e.toString());
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _showCountryPicker() {
    showModalBottomSheet(
      context: context,
      backgroundColor: AppColors.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        return SafeArea(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.md),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Select Country',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.ink),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close_rounded, size: 20),
                      onPressed: () => Navigator.of(ctx).pop(),
                    ),
                  ],
                ),
              ),
              const Divider(height: 1),
              Flexible(
                child: ListView.separated(
                  shrinkWrap: true,
                  itemCount: _commonCountries.length,
                  separatorBuilder: (context, index) => const Divider(height: 1, indent: 16, endIndent: 16),
                  itemBuilder: (_, i) {
                    final country = _commonCountries[i];
                    final isSelected = country == _countryController.text;
                    return ListTile(
                      title: Text(
                        country,
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
                          color: isSelected ? AppColors.brand700 : AppColors.ink,
                        ),
                      ),
                      trailing: isSelected
                          ? const Icon(Icons.check_rounded, color: AppColors.brand600, size: 18)
                          : null,
                      onTap: () {
                        setState(() => _countryController.text = country);
                        Navigator.of(ctx).pop();
                      },
                    );
                  },
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    // Exact accent color from the web design screenshot
    const stepYellow = Color(0xFFFACC15);
    const stepYellowDark = Color(0xFFCA8A04);
    const stepYellowLight = Color(0xFFFEF9C3);

    return Scaffold(
      backgroundColor: const Color(0xFFFCFCFD),
      body: SafeArea(
        child: GestureDetector(
          behavior: HitTestBehavior.opaque,
          onTap: () => FocusScope.of(context).unfocus(),
          child: SingleChildScrollView(
            keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.onDrag,
            padding: const EdgeInsets.symmetric(horizontal: AppSpacing.xxl, vertical: AppSpacing.lg),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Top Row: Back button & 2-Step Progress Indicator
                  Row(
                    children: [
                      IconButton(
                        padding: EdgeInsets.zero,
                        constraints: const BoxConstraints(minWidth: 32, minHeight: 32),
                        icon: const Icon(Icons.arrow_back_ios_new_rounded, size: 16, color: AppColors.slate700),
                        onPressed: () => Navigator.of(context).maybePop(),
                      ),
                      const SizedBox(width: AppSpacing.sm),
                      Expanded(
                        child: Row(
                          children: [
                            // Step 1 Active Indicator
                            Expanded(
                              child: Container(
                                height: 5,
                                decoration: BoxDecoration(
                                  color: stepYellow,
                                  borderRadius: BorderRadius.circular(4),
                                ),
                              ),
                            ),
                            const SizedBox(width: 6),
                            // Step 2 Inactive Indicator
                            Expanded(
                              child: Container(
                                height: 5,
                                decoration: BoxDecoration(
                                  color: AppColors.slate200,
                                  borderRadius: BorderRadius.circular(4),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: AppSpacing.xxl),

                  // Location Icon Badge & Header
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Container(
                        width: 48,
                        height: 48,
                        decoration: BoxDecoration(
                          color: stepYellowLight,
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: const Icon(
                          Icons.location_on_rounded,
                          color: stepYellowDark,
                          size: 24,
                        ),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: const [
                            Text(
                              'STEP 1 OF 2 · SHIPPING ADDRESS',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w800,
                                color: stepYellowDark,
                                letterSpacing: 0.8,
                              ),
                            ),
                            SizedBox(height: 4),
                            Text(
                              'Add Your Primary Address',
                              style: TextStyle(
                                fontSize: 20,
                                fontWeight: FontWeight.w800,
                                color: Color(0xFF0F172A),
                                letterSpacing: -0.4,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: AppSpacing.sm),

                  // Subtitle
                  const Padding(
                    padding: EdgeInsets.only(left: 62),
                    child: Text(
                      "Enter your primary shipping address below. Once saved, we'll send a verification link to your email.",
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w500,
                        color: AppColors.slate500,
                        height: 1.4,
                      ),
                    ),
                  ),
                  const SizedBox(height: AppSpacing.xxl),

                  // Section 1: Contact Details
                  _buildSectionHeader(
                    icon: Icons.person_outline_rounded,
                    title: 'Contact Details',
                  ),
                  const SizedBox(height: AppSpacing.md),

                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: _buildFormField(
                          controller: _nameController,
                          label: 'Full name',
                          hint: 'Enter your full name',
                          validator: (val) {
                            if (val == null || val.trim().isEmpty) return 'Enter full name';
                            return null;
                          },
                        ),
                      ),
                      const SizedBox(width: AppSpacing.md),
                      Expanded(
                        child: _buildFormField(
                          controller: _phoneController,
                          label: 'Phone',
                          hint: 'e.g. +880 1700-000000',
                          keyboardType: TextInputType.phone,
                          validator: (val) {
                            if (val == null || val.trim().isEmpty) return 'Enter phone';
                            return null;
                          },
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: AppSpacing.xxl),

                  // Section 2: Delivery Address
                  _buildSectionHeader(
                    icon: Icons.location_on_outlined,
                    title: 'Delivery Address',
                  ),
                  const SizedBox(height: AppSpacing.md),

                  // Address line 1 & Address line 2 (optional)
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: _buildFormField(
                          controller: _line1Controller,
                          label: 'Address line 1',
                          hint: 'House / Building #, Street name',
                          validator: (val) {
                            if (val == null || val.trim().isEmpty) return 'Enter address line 1';
                            return null;
                          },
                        ),
                      ),
                      const SizedBox(width: AppSpacing.md),
                      Expanded(
                        child: _buildFormField(
                          controller: _line2Controller,
                          label: 'Address line 2 (optional)',
                          hint: 'Apartment, Suite, Unit, Floor (optional)',
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: AppSpacing.md),

                  // Country & State/Area (optional)
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: _buildFormField(
                          controller: _countryController,
                          label: 'Country',
                          hint: 'Select Country',
                          readOnly: true,
                          onTap: _showCountryPicker,
                          suffixIcon: const Icon(Icons.keyboard_arrow_down_rounded, size: 20, color: AppColors.slate400),
                          validator: (val) {
                            if (val == null || val.trim().isEmpty) return 'Select country';
                            return null;
                          },
                        ),
                      ),
                      const SizedBox(width: AppSpacing.md),
                      Expanded(
                        child: _buildFormField(
                          controller: _stateController,
                          label: 'State/Area (optional)',
                          hint: 'Select State/Area',
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: AppSpacing.md),

                  // City & Postal code (optional)
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: _buildFormField(
                          controller: _cityController,
                          label: 'City',
                          hint: 'Select City',
                          validator: (val) {
                            if (val == null || val.trim().isEmpty) return 'Enter city';
                            return null;
                          },
                        ),
                      ),
                      const SizedBox(width: AppSpacing.md),
                      Expanded(
                        child: _buildFormField(
                          controller: _postalCodeController,
                          label: 'Postal code (optional)',
                          hint: 'e.g. 1207',
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: AppSpacing.xxl),

                  // Save Address & Send Verification Code Button
                  SizedBox(
                    height: 52,
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: stepYellow,
                        foregroundColor: const Color(0xFF0F172A),
                        elevation: 0,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(AppRadius.md),
                        ),
                      ),
                      onPressed: _isLoading ? null : _handleSaveAddress,
                      child: _isLoading
                          ? const SizedBox(
                              width: 22,
                              height: 22,
                              child: CircularProgressIndicator(
                                strokeWidth: 2.5,
                                valueColor: AlwaysStoppedAnimation(Color(0xFF0F172A)),
                              ),
                            )
                          : Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: const [
                                Text(
                                  'Save Address & Send Verification Code',
                                  style: TextStyle(
                                    fontSize: 14,
                                    fontWeight: FontWeight.w700,
                                    letterSpacing: -0.2,
                                  ),
                                ),
                                SizedBox(width: 8),
                                Icon(Icons.arrow_forward_rounded, size: 18),
                              ],
                            ),
                    ),
                  ),
                  const SizedBox(height: AppSpacing.lg),

                  // Skip for now link
                  Center(
                    child: TextButton(
                      onPressed: () {
                        Navigator.of(context).pushAndRemoveUntil(
                          MaterialPageRoute(builder: (_) => const MainNavigationScreen()),
                          (route) => false,
                        );
                      },
                      child: const Text(
                        'Skip for now',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                          color: AppColors.slate500,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildSectionHeader({
    required IconData icon,
    required String title,
  }) {
    const stepYellowDark = Color(0xFFCA8A04);
    const stepYellowLight = Color(0xFFFEF9C3);

    return Row(
      children: [
        Container(
          width: 28,
          height: 28,
          decoration: BoxDecoration(
            color: stepYellowLight,
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(icon, size: 16, color: stepYellowDark),
        ),
        const SizedBox(width: 10),
        Text(
          title,
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w700,
            color: AppColors.ink,
          ),
        ),
      ],
    );
  }

  Widget _buildFormField({
    required TextEditingController controller,
    required String label,
    required String hint,
    TextInputType? keyboardType,
    bool readOnly = false,
    VoidCallback? onTap,
    Widget? suffixIcon,
    String? Function(String?)? validator,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w600,
            color: AppColors.slate700,
          ),
        ),
        const SizedBox(height: 6),
        TextFormField(
          controller: controller,
          readOnly: readOnly,
          onTap: onTap,
          keyboardType: keyboardType,
          validator: validator,
          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500, color: AppColors.ink),
          decoration: InputDecoration(
            hintText: hint,
            hintStyle: const TextStyle(fontSize: 12, color: AppColors.slate400),
            filled: true,
            fillColor: AppColors.surface,
            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            suffixIcon: suffixIcon,
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(AppRadius.md),
              borderSide: const BorderSide(color: Color(0xFFE2E8F0), width: 1.2),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(AppRadius.md),
              borderSide: const BorderSide(color: Color(0xFFCA8A04), width: 1.5),
            ),
            errorBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(AppRadius.md),
              borderSide: const BorderSide(color: AppColors.danger, width: 1.2),
            ),
            focusedErrorBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(AppRadius.md),
              borderSide: const BorderSide(color: AppColors.danger, width: 1.5),
            ),
          ),
        ),
      ],
    );
  }
}
