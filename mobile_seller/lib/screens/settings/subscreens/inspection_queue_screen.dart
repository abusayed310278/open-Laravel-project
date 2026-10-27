import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/network/api_client.dart';
import '../../../core/network/api_config.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_spacing.dart';
import '../../../core/widgets/app_snackbar.dart';
import '../../../core/widgets/empty_view.dart';
import '../../../core/widgets/loading_view.dart';

class InspectionQueueScreen extends StatefulWidget {
  const InspectionQueueScreen({super.key});

  @override
  State<InspectionQueueScreen> createState() => _InspectionQueueScreenState();
}

class _InspectionQueueScreenState extends State<InspectionQueueScreen> {
  final ApiClient _apiClient = ApiClient();
  final NumberFormat _currencyFormat = NumberFormat('#,##0.00', 'en_US');
  final TextEditingController _searchController = TextEditingController();

  bool _isLoading = true;
  String? _errorMessage;
  List<dynamic> _inspections = [];
  Map<String, dynamic> _summary = {};

  String _selectedTab = 'all'; // 'all', 'scheduled', 'pending', 'verified', 'rejected'
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _fetchInspections();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _fetchInspections({bool isRefresh = false}) async {
    setState(() {
      if (!isRefresh) {
        _isLoading = true;
      }
      _errorMessage = null;
    });

    try {
      final response = await _apiClient.get('/inspections');
      if (mounted && response is Map<String, dynamic>) {
        setState(() {
          _inspections = (response['inspections'] as List?) ?? [];
          _summary = (response['summary'] as Map<String, dynamic>?) ?? {};
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _errorMessage = e.toString();
          _isLoading = false;
        });
      }
    }
  }

  List<dynamic> get _filteredInspections {
    return _inspections.where((item) {
      if (item is! Map<String, dynamic>) return false;
      final status = (item['status']?.toString() ?? 'pending').toLowerCase();

      // Tab filtering
      if (_selectedTab != 'all' && status != _selectedTab) {
        return false;
      }

      // Search filtering
      if (_searchQuery.isNotEmpty) {
        final q = _searchQuery.toLowerCase();
        final title = (item['product_title']?.toString() ?? '').toLowerCase();
        final sku = (item['product_sku']?.toString() ?? '').toLowerCase();
        final location = (item['location_name']?.toString() ?? '').toLowerCase();
        final verifier = (item['verifier_name']?.toString() ?? '').toLowerCase();

        return title.contains(q) || sku.contains(q) || location.contains(q) || verifier.contains(q);
      }

      return true;
    }).toList();
  }

  ({String label, Color color, Color bgColor, IconData icon}) _getStatusInfo(String status) {
    switch (status.toLowerCase()) {
      case 'verified':
        return (
          label: 'VERIFIED',
          color: AppColors.success,
          bgColor: AppColors.successBg,
          icon: Icons.verified_rounded,
        );
      case 'scheduled':
        return (
          label: 'SCHEDULED',
          color: const Color(0xFFD97706),
          bgColor: const Color(0xFFFEF3C7),
          icon: Icons.event_available_rounded,
        );
      case 'rejected':
      case 'failed':
        return (
          label: 'REJECTED',
          color: AppColors.danger,
          bgColor: AppColors.dangerBg,
          icon: Icons.cancel_rounded,
        );
      case 'in_progress':
      case 'inspecting':
        return (
          label: 'INSPECTION IN PROGRESS',
          color: AppColors.info,
          bgColor: AppColors.infoBg,
          icon: Icons.hourglass_top_rounded,
        );
      case 'pending':
      default:
        return (
          label: 'PENDING SCRUTINY',
          color: AppColors.brand700,
          bgColor: AppColors.brand50,
          icon: Icons.pending_actions_rounded,
        );
    }
  }

  Future<void> _openGoogleMaps(String locationName, String? address, String? city) async {
    final query = [locationName, address, city, 'Qatar'].where((s) => s != null && s.isNotEmpty).join(', ');
    final uri = Uri.parse('https://www.google.com/maps/search/?api=1&query=${Uri.encodeComponent(query)}');

    try {
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        if (mounted) {
          AppSnackBar.error(context, 'Could not open maps application');
        }
      }
    } catch (_) {
      if (mounted) {
        AppSnackBar.error(context, 'Unable to launch navigation');
      }
    }
  }

  void _showInspectionDetails(Map<String, dynamic> item) {
    final status = item['status']?.toString() ?? 'pending';
    final statusInfo = _getStatusInfo(status);
    final rawImg = item['product_image']?.toString();
    final imageUrl = (rawImg != null && rawImg.isNotEmpty) ? ApiConfig.sanitizeUrl(rawImg) : null;
    final price = (item['product_price'] as num?)?.toDouble() ?? 0.0;
    final title = item['product_title']?.toString() ?? 'Product';
    final sku = item['product_sku']?.toString() ?? 'N/A';
    final condition = (item['product_condition']?.toString() ?? 'used').toUpperCase();
    final locationName = item['location_name']?.toString() ?? 'Verification Hub';
    final locationAddress = item['location_address']?.toString();
    final locationCity = item['location_city']?.toString() ?? 'Doha';
    final verifier = item['verifier_name']?.toString() ?? 'Openbox Certified Verifier';
    final scheduledAt = item['scheduled_at']?.toString();
    final inspectedAt = item['inspected_at']?.toString();
    final notes = item['notes']?.toString();
    final rejectionReason = item['rejection_reason']?.toString();
    final createdAt = item['created_at']?.toString() ?? 'Recently';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => DraggableScrollableSheet(
        initialChildSize: 0.75,
        minChildSize: 0.5,
        maxChildSize: 0.95,
        builder: (_, scrollController) => Container(
          decoration: const BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.vertical(top: Radius.circular(AppRadius.xl)),
          ),
          child: Column(
            children: [
              // Drag Handle
              Container(
                margin: const EdgeInsets.only(top: 12, bottom: 8),
                width: 44,
                height: 4,
                decoration: BoxDecoration(
                  color: AppColors.slate300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),

              // Sheet Header
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: 8),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Verification Details',
                      style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.ink),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close_rounded, size: 20, color: AppColors.slate500),
                      onPressed: () => Navigator.of(ctx).pop(),
                    ),
                  ],
                ),
              ),
              const Divider(height: 1, color: AppColors.slate200),

              // Sheet Body
              Expanded(
                child: ListView(
                  controller: scrollController,
                  padding: const EdgeInsets.all(AppSpacing.lg),
                  children: [
                    // Product Card
                    Container(
                      padding: const EdgeInsets.all(AppSpacing.md),
                      decoration: BoxDecoration(
                        color: AppColors.slate50,
                        borderRadius: BorderRadius.circular(AppRadius.lg),
                        border: Border.all(color: AppColors.slate200),
                      ),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            width: 64,
                            height: 64,
                            decoration: BoxDecoration(
                              color: AppColors.surface,
                              borderRadius: BorderRadius.circular(AppRadius.md),
                              border: Border.all(color: AppColors.slate200),
                            ),
                            clipBehavior: Clip.antiAlias,
                            child: imageUrl != null
                                ? Image.network(
                                    imageUrl,
                                    fit: BoxFit.cover,
                                    errorBuilder: (_, _, _) => const Icon(Icons.inventory_2_outlined, color: AppColors.slate400),
                                  )
                                : const Icon(Icons.inventory_2_outlined, color: AppColors.slate400),
                          ),
                          const SizedBox(width: AppSpacing.md),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  title,
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.ink),
                                ),
                                const SizedBox(height: 4),
                                Row(
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                      decoration: BoxDecoration(
                                        color: AppColors.brand100,
                                        borderRadius: BorderRadius.circular(AppRadius.sm),
                                      ),
                                      child: Text(
                                        condition,
                                        style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: AppColors.brand800),
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    Text('SKU: $sku', style: const TextStyle(fontSize: 11, color: AppColors.slate500)),
                                  ],
                                ),
                                if (price > 0) ...[
                                  const SizedBox(height: 4),
                                  Text(
                                    '${_currencyFormat.format(price)} QAR',
                                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: Color(0xFF000080)),
                                  ),
                                ],
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: AppSpacing.lg),

                    // Status Highlight Banner
                    Container(
                      padding: const EdgeInsets.all(AppSpacing.md),
                      decoration: BoxDecoration(
                        color: statusInfo.bgColor,
                        borderRadius: BorderRadius.circular(AppRadius.lg),
                        border: Border.all(color: statusInfo.color.withValues(alpha: 0.3)),
                      ),
                      child: Row(
                        children: [
                          Icon(statusInfo.icon, color: statusInfo.color, size: 24),
                          const SizedBox(width: AppSpacing.md),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  statusInfo.label,
                                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: statusInfo.color),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  status == 'verified'
                                      ? 'Product has passed physical scrutiny and certified by Openbox verifiers.'
                                      : (status == 'scheduled'
                                          ? 'Appointment confirmed. Please present the device at the selected hub.'
                                          : (status == 'rejected'
                                              ? 'Device did not meet platform condition standards or scrutiny SLA.'
                                              : 'Awaiting station assignment and physical scrutiny inspection.')),
                                  style: TextStyle(fontSize: 11, color: statusInfo.color.withValues(alpha: 0.9)),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: AppSpacing.xl),

                    // Inspection Timeline / Milestones
                    const Text(
                      'Verification Process Timeline',
                      style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: AppColors.ink),
                    ),
                    const SizedBox(height: AppSpacing.md),
                    _buildTimelineTile(
                      icon: Icons.post_add_rounded,
                      title: 'Appointment Requested',
                      caption: 'Submitted on $createdAt',
                      isCompleted: true,
                    ),
                    _buildTimelineTile(
                      icon: Icons.event_available_rounded,
                      title: 'Slot Scheduled at Center',
                      caption: scheduledAt != null ? 'Scheduled for $scheduledAt' : 'Pending appointment scheduling',
                      isCompleted: scheduledAt != null,
                    ),
                    _buildTimelineTile(
                      icon: Icons.biotech_rounded,
                      title: 'Hardware & Diagnostic Scrutiny',
                      caption: inspectedAt != null ? 'Inspected on $inspectedAt by $verifier' : 'Waiting for device drop-off',
                      isCompleted: inspectedAt != null,
                    ),
                    _buildTimelineTile(
                      icon: status == 'verified'
                          ? Icons.verified_rounded
                          : (status == 'rejected' ? Icons.cancel_rounded : Icons.shield_outlined),
                      title: status == 'verified'
                          ? 'Certified & Badge Granted'
                          : (status == 'rejected' ? 'Scrutiny Declined' : 'Final Certification Decision'),
                      caption: status == 'verified'
                          ? 'Product certified with Openbox warranty seal'
                          : (status == 'rejected' ? 'Inspection failed specifications' : 'Pending inspection completion'),
                      isCompleted: status == 'verified' || status == 'rejected',
                      isError: status == 'rejected',
                      isLast: true,
                    ),
                    const SizedBox(height: AppSpacing.xl),

                    // Inspection Hub Location Card
                    Container(
                      padding: const EdgeInsets.all(AppSpacing.md),
                      decoration: BoxDecoration(
                        color: AppColors.surface,
                        borderRadius: BorderRadius.circular(AppRadius.lg),
                        border: Border.all(color: AppColors.slate200),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(8),
                                decoration: BoxDecoration(
                                  color: AppColors.brand50,
                                  borderRadius: BorderRadius.circular(AppRadius.md),
                                ),
                                child: const Icon(Icons.storefront_rounded, size: 20, color: AppColors.brand600),
                              ),
                              const SizedBox(width: AppSpacing.md),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      locationName,
                                      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.ink),
                                    ),
                                    if (locationAddress != null) ...[
                                      const SizedBox(height: 2),
                                      Text(
                                        '$locationAddress, $locationCity',
                                        style: const TextStyle(fontSize: 11, color: AppColors.slate500),
                                      ),
                                    ],
                                  ],
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: AppSpacing.md),
                          OutlinedButton.icon(
                            style: OutlinedButton.styleFrom(
                              minimumSize: const Size.fromHeight(40),
                              side: const BorderSide(color: AppColors.brand500),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
                            ),
                            icon: const Icon(Icons.map_outlined, size: 16, color: AppColors.brand600),
                            label: const Text('Open Station on Google Maps', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.brand600)),
                            onPressed: () => _openGoogleMaps(locationName, locationAddress, locationCity),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: AppSpacing.lg),

                    // Notes / Rejection Reason Box
                    if (rejectionReason != null && rejectionReason.isNotEmpty) ...[
                      Container(
                        padding: const EdgeInsets.all(AppSpacing.md),
                        decoration: BoxDecoration(
                          color: AppColors.dangerBg,
                          borderRadius: BorderRadius.circular(AppRadius.md),
                          border: Border.all(color: AppColors.danger.withValues(alpha: 0.3)),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Row(
                              children: [
                                Icon(Icons.warning_amber_rounded, size: 18, color: AppColors.danger),
                                SizedBox(width: 6),
                                Text(
                                  'Reason for Rejection',
                                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: AppColors.danger),
                                ),
                              ],
                            ),
                            const SizedBox(height: 6),
                            Text(
                              rejectionReason,
                              style: const TextStyle(fontSize: 12, color: AppColors.ink),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: AppSpacing.lg),
                    ] else if (notes != null && notes.isNotEmpty) ...[
                      Container(
                        padding: const EdgeInsets.all(AppSpacing.md),
                        decoration: BoxDecoration(
                          color: AppColors.slate50,
                          borderRadius: BorderRadius.circular(AppRadius.md),
                          border: Border.all(color: AppColors.slate200),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Row(
                              children: [
                                Icon(Icons.notes_rounded, size: 18, color: AppColors.brand600),
                                SizedBox(width: 6),
                                Text(
                                  'Verifier Notes & Inspection Checklist',
                                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: AppColors.ink),
                                ),
                              ],
                            ),
                            const SizedBox(height: 6),
                            Text(
                              notes,
                              style: const TextStyle(fontSize: 12, color: AppColors.slate700),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: AppSpacing.lg),
                    ],
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildTimelineTile({
    required IconData icon,
    required String title,
    required String caption,
    required bool isCompleted,
    bool isError = false,
    bool isLast = false,
  }) {
    final activeColor = isError ? AppColors.danger : AppColors.success;
    final dotColor = isCompleted ? activeColor : AppColors.slate300;

    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Column(
          children: [
            Container(
              width: 28,
              height: 28,
              decoration: BoxDecoration(
                color: isCompleted ? dotColor.withValues(alpha: 0.15) : AppColors.slate100,
                shape: BoxShape.circle,
                border: Border.all(color: dotColor, width: 2),
              ),
              child: Icon(icon, size: 14, color: isCompleted ? dotColor : AppColors.slate400),
            ),
            if (!isLast)
              Container(
                width: 2,
                height: 36,
                color: isCompleted ? activeColor.withValues(alpha: 0.4) : AppColors.slate200,
              ),
          ],
        ),
        const SizedBox(width: AppSpacing.md),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  color: isCompleted ? AppColors.ink : AppColors.slate500,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                caption,
                style: const TextStyle(fontSize: 11, color: AppColors.slate500),
              ),
              const SizedBox(height: 12),
            ],
          ),
        ),
      ],
    );
  }

  // Booking Modal
  Future<void> _openBookInspectionModal() async {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _BookInspectionBottomSheet(
        apiClient: _apiClient,
        onSuccess: () {
          Navigator.of(ctx).pop();
          AppSnackBar.success(context, 'Inspection appointment booked successfully!');
          _fetchInspections();
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final filtered = _filteredInspections;

    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, size: 20, color: AppColors.ink),
          onPressed: () => Navigator.of(context).pop(),
        ),
        title: const Text(
          'Product Inspection Queue',
          style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.ink),
        ),
        actions: [
          IconButton(
            tooltip: 'Book New Inspection',
            icon: const Icon(Icons.add_circle_outline_rounded, color: AppColors.brand600),
            onPressed: _openBookInspectionModal,
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: AppColors.brand500,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add_rounded, size: 20),
        label: const Text('Book Inspection', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
        onPressed: _openBookInspectionModal,
      ),
      body: _isLoading
          ? const LoadingView(message: 'Loading inspection queue & station appointments...')
          : _errorMessage != null && _inspections.isEmpty
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(AppSpacing.xl),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.error_outline_rounded, size: 48, color: AppColors.danger),
                        const SizedBox(height: AppSpacing.md),
                        Text(_errorMessage!, textAlign: TextAlign.center, style: const TextStyle(color: AppColors.slate600)),
                        const SizedBox(height: AppSpacing.md),
                        ElevatedButton.icon(
                          onPressed: _fetchInspections,
                          icon: const Icon(Icons.refresh_rounded, size: 16),
                          label: const Text('Retry'),
                        ),
                      ],
                    ),
                  ),
                )
              : RefreshIndicator(
                  onRefresh: () => _fetchInspections(isRefresh: true),
                  color: AppColors.brand500,
                  child: ListView(
                    padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.lg, AppSpacing.lg, 80),
                    children: [
                      // 1. Metric Tiles (Total, Scheduled, Pending, Verified, Rejected)
                      _buildMetricTiles(),
                      const SizedBox(height: AppSpacing.lg),

                      // 2. Search & Filter Bar
                      _buildSearchAndFilterBar(),
                      const SizedBox(height: AppSpacing.md),

                      // 3. Tab Pills
                      _buildTabPills(),
                      const SizedBox(height: AppSpacing.lg),

                      // 4. Queue Content
                      if (filtered.isEmpty)
                        EmptyView(
                          icon: Icons.fact_check_outlined,
                          title: _searchQuery.isNotEmpty || _selectedTab != 'all'
                              ? 'No matching inspections'
                              : 'Inspection Queue Clean',
                          subtitle: _searchQuery.isNotEmpty || _selectedTab != 'all'
                              ? 'No appointments match your current search or tab filters.'
                              : 'You have no devices awaiting station inspection or physical scrutiny. Tap below to book an appointment.',
                          actionLabel: 'Book Device Inspection',
                          onAction: _openBookInspectionModal,
                        )
                      else
                        ListView.separated(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          itemCount: filtered.length,
                          separatorBuilder: (_, _) => const SizedBox(height: AppSpacing.md),
                          itemBuilder: (context, index) {
                            final item = filtered[index] as Map<String, dynamic>;
                            return _buildInspectionCard(item);
                          },
                        ),
                    ],
                  ),
                ),
    );
  }

  Widget _buildMetricTiles() {
    final total = _summary['total'] ?? _inspections.length;
    final scheduled = _summary['scheduled'] ?? 0;
    final pending = _summary['pending'] ?? 0;
    final verified = _summary['verified'] ?? 0;
    final rejected = _summary['rejected'] ?? 0;

    return Row(
      children: [
        _buildMetricCard('All', '$total', AppColors.ink, 'all'),
        const SizedBox(width: 8),
        _buildMetricCard('Scheduled', '$scheduled', const Color(0xFFD97706), 'scheduled'),
        const SizedBox(width: 8),
        _buildMetricCard('Pending', '$pending', AppColors.brand600, 'pending'),
        const SizedBox(width: 8),
        _buildMetricCard('Verified', '$verified', AppColors.success, 'verified'),
        if ((rejected as num) > 0) ...[
          const SizedBox(width: 8),
          _buildMetricCard('Rejected', '$rejected', AppColors.danger, 'rejected'),
        ],
      ],
    );
  }

  Widget _buildMetricCard(String label, String value, Color color, String tabKey) {
    final isSelected = _selectedTab == tabKey;

    return Expanded(
      child: GestureDetector(
        onTap: () => setState(() => _selectedTab = tabKey),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 4),
          decoration: BoxDecoration(
            color: isSelected ? color.withValues(alpha: 0.12) : AppColors.surface,
            borderRadius: BorderRadius.circular(AppRadius.md),
            border: Border.all(
              color: isSelected ? color : AppColors.slate200.withValues(alpha: 0.8),
              width: isSelected ? 1.5 : 1,
            ),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.02),
                blurRadius: 6,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Column(
            children: [
              Text(
                value,
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w800,
                  color: color,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                label,
                style: TextStyle(
                  fontSize: 10,
                  fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
                  color: isSelected ? color : AppColors.slate500,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSearchAndFilterBar() {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.md),
        border: Border.all(color: AppColors.slate200),
      ),
      child: TextField(
        controller: _searchController,
        onChanged: (val) => setState(() => _searchQuery = val.trim()),
        decoration: InputDecoration(
          hintText: 'Search by product title, SKU, center or verifier...',
          hintStyle: const TextStyle(fontSize: 12, color: AppColors.slate400),
          prefixIcon: const Icon(Icons.search_rounded, size: 20, color: AppColors.slate400),
          suffixIcon: _searchQuery.isNotEmpty
              ? IconButton(
                  icon: const Icon(Icons.clear_rounded, size: 18, color: AppColors.slate400),
                  onPressed: () {
                    _searchController.clear();
                    setState(() => _searchQuery = '');
                  },
                )
              : null,
          border: InputBorder.none,
          contentPadding: const EdgeInsets.symmetric(vertical: 12),
        ),
      ),
    );
  }

  Widget _buildTabPills() {
    final tabs = [
      {'key': 'all', 'label': 'All Items'},
      {'key': 'scheduled', 'label': 'Scheduled'},
      {'key': 'pending', 'label': 'Pending'},
      {'key': 'verified', 'label': 'Verified'},
      {'key': 'rejected', 'label': 'Rejected'},
    ];

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Row(
        children: tabs.map((t) {
          final isSelected = _selectedTab == t['key'];
          return Padding(
            padding: const EdgeInsets.only(right: 8),
            child: ChoiceChip(
              selected: isSelected,
              label: Text(t['label']!),
              labelStyle: TextStyle(
                fontSize: 12,
                fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
                color: isSelected ? AppColors.brand800 : AppColors.slate600,
              ),
              selectedColor: AppColors.brand100,
              backgroundColor: AppColors.surface,
              side: BorderSide(color: isSelected ? AppColors.brand500 : AppColors.slate200),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.pill)),
              onSelected: (_) => setState(() => _selectedTab = t['key']!),
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildInspectionCard(Map<String, dynamic> item) {
    final status = item['status']?.toString() ?? 'pending';
    final statusInfo = _getStatusInfo(status);
    final rawImg = item['product_image']?.toString();
    final imageUrl = (rawImg != null && rawImg.isNotEmpty) ? ApiConfig.sanitizeUrl(rawImg) : null;
    final price = (item['product_price'] as num?)?.toDouble() ?? 0.0;
    final title = item['product_title']?.toString() ?? 'Product';
    final sku = item['product_sku']?.toString() ?? 'N/A';
    final condition = (item['product_condition']?.toString() ?? 'used').toUpperCase();
    final locationName = item['location_name']?.toString() ?? 'Main Hub Verification Station';
    final scheduledAt = item['scheduled_at']?.toString();
    final inspectedAt = item['inspected_at']?.toString();
    final verifier = item['verifier_name']?.toString() ?? 'Assigned Verifier';
    final rejectionReason = item['rejection_reason']?.toString();

    return InkWell(
      onTap: () => _showInspectionDetails(item),
      borderRadius: BorderRadius.circular(AppRadius.lg),
      child: Container(
        padding: const EdgeInsets.all(AppSpacing.md),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(AppRadius.lg),
          border: Border.all(color: AppColors.slate200.withValues(alpha: 0.8)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.02),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Top: Thumbnail + Title + Status Badge
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 58,
                  height: 58,
                  decoration: BoxDecoration(
                    color: AppColors.slate100,
                    borderRadius: BorderRadius.circular(AppRadius.md),
                  ),
                  clipBehavior: Clip.antiAlias,
                  child: imageUrl != null
                      ? Image.network(
                          imageUrl,
                          fit: BoxFit.cover,
                          errorBuilder: (_, _, _) => const Icon(Icons.inventory_2_outlined, color: AppColors.slate400),
                        )
                      : const Icon(Icons.inventory_2_outlined, color: AppColors.slate400),
                ),
                const SizedBox(width: AppSpacing.md),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Expanded(
                            child: Text(
                              title,
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.ink),
                            ),
                          ),
                          const SizedBox(width: 6),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(
                              color: statusInfo.bgColor,
                              borderRadius: BorderRadius.circular(AppRadius.pill),
                              border: Border.all(color: statusInfo.color.withValues(alpha: 0.3)),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(statusInfo.icon, size: 12, color: statusInfo.color),
                                const SizedBox(width: 4),
                                Text(
                                  statusInfo.label,
                                  style: TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: statusInfo.color),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                            decoration: BoxDecoration(
                              color: AppColors.slate100,
                              borderRadius: BorderRadius.circular(3),
                            ),
                            child: Text(
                              condition,
                              style: const TextStyle(fontSize: 9, fontWeight: FontWeight.w700, color: AppColors.slate700),
                            ),
                          ),
                          const SizedBox(width: 6),
                          Text('SKU: $sku', style: const TextStyle(fontSize: 11, color: AppColors.slate500)),
                          const Spacer(),
                          if (price > 0)
                            Text(
                              '${_currencyFormat.format(price)} QAR',
                              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Color(0xFF000080)),
                            ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const Divider(height: 18, color: AppColors.slate100),

            // Middle: Station Location & Verifier Info
            Row(
              children: [
                const Icon(Icons.location_on_outlined, size: 15, color: AppColors.slate400),
                const SizedBox(width: 4),
                Expanded(
                  child: Text(
                    locationName,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontSize: 12, color: AppColors.slate600),
                  ),
                ),
                const SizedBox(width: 8),
                const Icon(Icons.badge_outlined, size: 15, color: AppColors.slate400),
                const SizedBox(width: 4),
                Text(
                  verifier,
                  style: const TextStyle(fontSize: 11, color: AppColors.slate500),
                ),
              ],
            ),

            // Bottom: Appointment Date or Rejection Warning
            if (scheduledAt != null) ...[
              const SizedBox(height: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
                decoration: BoxDecoration(
                  color: const Color(0xFFFEF3C7),
                  borderRadius: BorderRadius.circular(AppRadius.sm),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.calendar_today_rounded, size: 13, color: Color(0xFFD97706)),
                    const SizedBox(width: 6),
                    Text(
                      'Appointment: $scheduledAt',
                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Color(0xFFB45309)),
                    ),
                  ],
                ),
              ),
            ] else if (inspectedAt != null) ...[
              const SizedBox(height: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
                decoration: BoxDecoration(
                  color: AppColors.successBg,
                  borderRadius: BorderRadius.circular(AppRadius.sm),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.check_circle_outline_rounded, size: 13, color: AppColors.success),
                    const SizedBox(width: 6),
                    Text(
                      'Inspected on $inspectedAt',
                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.success),
                    ),
                  ],
                ),
              ),
            ],

            if (rejectionReason != null && rejectionReason.isNotEmpty) ...[
              const SizedBox(height: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
                decoration: BoxDecoration(
                  color: AppColors.dangerBg,
                  borderRadius: BorderRadius.circular(AppRadius.sm),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.error_outline_rounded, size: 13, color: AppColors.danger),
                    const SizedBox(width: 6),
                    Expanded(
                      child: Text(
                        'Reason: $rejectionReason',
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.danger),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

// Interactive Booking Modal Sheet
class _BookInspectionBottomSheet extends StatefulWidget {
  final ApiClient apiClient;
  final VoidCallback onSuccess;

  const _BookInspectionBottomSheet({
    required this.apiClient,
    required this.onSuccess,
  });

  @override
  State<_BookInspectionBottomSheet> createState() => _BookInspectionBottomSheetState();
}

class _BookInspectionBottomSheetState extends State<_BookInspectionBottomSheet> {
  bool _isLoadingMeta = true;
  bool _isSubmitting = false;

  List<dynamic> _locations = [];
  Map<String, dynamic> _slots = {};
  List<dynamic> _products = [];

  int? _selectedProductId;
  int? _selectedLocationId;
  DateTime _selectedDate = DateTime.now().add(const Duration(days: 1));
  String? _selectedTimeSlot;

  @override
  void initState() {
    super.initState();
    _fetchMeta();
  }

  Future<void> _fetchMeta() async {
    try {
      final res = await widget.apiClient.get('/inspections/meta');
      if (mounted && res is Map<String, dynamic>) {
        setState(() {
          _locations = (res['locations'] as List?) ?? [];
          _slots = (res['slots'] as Map<String, dynamic>?) ?? {};
          _products = (res['products'] as List?) ?? [];

          if (_locations.isNotEmpty) {
            _selectedLocationId = (_locations.first['id'] as num?)?.toInt();
          }
          if (_slots.isNotEmpty) {
            _selectedTimeSlot = _slots.keys.first;
          }
          if (_products.isNotEmpty) {
            _selectedProductId = (_products.first['id'] as num?)?.toInt();
          }
          _isLoadingMeta = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _isLoadingMeta = false);
    }
  }

  Future<void> _submitBooking() async {
    if (_selectedProductId == null) {
      AppSnackBar.warning(context, 'Please select an eligible product to inspect.');
      return;
    }
    if (_selectedLocationId == null) {
      AppSnackBar.warning(context, 'Please select an official inspection center.');
      return;
    }
    if (_selectedTimeSlot == null) {
      AppSnackBar.warning(context, 'Please pick a 30-minute inspection time slot.');
      return;
    }

    setState(() => _isSubmitting = true);

    try {
      final dateStr = DateFormat('yyyy-MM-dd').format(_selectedDate);
      await widget.apiClient.post(
        '/inspections',
        body: {
          'product_id': _selectedProductId,
          'location_id': _selectedLocationId,
          'appointment_date': dateStr,
          'appointment_time': _selectedTimeSlot,
        },
      );
      widget.onSuccess();
    } catch (e) {
      if (mounted) {
        setState(() => _isSubmitting = false);
        AppSnackBar.error(context, e.toString());
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
      decoration: const BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.vertical(top: Radius.circular(AppRadius.xl)),
      ),
      child: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppSpacing.lg),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 44,
                  height: 4,
                  decoration: BoxDecoration(
                    color: AppColors.slate300,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: AppSpacing.md),
              const Text(
                'Book Physical Scrutiny Appointment',
                style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.ink),
              ),
              const SizedBox(height: 2),
              const Text(
                'Schedule a station drop-off for device hardware & grading verification',
                style: TextStyle(fontSize: 12, color: AppColors.slate500),
              ),
              const SizedBox(height: AppSpacing.lg),

              if (_isLoadingMeta)
                const Center(
                  child: Padding(
                    padding: EdgeInsets.all(AppSpacing.xl),
                    child: CircularProgressIndicator(color: AppColors.brand500),
                  ),
                )
              else ...[
                // 1. Choose Product
                const Text('1. Select Product for Inspection', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.ink)),
                const SizedBox(height: 6),
                if (_products.isEmpty)
                  Container(
                    padding: const EdgeInsets.all(AppSpacing.md),
                    decoration: BoxDecoration(
                      color: AppColors.slate50,
                      borderRadius: BorderRadius.circular(AppRadius.md),
                      border: Border.all(color: AppColors.slate200),
                    ),
                    child: const Text('No products currently awaiting verification in your store.', style: TextStyle(fontSize: 12, color: AppColors.slate500)),
                  )
                else
                  DropdownButtonFormField<int>(
                    initialValue: _selectedProductId,
                    isExpanded: true,
                    decoration: InputDecoration(
                      filled: true,
                      fillColor: AppColors.slate50,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(AppRadius.md),
                        borderSide: const BorderSide(color: AppColors.slate200),
                      ),
                    ),
                    items: _products.map((p) {
                      final title = p['title']?.toString() ?? 'Product';
                      final sku = p['sku']?.toString() ?? '';
                      return DropdownMenuItem<int>(
                        value: (p['id'] as num).toInt(),
                        child: Text(
                          '$title ($sku)',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(fontSize: 13),
                        ),
                      );
                    }).toList(),
                    onChanged: (val) => setState(() => _selectedProductId = val),
                  ),
                const SizedBox(height: AppSpacing.lg),

                // 2. Select Inspection Hub
                const Text('2. Choose Verification Station', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.ink)),
                const SizedBox(height: 6),
                Column(
                  children: _locations.map((loc) {
                    final id = (loc['id'] as num).toInt();
                    final isSelected = _selectedLocationId == id;
                    final name = loc['name']?.toString() ?? 'Inspection Hub';
                    final address = loc['address']?.toString() ?? '';
                    final city = loc['city']?.toString() ?? 'Doha';

                    return InkWell(
                      onTap: () => setState(() => _selectedLocationId = id),
                      borderRadius: BorderRadius.circular(AppRadius.md),
                      child: Container(
                        margin: const EdgeInsets.only(bottom: 8),
                        padding: const EdgeInsets.all(AppSpacing.md),
                        decoration: BoxDecoration(
                          color: isSelected ? AppColors.brand50 : AppColors.surface,
                          borderRadius: BorderRadius.circular(AppRadius.md),
                          border: Border.all(
                            color: isSelected ? AppColors.brand500 : AppColors.slate200,
                            width: isSelected ? 1.5 : 1,
                          ),
                        ),
                        child: Row(
                          children: [
                            Icon(
                              isSelected ? Icons.radio_button_checked : Icons.radio_button_off,
                              size: 18,
                              color: isSelected ? AppColors.brand600 : AppColors.slate400,
                            ),
                            const SizedBox(width: AppSpacing.md),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.ink)),
                                  Text('$address, $city', style: const TextStyle(fontSize: 11, color: AppColors.slate500)),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    );
                  }).toList(),
                ),
                const SizedBox(height: AppSpacing.md),

                // 3. Appointment Date
                const Text('3. Select Date', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.ink)),
                const SizedBox(height: 6),
                OutlinedButton.icon(
                  style: OutlinedButton.styleFrom(
                    minimumSize: const Size.fromHeight(46),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
                    side: const BorderSide(color: AppColors.slate300),
                  ),
                  icon: const Icon(Icons.calendar_month_rounded, size: 18, color: AppColors.brand600),
                  label: Text(
                    DateFormat('EEEE, MMMM d, yyyy').format(_selectedDate),
                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.ink),
                  ),
                  onPressed: () async {
                    final picked = await showDatePicker(
                      context: context,
                      initialDate: _selectedDate,
                      firstDate: DateTime.now(),
                      lastDate: DateTime.now().add(const Duration(days: 60)),
                    );
                    if (picked != null) {
                      setState(() => _selectedDate = picked);
                    }
                  },
                ),
                const SizedBox(height: AppSpacing.lg),

                // 4. 30-Min Time Slots
                const Text('4. Available 30-Min Scrutiny Slots', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.ink)),
                const SizedBox(height: 8),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: _slots.entries.map((entry) {
                    final slotKey = entry.key;
                    final slotLabel = entry.value.toString();
                    final isSelected = _selectedTimeSlot == slotKey;

                    return ChoiceChip(
                      selected: isSelected,
                      label: Text(slotLabel),
                      labelStyle: TextStyle(
                        fontSize: 11,
                        fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
                        color: isSelected ? Colors.white : AppColors.ink,
                      ),
                      selectedColor: AppColors.brand600,
                      backgroundColor: AppColors.slate100,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
                      onSelected: (_) => setState(() => _selectedTimeSlot = slotKey),
                    );
                  }).toList(),
                ),
                const SizedBox(height: AppSpacing.xl),

                // Submit Button
                ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    minimumSize: const Size.fromHeight(48),
                    backgroundColor: AppColors.brand500,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
                  ),
                  onPressed: _isSubmitting ? null : _submitBooking,
                  child: _isSubmitting
                      ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                      : const Text('Confirm & Book Appointment', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700)),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
