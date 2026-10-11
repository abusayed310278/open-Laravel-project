import 'package:flutter/material.dart';

import '../../../core/network/api_client.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_spacing.dart';
import '../../../core/widgets/custom_button.dart';
import '../../../core/widgets/custom_text_field.dart';
import '../../../core/widgets/loading_view.dart';

class VerificationStatusScreen extends StatefulWidget {
  const VerificationStatusScreen({super.key});

  @override
  State<VerificationStatusScreen> createState() => _VerificationStatusScreenState();
}

class _VerificationStatusScreenState extends State<VerificationStatusScreen> {
  final ApiClient _apiClient = ApiClient();
  bool _isLoading = true;
  Map<String, dynamic>? _data;

  final _docNumberController = TextEditingController();
  String _selectedDocType = 'National ID';

  @override
  void initState() {
    super.initState();
    _fetchStatus();
  }

  @override
  void dispose() {
    _docNumberController.dispose();
    super.dispose();
  }

  Future<void> _fetchStatus() async {
    setState(() => _isLoading = true);

    try {
      final response = await _apiClient.get('/verification');
      if (mounted) {
        setState(() {
          _data = response is Map<String, dynamic> ? response : null;
          _isLoading = false;
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _submitVerification() async {
    if (_docNumberController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please enter your document ID number.'), backgroundColor: AppColors.danger),
      );
      return;
    }

    try {
      await _apiClient.post(
        '/verification',
        body: {
          'document_type': _selectedDocType,
          'document_number': _docNumberController.text.trim(),
        },
      );
      _docNumberController.clear();
      _fetchStatus();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Verification details submitted!'), backgroundColor: AppColors.success),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Submission failed: $e'), backgroundColor: AppColors.danger),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isApproved = _data?['is_kyc_approved'] == true;
    final status = _data?['kyc_status']?.toString() ?? 'pending';
    final submittedAt = _data?['submitted_at']?.toString();
    final requirements = (_data?['requirements'] as List?) ?? [];
    final documents = (_data?['documents'] as List?) ?? [];

    Color badgeColor = isApproved ? AppColors.success : (status == 'rejected' ? AppColors.danger : AppColors.brand600);
    String statusLabel = isApproved ? 'Verified Merchant' : (status == 'rejected' ? 'Verification Rejected' : 'Under Compliance Review');

    return Scaffold(
      backgroundColor: AppColors.surface,
      appBar: AppBar(title: const Text('Verification Status & KYC')),
      body: _isLoading
          ? const LoadingView(message: 'Checking verification status...')
          : RefreshIndicator(
              onRefresh: _fetchStatus,
              color: AppColors.brand500,
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(AppSpacing.lg),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Status Hero Banner
                    Container(
                      padding: const EdgeInsets.all(AppSpacing.xl),
                      decoration: BoxDecoration(
                        color: AppColors.surface,
                        borderRadius: BorderRadius.circular(AppRadius.md),
                        border: Border.all(color: AppColors.slate200),
                      ),
                      child: Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(AppSpacing.md),
                            decoration: BoxDecoration(
                              color: badgeColor.withOpacity(0.12),
                              shape: BoxShape.circle,
                            ),
                            child: Icon(isApproved ? Icons.verified_rounded : Icons.shield_outlined, size: 36, color: badgeColor),
                          ),
                          const SizedBox(width: AppSpacing.lg),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(statusLabel, style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: badgeColor)),
                                const SizedBox(height: 4),
                                Text(
                                  isApproved
                                      ? 'Your store is fully verified to publish listings and receive payouts.'
                                      : 'Submitted on ${submittedAt ?? 'Recently'}. Approval takes 24-48 hours.',
                                  style: const TextStyle(fontSize: 12, color: AppColors.slate600),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: AppSpacing.xl),

                    // Verification Checklist
                    const Text('Mandatory Requirements', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.ink)),
                    const SizedBox(height: AppSpacing.sm),
                    ...requirements.map((req) => Padding(
                          padding: const EdgeInsets.only(bottom: 8),
                          child: Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Icon(isApproved ? Icons.check_circle_rounded : Icons.radio_button_unchecked, size: 18, color: isApproved ? AppColors.success : AppColors.slate400),
                              const SizedBox(width: 8),
                              Expanded(child: Text(req.toString(), style: const TextStyle(fontSize: 13, color: AppColors.slate700))),
                            ],
                          ),
                        )),
                    const SizedBox(height: AppSpacing.xl),

                    // Documents List
                    if (documents.isNotEmpty) ...[
                      const Text('Submitted Documents', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.ink)),
                      const SizedBox(height: AppSpacing.sm),
                      ...documents.map((doc) => Container(
                            margin: const EdgeInsets.only(bottom: 8),
                            padding: const EdgeInsets.all(AppSpacing.md),
                            decoration: BoxDecoration(
                              color: AppColors.surface,
                              borderRadius: BorderRadius.circular(AppRadius.md),
                              border: Border.all(color: AppColors.slate200),
                            ),
                            child: Row(
                              children: [
                                const Icon(Icons.description_outlined, size: 24, color: AppColors.slate500),
                                const SizedBox(width: AppSpacing.md),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(doc['type'] ?? 'ID Document', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.ink)),
                                      Text('Doc #: ${doc['document_number']}', style: const TextStyle(fontSize: 12, color: AppColors.slate500)),
                                    ],
                                  ),
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                  decoration: BoxDecoration(
                                    color: (doc['status'] == 'approved' ? AppColors.success : AppColors.brand50),
                                    borderRadius: BorderRadius.circular(AppRadius.sm),
                                  ),
                                  child: Text(
                                    (doc['status'] ?? 'Pending').toString().toUpperCase(),
                                    style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: doc['status'] == 'approved' ? AppColors.success : AppColors.brand700),
                                  ),
                                ),
                              ],
                            ),
                          )),
                      const SizedBox(height: AppSpacing.xl),
                    ],

                    // Submission Form (if not yet approved)
                    if (!isApproved) ...[
                      const Text('Submit Document ID for Verification', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.ink)),
                      const SizedBox(height: AppSpacing.md),
                      DropdownButtonFormField<String>(
                        value: _selectedDocType,
                        decoration: const InputDecoration(labelText: 'Document Type'),
                        items: const [
                          DropdownMenuItem(value: 'National ID', child: Text('National ID / QID')),
                          DropdownMenuItem(value: 'Passport', child: Text('Passport')),
                          DropdownMenuItem(value: 'Commercial Registration', child: Text('Commercial Registration (CR)')),
                          DropdownMenuItem(value: 'Trade License', child: Text('Trade License')),
                        ],
                        onChanged: (val) => setState(() => _selectedDocType = val ?? 'National ID'),
                      ),
                      const SizedBox(height: AppSpacing.md),
                      CustomTextField(
                        controller: _docNumberController,
                        label: 'Document Identification Number',
                        hint: 'e.g. 29400012345',
                      ),
                      const SizedBox(height: AppSpacing.xl),
                      CustomButton(
                        text: 'Submit Verification Info',
                        onPressed: _submitVerification,
                      ),
                    ],
                  ],
                ),
              ),
            ),
    );
  }
}
