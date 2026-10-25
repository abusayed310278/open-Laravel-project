import 'package:fl_chart/fl_chart.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../../core/network/api_client.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../core/widgets/loading_view.dart';
import '../../core/widgets/status_badge.dart';
import '../../providers/seller_auth_provider.dart';
import '../../providers/seller_dashboard_provider.dart';
import '../../data/models/dashboard_data.dart';
import '../../data/models/seller_user.dart';
import '../orders/order_detail_screen.dart';
import '../products/add_product_screen.dart';
import '../notifications/notifications_screen.dart';
import '../settings/subscreens/update_profile_screen.dart';
import '../settings/subscreens/verification_status_screen.dart';
import '../settings/subscreens/seller_center_screen.dart';
import '../catalog/catalog_screen.dart';
import '../wallet/wallet_screen.dart';

class DashboardScreen extends StatefulWidget {
  final void Function(int tabIndex)? onNavigateTab;

  const DashboardScreen({super.key, this.onNavigateTab});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _OnboardingStep {
  final String title;
  final bool isDone;
  final IconData icon;
  final String description;
  final String actionLabel;
  final VoidCallback onAction;

  const _OnboardingStep({
    required this.title,
    required this.isDone,
    required this.icon,
    required this.description,
    required this.actionLabel,
    required this.onAction,
  });
}

class _DashboardScreenState extends State<DashboardScreen> {
  final ApiClient _apiClient = ApiClient();
  final _currencyFormat = NumberFormat('#,##0.00', 'en_US');
  int? _expandedStep;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<SellerDashboardProvider>().fetchDashboard();
      context.read<SellerAuthProvider>().refreshProfile();
    });
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<SellerAuthProvider>();
    final dashboardProvider = context.watch<SellerDashboardProvider>();
    final user = authProvider.user;
    final metrics = dashboardProvider.metrics;

    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        title: const Text(
          'Seller Center',
          style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: AppColors.brand600),
        ),
        actions: [
          if (user != null)
            Container(
              margin: const EdgeInsets.only(right: AppSpacing.sm),
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(
                color: AppColors.brand50,
                borderRadius: BorderRadius.circular(AppRadius.pill),
                border: Border.all(color: AppColors.brand200),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.bolt_rounded, size: 14, color: AppColors.brand600),
                  const SizedBox(width: 4),
                  Text(
                    '${user.listingCredits} Credits',
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: AppColors.brand700,
                    ),
                  ),
                ],
              ),
            ),
          IconButton(
            tooltip: 'Seller Centers Map',
            icon: const Icon(Icons.map_outlined, color: AppColors.ink),
            onPressed: () => _open(const SellerCenterScreen()),
          ),
          IconButton(
            tooltip: 'Notifications',
            icon: Stack(
              clipBehavior: Clip.none,
              children: [
                const Icon(Icons.notifications_none_rounded, color: AppColors.ink),
                Positioned(
                  top: 0,
                  right: 0,
                  child: Container(
                    width: 8,
                    height: 8,
                    decoration: const BoxDecoration(
                      color: AppColors.brand500,
                      shape: BoxShape.circle,
                    ),
                  ),
                ),
              ],
            ),
            onPressed: () => _open(const NotificationsScreen()),
          ),
          const SizedBox(width: AppSpacing.xs),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          final dashboardProv = context.read<SellerDashboardProvider>();
          final authProv = context.read<SellerAuthProvider>();
          await dashboardProv.fetchDashboard();
          await authProv.refreshProfile();
        },
        color: AppColors.brand500,
        child: dashboardProvider.isLoading && metrics == null
            ? const LoadingView(message: 'Loading dashboard analytics...')
            : SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(AppSpacing.lg),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // 0. Profile, order status & onboarding checklist
                    _buildProfileHeader(user),
                    const SizedBox(height: AppSpacing.lg),
                    _buildOrderStatusCard(dashboardProvider.orderCounts),
                    ..._buildOnboardingSection(user, metrics),
                    const SizedBox(height: AppSpacing.lg),

                    // 1. 4-Stat Cards Grid (2x2)
                    _buildStatsGrid(metrics),
                    const SizedBox(height: AppSpacing.xl),

                    // 2. Sales Trend Line Chart
                    _buildSalesChartCard(dashboardProvider),
                    const SizedBox(height: AppSpacing.xl),

                    // 3. Quick Action Row
                    _buildQuickActionsRow(context),
                    const SizedBox(height: AppSpacing.xl),

                    // 4. Top Performing Products
                    if (dashboardProvider.topProducts.isNotEmpty) ...[
                      _buildTopProductsSection(dashboardProvider),
                      const SizedBox(height: AppSpacing.xl),
                    ],

                    // 5. Recent Orders
                    _buildRecentOrdersSection(context, dashboardProvider),
                    const SizedBox(height: AppSpacing.xxl),
                  ],
                ),
              ),
      ),
    );
  }

  void _open(Widget screen) {
    Navigator.of(context).push(MaterialPageRoute(builder: (_) => screen));
  }

  Widget _buildProfileHeader(SellerUser? user) {
    final imageUrl = user?.avatarUrl ?? user?.logoUrl;
    final userName = (user?.name ?? '').trim();
    final storeName = (user?.storeName ?? '').trim();
    final hasBoth = userName.isNotEmpty && storeName.isNotEmpty && userName.toLowerCase() != storeName.toLowerCase();

    return Row(
      children: [
        GestureDetector(
          onTap: () => _open(const UpdateProfileScreen()),
          child: Stack(
            clipBehavior: Clip.none,
            children: [
              Container(
                width: 60,
                height: 60,
                decoration: const BoxDecoration(color: AppColors.brand100, shape: BoxShape.circle),
                clipBehavior: Clip.antiAlias,
                child: imageUrl != null && imageUrl.isNotEmpty
                    ? Image.network(
                        imageUrl,
                        fit: BoxFit.cover,
                        errorBuilder: (_, _, _) => _buildAvatarFallback(user),
                      )
                    : _buildAvatarFallback(user),
              ),
              Positioned(
                right: -2,
                bottom: -2,
                child: Container(
                  width: 20,
                  height: 20,
                  decoration: BoxDecoration(
                    color: AppColors.brand500,
                    shape: BoxShape.circle,
                    border: Border.all(color: AppColors.surface, width: 1.5),
                  ),
                  child: const Icon(Icons.edit, size: 11, color: Colors.white),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(width: AppSpacing.md),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                hasBoth
                    ? userName
                    : (storeName.isNotEmpty ? storeName : (userName.isNotEmpty ? userName : 'Seller Portal')),
                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.ink),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 2),
              Row(
                children: [
                  if (hasBoth) ...[
                    Flexible(
                      child: Text(
                        storeName,
                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.brand600),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    const Text(' • ', style: TextStyle(fontSize: 12, color: AppColors.slate400)),
                  ],
                  Text(
                    user?.roleLabel ?? 'Seller',
                    style: const TextStyle(fontSize: 12, color: AppColors.slate500),
                  ),
                ],
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildAvatarFallback(SellerUser? user) {
    final initials = _getInitials(user?.name.isNotEmpty == true ? user!.name : user?.storeName);
    return Container(
      width: 60,
      height: 60,
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [AppColors.brand500, AppColors.brand700],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        shape: BoxShape.circle,
      ),
      alignment: Alignment.center,
      child: Text(
        initials,
        style: const TextStyle(
          fontSize: 20,
          fontWeight: FontWeight.w800,
          color: Colors.white,
          letterSpacing: 0.5,
        ),
      ),
    );
  }

  String _getInitials(String? text) {
    if (text == null || text.trim().isEmpty) return 'S';
    final parts = text.trim().split(RegExp(r'\s+'));
    if (parts.length >= 2) {
      return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
    }
    return text.trim().substring(0, text.trim().length >= 2 ? 2 : 1).toUpperCase();
  }

  Widget _buildOrderStatusCard(OrderCounts counts) {
    return InkWell(
      onTap: () => widget.onNavigateTab?.call(3),
      borderRadius: BorderRadius.circular(AppRadius.lg),
      child: Container(
        padding: const EdgeInsets.all(AppSpacing.lg),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(AppRadius.lg),
          border: Border.all(color: AppColors.slate200.withValues(alpha: 0.8)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Text(
                  'Order',
                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.ink),
                ),
                const SizedBox(width: 6),
                const Icon(Icons.schedule, size: 13, color: AppColors.slate400),
                const SizedBox(width: 3),
                Text(
                  DateFormat('dd/MM/yyyy HH:mm:ss').format(DateTime.now()),
                  style: const TextStyle(fontSize: 11, color: AppColors.slate400),
                ),
                const Spacer(),
                const Icon(Icons.chevron_right, size: 18, color: AppColors.slate400),
              ],
            ),
            const SizedBox(height: AppSpacing.lg),
            Row(
              children: [
                _buildOrderCount('To Process', counts.toProcess, highlight: true),
                _buildOrderCount('Shipping', counts.shipping),
                _buildOrderCount('Review', counts.toReview),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildOrderCount(String label, int count, {bool highlight = false}) {
    return Expanded(
      child: Column(
        children: [
          Text(
            '$count',
            style: TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.w700,
              color: highlight && count > 0 ? AppColors.danger : AppColors.ink,
            ),
          ),
          const SizedBox(height: 4),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(label, style: const TextStyle(fontSize: 12, color: AppColors.slate500)),
              const Icon(Icons.chevron_right, size: 14, color: AppColors.slate400),
            ],
          ),
        ],
      ),
    );
  }

  List<_OnboardingStep> _onboardingSteps(SellerUser? user, dynamic metrics) {
    final hasEmail = (user?.email ?? '').trim().isNotEmpty;
    final hasAddress = (user?.address ?? user?.location ?? '').trim().isNotEmpty;
    final hasKyc = user?.isKycApproved == true;
    final hasProducts = (metrics?.totalProducts ?? 0) > 0;

    return [
      _OnboardingStep(
        title: 'Add Email',
        isDone: hasEmail,
        icon: Icons.mail_outline_rounded,
        description: 'Add an email address so customers and support can reach you.',
        actionLabel: hasEmail ? 'Edit' : 'Add',
        onAction: () => _showAddEmailModal(user),
      ),
      _OnboardingStep(
        title: 'Add warehouse address',
        isDone: hasAddress,
        icon: Icons.store_mall_directory_outlined,
        description: 'Add your business address to make products visible to customers.',
        actionLabel: hasAddress ? 'Edit' : 'Add',
        onAction: () => _showAddWarehouseAddressModal(user),
      ),
      _OnboardingStep(
        title: 'Add ID & Bank documents',
        isDone: hasKyc,
        icon: Icons.verified_user_outlined,
        description: 'Verify your identity and bank details to receive payouts.',
        actionLabel: 'Verify',
        onAction: () => _open(const VerificationStatusScreen()),
      ),
      _OnboardingStep(
        title: 'Upload products',
        isDone: hasProducts,
        icon: Icons.inventory_2_outlined,
        description: 'List your first product and start selling.',
        actionLabel: 'Upload',
        onAction: () => _open(const AddProductScreen()),
      ),
    ];
  }

  void _showAddEmailModal(SellerUser? user) {
    final emailController = TextEditingController(text: user?.email ?? '');
    final authProvider = context.read<SellerAuthProvider>();
    final dashboardProvider = context.read<SellerDashboardProvider>();
    bool isSaving = false;
    String? errorText;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) => Container(
          padding: EdgeInsets.fromLTRB(
            AppSpacing.xl,
            AppSpacing.lg,
            AppSpacing.xl,
            MediaQuery.of(context).viewInsets.bottom + AppSpacing.xl,
          ),
          decoration: const BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.vertical(top: Radius.circular(AppRadius.xl)),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: AppColors.slate300,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: AppSpacing.md),
              Row(
                children: [
                  Container(
                    width: 38,
                    height: 38,
                    decoration: BoxDecoration(
                      color: AppColors.brand50,
                      borderRadius: BorderRadius.circular(AppRadius.md),
                    ),
                    child: const Icon(Icons.mail_outline_rounded, color: AppColors.brand600, size: 20),
                  ),
                  const SizedBox(width: AppSpacing.sm),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Add Email Address', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.ink)),
                        Text('Connect your business email for order and support updates', style: TextStyle(fontSize: 11, color: AppColors.slate500)),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, size: 20, color: AppColors.slate400),
                    onPressed: () => Navigator.of(ctx).pop(),
                  ),
                ],
              ),
              const SizedBox(height: AppSpacing.lg),
              TextField(
                controller: emailController,
                keyboardType: TextInputType.emailAddress,
                autofocus: true,
                decoration: InputDecoration(
                  labelText: 'Email Address',
                  hintText: 'e.g. store@example.com',
                  errorText: errorText,
                  prefixIcon: const Icon(Icons.alternate_email_rounded, size: 18),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
                ),
              ),
              const SizedBox(height: AppSpacing.xl),
              SizedBox(
                height: 46,
                child: ElevatedButton(
                  onPressed: isSaving
                      ? null
                      : () async {
                          final email = emailController.text.trim();
                          if (email.isEmpty || !email.contains('@')) {
                            setModalState(() => errorText = 'Please enter a valid email address');
                            return;
                          }
                          setModalState(() {
                            isSaving = true;
                            errorText = null;
                          });

                          try {
                            await _apiClient.post('/profile', body: {'email': email});
                            await authProvider.refreshProfile();
                            await dashboardProvider.fetchDashboard();
                            if (ctx.mounted) Navigator.of(ctx).pop();
                            if (context.mounted) {
                              AppSnackBar.success(context, 'Email saved successfully!');
                            }
                          } catch (e) {
                            setModalState(() => isSaving = false);
                            if (context.mounted) {
                              AppSnackBar.error(context, 'Failed to save email: $e');
                            }
                          }
                        },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.brand500,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
                    elevation: 0,
                  ),
                  child: isSaving
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                        )
                      : const Text('Save Email', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showAddWarehouseAddressModal(SellerUser? user) {
    final addressController = TextEditingController(text: user?.address ?? user?.location ?? '');
    final cityController = TextEditingController(text: user?.city ?? 'Doha');
    final countryController = TextEditingController(text: user?.country ?? 'Qatar');
    final authProvider = context.read<SellerAuthProvider>();
    final dashboardProvider = context.read<SellerDashboardProvider>();
    bool isSaving = false;
    String? errorText;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) => Container(
          padding: EdgeInsets.fromLTRB(
            AppSpacing.xl,
            AppSpacing.lg,
            AppSpacing.xl,
            MediaQuery.of(context).viewInsets.bottom + AppSpacing.xl,
          ),
          decoration: const BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.vertical(top: Radius.circular(AppRadius.xl)),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: AppColors.slate300,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: AppSpacing.md),
              Row(
                children: [
                  Container(
                    width: 38,
                    height: 38,
                    decoration: BoxDecoration(
                      color: AppColors.brand50,
                      borderRadius: BorderRadius.circular(AppRadius.md),
                    ),
                    child: const Icon(Icons.store_mall_directory_outlined, color: AppColors.brand600, size: 20),
                  ),
                  const SizedBox(width: AppSpacing.sm),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Add Warehouse Address', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.ink)),
                        Text('Dispatch location for parcel handoffs and fulfillment', style: TextStyle(fontSize: 11, color: AppColors.slate500)),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, size: 20, color: AppColors.slate400),
                    onPressed: () => Navigator.of(ctx).pop(),
                  ),
                ],
              ),
              const SizedBox(height: AppSpacing.lg),
              TextField(
                controller: addressController,
                autofocus: true,
                decoration: InputDecoration(
                  labelText: 'Street / Building Address',
                  hintText: 'e.g. Building 42, Street 810, Zone 57',
                  errorText: errorText,
                  prefixIcon: const Icon(Icons.place_outlined, size: 18),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
                ),
              ),
              const SizedBox(height: AppSpacing.md),
              Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: cityController,
                      decoration: InputDecoration(
                        labelText: 'City',
                        prefixIcon: const Icon(Icons.location_city_rounded, size: 18),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
                      ),
                    ),
                  ),
                  const SizedBox(width: AppSpacing.md),
                  Expanded(
                    child: TextField(
                      controller: countryController,
                      decoration: InputDecoration(
                        labelText: 'Country',
                        prefixIcon: const Icon(Icons.flag_outlined, size: 18),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: AppSpacing.xl),
              SizedBox(
                height: 46,
                child: ElevatedButton(
                  onPressed: isSaving
                      ? null
                      : () async {
                          final address = addressController.text.trim();
                          final city = cityController.text.trim();
                          final country = countryController.text.trim();
                          if (address.isEmpty) {
                            setModalState(() => errorText = 'Please enter warehouse address');
                            return;
                          }
                          setModalState(() {
                            isSaving = true;
                            errorText = null;
                          });

                          try {
                            await _apiClient.post(
                              '/profile',
                              body: {
                                'address': address,
                                'location': address,
                                'city': city,
                                'country': country,
                              },
                            );
                            await authProvider.refreshProfile();
                            await dashboardProvider.fetchDashboard();
                            if (ctx.mounted) Navigator.of(ctx).pop();
                            if (context.mounted) {
                              AppSnackBar.success(context, 'Warehouse address saved successfully!');
                            }
                          } catch (e) {
                            setModalState(() => isSaving = false);
                            if (context.mounted) {
                              AppSnackBar.error(context, 'Failed to save address: $e');
                            }
                          }
                        },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.brand500,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
                    elevation: 0,
                  ),
                  child: isSaving
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                        )
                      : const Text('Save Address', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  List<Widget> _buildOnboardingSection(SellerUser? user, dynamic metrics) {
    if (user == null || metrics == null) {
      return const [];
    }

    final steps = _onboardingSteps(user, metrics);
    final allCompleted = steps.every((step) => step.isDone);
    if (allCompleted) {
      return const [];
    }

    final firstPending = steps.indexWhere((step) => !step.isDone);
    final expanded = _expandedStep ?? (firstPending != -1 ? firstPending : 0);

    return [
      const SizedBox(height: AppSpacing.lg),
      Container(
        padding: const EdgeInsets.all(AppSpacing.md),
        decoration: BoxDecoration(
          color: AppColors.brand500,
          borderRadius: BorderRadius.circular(AppRadius.lg),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Padding(
              padding: EdgeInsets.fromLTRB(4, 4, 4, AppSpacing.md),
              child: Text(
                'Start your business right now!',
                style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: Colors.white),
              ),
            ),
            for (var i = 0; i < steps.length; i++) ...[
              _buildOnboardingStep(i, steps[i], isExpanded: i == expanded),
              if (i != steps.length - 1) const SizedBox(height: AppSpacing.sm),
            ],
          ],
        ),
      ),
    ];
  }

  Widget _buildOnboardingStep(int index, _OnboardingStep step, {required bool isExpanded}) {
    final leading = step.isDone
        ? Container(
            width: 24,
            height: 24,
            decoration: BoxDecoration(shape: BoxShape.circle, border: Border.all(color: Colors.white, width: 1.5)),
            child: const Icon(Icons.check, size: 15, color: Colors.white),
          )
        : Container(
            width: 24,
            height: 24,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: isExpanded ? AppColors.brand500 : null,
              border: isExpanded ? null : Border.all(color: Colors.white, width: 1.5),
            ),
            child: Text(
              '${index + 1}',
              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Colors.white),
            ),
          );

    return GestureDetector(
      onTap: step.isDone ? null : () => setState(() => _expandedStep = index),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        width: double.infinity,
        padding: const EdgeInsets.all(AppSpacing.md),
        decoration: BoxDecoration(
          color: isExpanded ? AppColors.surface : Colors.white.withValues(alpha: 0.18),
          borderRadius: BorderRadius.circular(AppRadius.md),
        ),
        child: Column(
          children: [
            Row(
              children: [
                leading,
                const SizedBox(width: AppSpacing.sm),
                Expanded(
                  child: Text(
                    step.title,
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w500,
                      color: isExpanded ? AppColors.ink : Colors.white,
                    ),
                  ),
                ),
              ],
            ),
            if (isExpanded) ...[
              const SizedBox(height: AppSpacing.lg),
              Icon(step.icon, size: 48, color: AppColors.brand400),
              const SizedBox(height: AppSpacing.md),
              Text(
                step.description,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 12, color: AppColors.slate700),
              ),
              const SizedBox(height: AppSpacing.md),
              SizedBox(
                width: double.infinity,
                height: 42,
                child: ElevatedButton(
                  onPressed: step.onAction,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.brand500,
                    foregroundColor: Colors.white,
                    elevation: 0,
                    shape: const StadiumBorder(),
                  ),
                  child: Text(step.actionLabel, style: const TextStyle(fontWeight: FontWeight.w600)),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildStatsGrid(dynamic metrics) {
    final revenue = metrics?.periodRevenue != null ? _currencyFormat.format(metrics.periodRevenue) : '0.00';
    final orders = metrics?.periodOrders?.toString() ?? '0';
    final activeProducts = metrics?.activeProducts?.toString() ?? '0';
    final views = metrics?.totalViews != null ? _formatViews(metrics.totalViews) : '0';

    final revPct = metrics?.revenueChangePct != null
        ? '${metrics.revenueChangePct >= 0 ? '+' : ''}${metrics.revenueChangePct}%'
        : null;
    final ordersPct = metrics?.ordersChangePct != null
        ? '${metrics.ordersChangePct >= 0 ? '+' : ''}${metrics.ordersChangePct}%'
        : null;

    Widget statItem({
      required String label,
      required String value,
      String? unit,
      String? changePct,
      bool isPositive = true,
      required IconData icon,
      required Color iconColor,
      required Color iconBgColor,
      VoidCallback? onTap,
    }) {
      return Expanded(
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(AppRadius.md),
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: AppSpacing.sm, horizontal: 2),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                Stack(
                  clipBehavior: Clip.none,
                  alignment: Alignment.center,
                  children: [
                    Container(
                      width: 36,
                      height: 36,
                      decoration: BoxDecoration(
                        color: iconBgColor,
                        borderRadius: BorderRadius.circular(AppRadius.md),
                      ),
                      child: Icon(icon, size: 18, color: iconColor),
                    ),
                    if (changePct != null)
                      Positioned(
                        top: -4,
                        right: -10,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1.5),
                          decoration: BoxDecoration(
                            color: isPositive ? AppColors.successBg : AppColors.dangerBg,
                            borderRadius: BorderRadius.circular(AppRadius.pill),
                            border: Border.all(
                              color: isPositive ? AppColors.success.withValues(alpha: 0.3) : AppColors.danger.withValues(alpha: 0.3),
                              width: 0.8,
                            ),
                          ),
                          child: Text(
                            changePct,
                            style: TextStyle(
                              fontSize: 9,
                              fontWeight: FontWeight.w700,
                              color: isPositive ? AppColors.success : AppColors.danger,
                              height: 1.1,
                            ),
                          ),
                        ),
                      ),
                  ],
                ),
                const SizedBox(height: AppSpacing.sm),
                FittedBox(
                  fit: BoxFit.scaleDown,
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.baseline,
                    textBaseline: TextBaseline.alphabetic,
                    children: [
                      Text(
                        value,
                        style: const TextStyle(
                          fontSize: 14.5,
                          fontWeight: FontWeight.w800,
                          color: AppColors.ink,
                          letterSpacing: -0.3,
                        ),
                      ),
                      if (unit != null) ...[
                        const SizedBox(width: 2),
                        Text(
                          unit,
                          style: const TextStyle(
                            fontSize: 9.5,
                            fontWeight: FontWeight.w600,
                            color: AppColors.slate500,
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  label,
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w500,
                    color: AppColors.slate500,
                  ),
                  textAlign: TextAlign.center,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
        ),
      );
    }

    Widget verticalDivider() {
      return Container(
        width: 1,
        height: 52,
        color: AppColors.slate100,
        margin: const EdgeInsets.symmetric(horizontal: 1),
      );
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: AppSpacing.xs, vertical: AppSpacing.md),
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
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          statItem(
            label: 'Revenue',
            value: revenue,
            unit: 'QAR',
            changePct: revPct,
            isPositive: (metrics?.revenueChangePct ?? 0) >= 0,
            icon: Icons.payments_outlined,
            iconColor: AppColors.brand600,
            iconBgColor: AppColors.brand50,
            onTap: () => _open(const WalletScreen()),
          ),
          verticalDivider(),
          statItem(
            label: 'Orders',
            value: orders,
            changePct: ordersPct,
            isPositive: (metrics?.ordersChangePct ?? 0) >= 0,
            icon: Icons.shopping_bag_outlined,
            iconColor: AppColors.info,
            iconBgColor: AppColors.infoBg,
            onTap: () => widget.onNavigateTab?.call(3),
          ),
          verticalDivider(),
          statItem(
            label: 'Listings',
            value: activeProducts,
            icon: Icons.inventory_2_outlined,
            iconColor: AppColors.warning,
            iconBgColor: AppColors.warningBg,
            onTap: () => _open(const CatalogScreen(initialTab: 0)),
          ),
          verticalDivider(),
          statItem(
            label: 'Total Views',
            value: views,
            icon: Icons.visibility_outlined,
            iconColor: AppColors.purple,
            iconBgColor: AppColors.purpleBg,
            onTap: () => _open(const CatalogScreen(initialTab: 0)),
          ),
        ],
      ),
    );
  }

  Widget _buildSalesChartCard(SellerDashboardProvider provider) {
    final chartData = provider.chartData;
    final hasData = chartData != null && chartData.revenue.isNotEmpty;

    return Container(
      padding: const EdgeInsets.all(AppSpacing.lg),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.slate200.withValues(alpha: 0.8)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Sales Overview',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w700,
                  color: AppColors.ink,
                ),
              ),
              Row(
                children: [
                  _buildPeriodPill(provider, 7, '7 Days'),
                  const SizedBox(width: 6),
                  _buildPeriodPill(provider, 30, '30 Days'),
                ],
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.lg),
          SizedBox(
            height: 180,
            child: hasData
                ? LineChart(
                    LineChartData(
                      gridData: FlGridData(
                        show: true,
                        drawVerticalLine: false,
                        horizontalInterval: _calcInterval(chartData.revenue),
                        getDrawingHorizontalLine: (value) => FlLine(
                          color: AppColors.slate100,
                          strokeWidth: 1,
                        ),
                      ),
                      titlesData: FlTitlesData(
                        show: true,
                        rightTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                        topTitles: const AxisTitles(sideTitles: SideTitles(showTitles: false)),
                        leftTitles: AxisTitles(
                          sideTitles: SideTitles(
                            showTitles: true,
                            reservedSize: 42,
                            getTitlesWidget: (value, meta) {
                              if (value == meta.max || value == meta.min) return const SizedBox.shrink();
                              return Text(
                                _compactCurrency(value),
                                style: const TextStyle(fontSize: 10, color: AppColors.slate400),
                              );
                            },
                          ),
                        ),
                        bottomTitles: AxisTitles(
                          sideTitles: SideTitles(
                            showTitles: true,
                            reservedSize: 22,
                            interval: (chartData.labels.length / 4).ceilToDouble().clamp(1.0, 30.0),
                            getTitlesWidget: (value, meta) {
                              final index = value.toInt();
                              if (index >= 0 && index < chartData.labels.length) {
                                return Padding(
                                  padding: const EdgeInsets.only(top: 4),
                                  child: Text(
                                    chartData.labels[index],
                                    style: const TextStyle(fontSize: 10, color: AppColors.slate400),
                                  ),
                                );
                              }
                              return const SizedBox.shrink();
                            },
                          ),
                        ),
                      ),
                      borderData: FlBorderData(show: false),
                      lineBarsData: [
                        LineChartBarData(
                          spots: chartData.revenue.asMap().entries.map((e) {
                            return FlSpot(e.key.toDouble(), e.value);
                          }).toList(),
                          isCurved: true,
                          curveSmoothness: 0.35,
                          color: const Color(0xFF7C83D4),
                          barWidth: 2.5,
                          isStrokeCapRound: true,
                          dotData: const FlDotData(show: false),
                          belowBarData: BarAreaData(
                            show: true,
                            color: const Color(0xFF7C83D4).withValues(alpha: 0.12),
                          ),
                        ),
                      ],
                    ),
                  )
                : const Center(
                    child: Text(
                      'No sales recorded in this period',
                      style: TextStyle(color: AppColors.slate400, fontSize: 13),
                    ),
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildPeriodPill(SellerDashboardProvider provider, int days, String label) {
    final isSelected = provider.selectedDays == days;
    return GestureDetector(
      onTap: () => provider.setDays(days),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.brand500 : AppColors.slate100,
          borderRadius: BorderRadius.circular(AppRadius.pill),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w600,
            color: isSelected ? Colors.white : AppColors.slate600,
          ),
        ),
      ),
    );
  }

  Widget _buildQuickActionsRow(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: _buildActionBtn(
            icon: Icons.add_circle_outline_rounded,
            label: 'Add Product',
            color: AppColors.brand500,
            onTap: () {
              Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const AddProductScreen()),
              );
            },
          ),
        ),
        const SizedBox(width: AppSpacing.sm),
        Expanded(
          child: _buildActionBtn(
            icon: Icons.receipt_long_outlined,
            label: 'All Orders',
            color: AppColors.info,
            onTap: () => widget.onNavigateTab?.call(3),
          ),
        ),
        const SizedBox(width: AppSpacing.sm),
        Expanded(
          child: _buildActionBtn(
            icon: Icons.account_balance_wallet_outlined,
            label: 'Payouts',
            color: AppColors.success,
            onTap: () => _open(const WalletScreen()),
          ),
        ),
        const SizedBox(width: AppSpacing.sm),
        Expanded(
          child: _buildActionBtn(
            icon: Icons.location_on_outlined,
            label: 'Seller Center',
            color: AppColors.brand600,
            onTap: () => _open(const SellerCenterScreen()),
          ),
        ),
      ],
    );
  }

  Widget _buildActionBtn({
    required IconData icon,
    required String label,
    required Color color,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(AppRadius.md),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: AppSpacing.md, horizontal: 8),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(AppRadius.md),
          border: Border.all(color: AppColors.slate200),
        ),
        child: Column(
          children: [
            Icon(icon, size: 22, color: color),
            const SizedBox(height: 6),
            Text(
              label,
              style: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: AppColors.ink,
              ),
              textAlign: TextAlign.center,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTopProductsSection(SellerDashboardProvider provider) {
    return Container(
      padding: const EdgeInsets.all(AppSpacing.lg),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.slate200.withValues(alpha: 0.8)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Top Performing Products',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w700,
              color: AppColors.ink,
            ),
          ),
          const SizedBox(height: AppSpacing.md),
          ...provider.topProducts.asMap().entries.map((entry) {
            final index = entry.key + 1;
            final product = entry.value;

            return Padding(
              padding: const EdgeInsets.only(bottom: AppSpacing.md),
              child: Row(
                children: [
                  Container(
                    width: 24,
                    height: 24,
                    decoration: BoxDecoration(
                      color: index == 1 ? AppColors.brand100 : AppColors.slate100,
                      shape: BoxShape.circle,
                    ),
                    alignment: Alignment.center,
                    child: Text(
                      '$index',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        color: index == 1 ? AppColors.brand700 : AppColors.slate600,
                      ),
                    ),
                  ),
                  const SizedBox(width: AppSpacing.sm),
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: AppColors.slate100,
                      borderRadius: BorderRadius.circular(AppRadius.sm),
                    ),
                    clipBehavior: Clip.antiAlias,
                    child: product.thumbnail != null
                        ? Image.network(
                            product.thumbnail!,
                            fit: BoxFit.cover,
                            errorBuilder: (_, _, _) => const Icon(Icons.image, size: 20, color: AppColors.slate400),
                          )
                        : const Icon(Icons.image, size: 20, color: AppColors.slate400),
                  ),
                  const SizedBox(width: AppSpacing.md),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          product.title,
                          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.ink),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        Text(
                          '${product.unitsSold} units sold',
                          style: const TextStyle(fontSize: 11, color: AppColors.slate400),
                        ),
                      ],
                    ),
                  ),
                  Text(
                    '${_currencyFormat.format(product.revenue)} QAR',
                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.ink),
                  ),
                ],
              ),
            );
          }),
        ],
      ),
    );
  }

  Widget _buildRecentOrdersSection(BuildContext context, SellerDashboardProvider provider) {
    return Container(
      padding: const EdgeInsets.all(AppSpacing.lg),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.slate200.withValues(alpha: 0.8)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Recent Orders',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w700,
                  color: AppColors.ink,
                ),
              ),
              TextButton(
                onPressed: () => widget.onNavigateTab?.call(3),
                style: TextButton.styleFrom(
                  padding: EdgeInsets.zero,
                  minimumSize: Size.zero,
                  tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                ),
                child: const Text(
                  'View All',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: AppColors.brand600,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: AppSpacing.md),
          if (provider.recentOrders.isEmpty)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 24),
              child: Center(
                child: Text('No orders received yet', style: TextStyle(color: AppColors.slate400, fontSize: 13)),
              ),
            )
          else
            ...provider.recentOrders.map((order) {
              return InkWell(
                onTap: () {
                  Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => OrderDetailScreen(vendorOrderId: order.id),
                    ),
                  );
                },
                child: Padding(
                  padding: const EdgeInsets.symmetric(vertical: 10),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: AppColors.slate100,
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          order.orderNumber,
                          style: const TextStyle(
                            fontFamily: 'monospace',
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: AppColors.slate600,
                          ),
                        ),
                      ),
                      const SizedBox(width: AppSpacing.md),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              order.customerName,
                              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.ink),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                            Text(
                              '${order.itemsCount} ${order.itemsCount == 1 ? 'item' : 'items'}',
                              style: const TextStyle(fontSize: 11, color: AppColors.slate400),
                            ),
                          ],
                        ),
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          StatusBadge(status: order.status, isSmall: true),
                          const SizedBox(height: 4),
                          Text(
                            '${_currencyFormat.format(order.total)} QAR',
                            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.ink),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              );
            }),
        ],
      ),
    );
  }

  String _formatViews(int views) {
    if (views >= 1000) {
      return '${(views / 1000).toStringAsFixed(1)}K';
    }
    return views.toString();
  }

  double _calcInterval(List<double> values) {
    if (values.isEmpty) return 1000.0;
    final max = values.reduce((a, b) => a > b ? a : b);
    if (max <= 0) return 500.0;
    return (max / 4).clamp(100.0, 50000.0);
  }

  String _compactCurrency(double val) {
    if (val >= 1000) {
      return '${(val / 1000).toStringAsFixed(0)}K';
    }
    return val.toInt().toString();
  }
}
