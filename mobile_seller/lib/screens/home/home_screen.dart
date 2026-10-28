import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../core/widgets/product_card.dart';
import '../../core/widgets/section_header.dart';
import '../../data/mock/mock_data.dart';
import '../../data/models/product.dart';
import '../../providers/location_provider.dart';
import '../../providers/product_provider.dart';
import '../notifications/notifications_screen.dart';
import '../product/product_detail_screen.dart';
import '../product/product_list_screen.dart';
import '../search/search_screen.dart';
import '../wishlist/wishlist_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  String _selectedCondition = 'all';

  final List<({String key, String label})> _conditions = const [
    (key: 'all', label: 'All'),
    (key: 'new', label: 'New'),
    (key: 'used', label: 'Used'),
    (key: 'refurbished', label: 'Refurbished'),
    (key: 'openbox', label: 'Openbox'),
    (key: 'out_of_stock', label: 'Out of Stock'),
  ];

  @override
  Widget build(BuildContext context) {
    final productProvider = context.watch<ProductProvider>();
    final liveProducts = productProvider.products;
    final displayProducts = liveProducts.isNotEmpty ? liveProducts : MockData.products;

    final openboxProducts = displayProducts.where((p) => p.isOpenboxCondition).toList();
    final refurbishedProducts = displayProducts.where((p) => p.isRefurbishedCondition).toList();
    final newProducts = displayProducts.where((p) => p.isNewCondition).toList();
    final usedProducts = displayProducts.where((p) => p.isUsedCondition).toList();
    final outOfStockProducts = displayProducts.where((p) => p.isOutOfStock).toList();

    final List<Product> activeProducts = switch (_selectedCondition) {
      'openbox' => openboxProducts,
      'refurbished' => refurbishedProducts,
      'new' => newProducts,
      'used' => usedProducts,
      'out_of_stock' => outOfStockProducts,
      _ => displayProducts,
    };

    return Scaffold(
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: () async {
            final p = context.read<ProductProvider>();
            final loc = context.read<LocationProvider>();
            await Future.wait([
              p.fetchProducts(),
              loc.fetchLiveLocation(),
            ]);
          },
          child: CustomScrollView(
            slivers: [
              SliverToBoxAdapter(child: _TopBar()),
              SliverToBoxAdapter(child: _SearchBar()),
              const SliverToBoxAdapter(child: SizedBox(height: AppSpacing.md)),

              // Condition Filter Pills Strip
              SliverToBoxAdapter(
                child: SizedBox(
                  height: 40,
                  child: ListView.separated(
                    scrollDirection: Axis.horizontal,
                    padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
                    itemCount: _conditions.length,
                    separatorBuilder: (_, _) => const SizedBox(width: AppSpacing.sm),
                    itemBuilder: (context, index) {
                      final item = _conditions[index];
                      final isSelected = _selectedCondition == item.key;

                      return InkWell(
                        onTap: () => setState(() => _selectedCondition = item.key),
                        borderRadius: BorderRadius.circular(AppRadius.md),
                        child: AnimatedContainer(
                          duration: const Duration(milliseconds: 200),
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                          decoration: BoxDecoration(
                            color: isSelected ? AppColors.brand600 : AppColors.surface,
                            borderRadius: BorderRadius.circular(AppRadius.md),
                            border: Border.all(
                              color: isSelected ? AppColors.brand600 : AppColors.slate200,
                              width: 1.2,
                            ),
                            boxShadow: isSelected
                                ? [
                                    BoxShadow(
                                      color: AppColors.brand600.withValues(alpha: 0.25),
                                      blurRadius: 8,
                                      offset: const Offset(0, 2),
                                    ),
                                  ]
                                : null,
                          ),
                          alignment: Alignment.center,
                          child: Text(
                            item.label,
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.w700,
                              color: isSelected ? Colors.white : AppColors.ink,
                            ),
                          ),
                        ),
                      );
                    },
                  ),
                ),
              ),

              const SliverToBoxAdapter(child: SizedBox(height: AppSpacing.lg)),

              // When 'all' is selected: Show dedicated condition carousels followed by full product feed
              if (_selectedCondition == 'all') ...[
                // 1. New Section
                if (newProducts.isNotEmpty) ...[
                  SliverToBoxAdapter(
                    child: SectionHeader(
                      title: 'New',
                      actionLabel: 'See all',
                      onAction: () => Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) => ProductListScreen(title: 'New Products', products: newProducts),
                        ),
                      ),
                    ),
                  ),
                  const SliverToBoxAdapter(child: SizedBox(height: AppSpacing.sm)),
                  SliverToBoxAdapter(child: _HorizontalProducts(products: newProducts)),
                  const SliverToBoxAdapter(child: SizedBox(height: AppSpacing.xl)),
                ],

                // 2. Used Section
                if (usedProducts.isNotEmpty) ...[
                  SliverToBoxAdapter(
                    child: SectionHeader(
                      title: 'Used',
                      actionLabel: 'See all',
                      onAction: () => Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) => ProductListScreen(title: 'Used Products', products: usedProducts),
                        ),
                      ),
                    ),
                  ),
                  const SliverToBoxAdapter(child: SizedBox(height: AppSpacing.sm)),
                  SliverToBoxAdapter(child: _HorizontalProducts(products: usedProducts)),
                  const SliverToBoxAdapter(child: SizedBox(height: AppSpacing.xl)),
                ],

                // 3. Refurbished Section
                if (refurbishedProducts.isNotEmpty) ...[
                  SliverToBoxAdapter(
                    child: SectionHeader(
                      title: 'Refurbished',
                      actionLabel: 'See all',
                      onAction: () => Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) => ProductListScreen(title: 'Refurbished Products', products: refurbishedProducts),
                        ),
                      ),
                    ),
                  ),
                  const SliverToBoxAdapter(child: SizedBox(height: AppSpacing.sm)),
                  SliverToBoxAdapter(child: _HorizontalProducts(products: refurbishedProducts)),
                  const SliverToBoxAdapter(child: SizedBox(height: AppSpacing.xl)),
                ],

                // 4. Openbox Section
                if (openboxProducts.isNotEmpty) ...[
                  SliverToBoxAdapter(
                    child: SectionHeader(
                      title: 'Openbox',
                      actionLabel: 'See all',
                      onAction: () => Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) => ProductListScreen(title: 'Openbox Products', products: openboxProducts),
                        ),
                      ),
                    ),
                  ),
                  const SliverToBoxAdapter(child: SizedBox(height: AppSpacing.sm)),
                  SliverToBoxAdapter(child: _HorizontalProducts(products: openboxProducts)),
                  const SliverToBoxAdapter(child: SizedBox(height: AppSpacing.xl)),
                ],

                // 5. Grid of All Products
                const SliverToBoxAdapter(
                  child: SectionHeader(
                    title: 'All Products',
                  ),
                ),
                const SliverToBoxAdapter(child: SizedBox(height: AppSpacing.sm)),
              ] else ...[
                // When a single condition filter is selected
                SliverToBoxAdapter(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          switch (_selectedCondition) {
                            'new' => 'New Products',
                            'used' => 'Used Products',
                            'refurbished' => 'Refurbished Products',
                            'openbox' => 'Openbox Products',
                            'out_of_stock' => 'Out of Stock Products',
                            _ => '${_selectedCondition[0].toUpperCase()}${_selectedCondition.substring(1)} Products',
                          },
                          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.ink),
                        ),
                        TextButton.icon(
                          onPressed: () => setState(() => _selectedCondition = 'all'),
                          icon: const Icon(Icons.close_rounded, size: 16),
                          label: const Text('Show All', style: TextStyle(fontWeight: FontWeight.w700)),
                        ),
                      ],
                    ),
                  ),
                ),
                const SliverToBoxAdapter(child: SizedBox(height: AppSpacing.sm)),
              ],

              if (productProvider.isLoading && liveProducts.isEmpty)
                const SliverToBoxAdapter(
                  child: Padding(
                    padding: EdgeInsets.all(AppSpacing.xxl),
                    child: Center(
                      child: CircularProgressIndicator(),
                    ),
                  ),
                )
              else if (activeProducts.isEmpty)
                SliverToBoxAdapter(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(horizontal: AppSpacing.xxl, vertical: AppSpacing.xxxl),
                    child: Center(
                      child: Column(
                        children: [
                          const Icon(Icons.inventory_2_outlined, size: 48, color: AppColors.slate300),
                          const SizedBox(height: AppSpacing.md),
                          Text(
                            _selectedCondition == 'out_of_stock'
                                ? 'No out of stock products'
                                : 'No $_selectedCondition products found',
                            style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.ink),
                          ),
                          const SizedBox(height: AppSpacing.sm),
                          OutlinedButton(
                            onPressed: () => setState(() => _selectedCondition = 'all'),
                            child: const Text('View All Products'),
                          ),
                        ],
                      ),
                    ),
                  ),
                )
              else
                SliverPadding(
                  padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
                  sliver: SliverGrid(
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 2,
                      mainAxisSpacing: AppSpacing.md,
                      crossAxisSpacing: AppSpacing.md,
                      childAspectRatio: 0.58,
                    ),
                    delegate: SliverChildBuilderDelegate(
                      (context, i) {
                        final product = activeProducts[i];
                        return ProductCard(
                          product: product,
                          onTap: () => Navigator.of(context).push(
                            MaterialPageRoute(builder: (_) => ProductDetailScreen(product: product)),
                          ),
                        );
                      },
                      childCount: activeProducts.length,
                    ),
                  ),
                ),
              const SliverToBoxAdapter(child: SizedBox(height: AppSpacing.xxxl)),
            ],
          ),
        ),
      ),
    );
  }
}

class _TopBar extends StatelessWidget {
  void _showLocationSheet(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => const _LocationSelectorBottomSheet(),
    );
  }

  @override
  Widget build(BuildContext context) {
    final locationProvider = context.watch<LocationProvider>();
    final locationLabel = locationProvider.currentLocation;

    return Padding(
      padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.sm, AppSpacing.lg, 0),
      child: Row(
        children: [
          Expanded(
            child: InkWell(
              onTap: () => _showLocationSheet(context),
              borderRadius: BorderRadius.circular(AppRadius.md),
              child: Padding(
                padding: const EdgeInsets.symmetric(vertical: 4),
                child: Row(
                  children: [
                    const Icon(Icons.location_on_rounded, size: 18, color: AppColors.brand600),
                    const SizedBox(width: 4),
                    Flexible(
                      child: Text.rich(
                        TextSpan(
                          children: [
                            const TextSpan(text: 'Deliver to  ', style: TextStyle(fontSize: 12, color: AppColors.slate500)),
                            TextSpan(
                              text: locationLabel,
                              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.ink),
                            ),
                          ],
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    const SizedBox(width: 3),
                    const Icon(Icons.keyboard_arrow_down_rounded, size: 16, color: AppColors.slate400),
                  ],
                ),
              ),
            ),
          ),
          IconButton(
            icon: const Icon(Icons.favorite_border_rounded),
            onPressed: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const WishlistScreen())),
          ),
          IconButton(
            icon: const Icon(Icons.notifications_none_rounded),
            onPressed: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const NotificationsScreen())),
          ),
        ],
      ),
    );
  }
}

class _LocationSelectorBottomSheet extends StatefulWidget {
  const _LocationSelectorBottomSheet();

  @override
  State<_LocationSelectorBottomSheet> createState() => _LocationSelectorBottomSheetState();
}

class _LocationSelectorBottomSheetState extends State<_LocationSelectorBottomSheet> {
  final TextEditingController _searchController = TextEditingController();
  List<Map<String, dynamic>> _searchResults = [];
  List<Map<String, dynamic>> _popularZones = [];
  bool _isSearching = false;

  @override
  void initState() {
    super.initState();
    _loadPopularZones();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadPopularZones() async {
    final loc = context.read<LocationProvider>();
    final zones = await loc.getPopularZones();
    if (mounted) {
      setState(() => _popularZones = zones);
    }
  }

  Future<void> _onSearch(String query) async {
    if (query.trim().isEmpty) {
      setState(() {
        _searchResults = [];
        _isSearching = false;
      });
      return;
    }

    setState(() => _isSearching = true);
    final loc = context.read<LocationProvider>();
    final results = await loc.searchPlaces(query);
    if (mounted) {
      setState(() {
        _searchResults = results;
        _isSearching = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final locProvider = context.watch<LocationProvider>();

    return Container(
      constraints: BoxConstraints(
        maxHeight: MediaQuery.of(context).size.height * 0.78,
      ),
      padding: EdgeInsets.only(
        bottom: MediaQuery.of(context).viewInsets.bottom,
      ),
      decoration: const BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.vertical(top: Radius.circular(AppRadius.xl)),
      ),
      child: SafeArea(
        top: false,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: AppSpacing.sm),
            Center(
              child: Container(
                width: 36,
                height: 4,
                decoration: BoxDecoration(
                  color: AppColors.slate300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.md, AppSpacing.lg, AppSpacing.xs),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: const [
                      Text(
                        'Select Delivery Location',
                        style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.ink),
                      ),
                      SizedBox(height: 2),
                      Text(
                        'Live location resolved via Google Maps backend',
                        style: TextStyle(fontSize: 11, color: AppColors.slate500),
                      ),
                    ],
                  ),
                  IconButton(
                    icon: const Icon(Icons.close_rounded, size: 20, color: AppColors.slate400),
                    onPressed: () => Navigator.of(context).pop(),
                  ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.sm),
              child: Container(
                decoration: BoxDecoration(
                  color: AppColors.slate100,
                  borderRadius: BorderRadius.circular(AppRadius.md),
                ),
                child: TextField(
                  controller: _searchController,
                  onChanged: _onSearch,
                  decoration: const InputDecoration(
                    hintText: 'Search area, street or city...',
                    hintStyle: TextStyle(fontSize: 13, color: AppColors.slate400),
                    prefixIcon: Icon(Icons.search_rounded, size: 20, color: AppColors.slate400),
                    border: InputBorder.none,
                    contentPadding: EdgeInsets.symmetric(vertical: 12),
                  ),
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.xs),
              child: InkWell(
                onTap: locProvider.isLoading
                    ? null
                    : () async {
                        await locProvider.fetchLiveLocation();
                        if (context.mounted) {
                          Navigator.of(context).pop();
                          AppSnackBar.success(context, 'Live location updated: ${locProvider.currentLocation}');
                        }
                      },
                borderRadius: BorderRadius.circular(AppRadius.md),
                child: Container(
                  padding: const EdgeInsets.all(AppSpacing.md),
                  decoration: BoxDecoration(
                    color: AppColors.brand50,
                    borderRadius: BorderRadius.circular(AppRadius.md),
                    border: Border.all(color: AppColors.brand200),
                  ),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: const BoxDecoration(
                          color: AppColors.brand500,
                          shape: BoxShape.circle,
                        ),
                        child: locProvider.isLoading
                            ? const SizedBox(
                                width: 14,
                                height: 14,
                                child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                              )
                            : const Icon(Icons.my_location_rounded, color: Colors.white, size: 16),
                      ),
                      const SizedBox(width: AppSpacing.md),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Use Current Live Location',
                              style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.brand700),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              locProvider.currentLocation,
                              style: const TextStyle(fontSize: 11, color: AppColors.slate600),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ],
                        ),
                      ),
                      const Icon(Icons.arrow_forward_ios_rounded, size: 12, color: AppColors.brand600),
                    ],
                  ),
                ),
              ),
            ),
            const Divider(height: AppSpacing.lg),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
              child: Text(
                _searchResults.isNotEmpty ? 'Search Results' : 'Popular Delivery Zones',
                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.slate500),
              ),
            ),
            const SizedBox(height: 6),
            Expanded(
              child: _isSearching
                  ? const Center(child: CircularProgressIndicator())
                  : ListView(
                      padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
                      children: [
                        if (_searchResults.isNotEmpty)
                          ..._searchResults.map((item) {
                            final label = item['label']?.toString() ?? item['formatted_address']?.toString() ?? 'Location';
                            final address = item['formatted_address']?.toString() ?? label;
                            final lat = (item['latitude'] is num) ? (item['latitude'] as num).toDouble() : null;
                            final lng = (item['longitude'] is num) ? (item['longitude'] as num).toDouble() : null;

                            return ListTile(
                              contentPadding: EdgeInsets.zero,
                              leading: const Icon(Icons.place_outlined, color: AppColors.brand600, size: 22),
                              title: Text(label, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.ink)),
                              subtitle: Text(address, style: const TextStyle(fontSize: 11, color: AppColors.slate400), maxLines: 1, overflow: TextOverflow.ellipsis),
                              onTap: () {
                                locProvider.selectLocation(label: label, address: address, lat: lat, lng: lng);
                                Navigator.of(context).pop();
                                AppSnackBar.success(context, 'Delivery location set to $label');
                              },
                            );
                          })
                        else
                          ..._popularZones.map((zone) {
                            final label = zone['label']?.toString() ?? '';
                            final district = zone['district']?.toString() ?? '';
                            final lat = (zone['latitude'] is num) ? (zone['latitude'] as num).toDouble() : null;
                            final lng = (zone['longitude'] is num) ? (zone['longitude'] as num).toDouble() : null;
                            final isSelected = locProvider.currentLocation.toLowerCase().contains(label.toLowerCase());

                            return ListTile(
                              contentPadding: EdgeInsets.zero,
                              leading: Container(
                                padding: const EdgeInsets.all(6),
                                decoration: BoxDecoration(
                                  color: isSelected ? AppColors.brand100 : AppColors.slate100,
                                  shape: BoxShape.circle,
                                ),
                                child: Icon(
                                  isSelected ? Icons.check_rounded : Icons.location_city_rounded,
                                  color: isSelected ? AppColors.brand600 : AppColors.slate500,
                                  size: 16,
                                ),
                              ),
                              title: Text(
                                label,
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: isSelected ? FontWeight.w700 : FontWeight.w600,
                                  color: isSelected ? AppColors.brand700 : AppColors.ink,
                                ),
                              ),
                              subtitle: district.isNotEmpty
                                  ? Text(district, style: const TextStyle(fontSize: 11, color: AppColors.slate400))
                                  : null,
                              trailing: isSelected
                                  ? const Icon(Icons.check_circle_rounded, color: AppColors.brand600, size: 18)
                                  : null,
                              onTap: () {
                                locProvider.selectLocation(label: label, address: label, lat: lat, lng: lng);
                                Navigator.of(context).pop();
                                AppSnackBar.success(context, 'Delivery location set to $label');
                              },
                            );
                          }),
                      ],
                    ),
            ),
          ],
        ),
      ),
    );
  }
}

class _SearchBar extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
      child: GestureDetector(
        onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const SearchScreen())),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.md),
          decoration: BoxDecoration(
            color: AppColors.slate100,
            borderRadius: BorderRadius.circular(AppRadius.md),
          ),
          child: const Row(
            children: [
              Icon(Icons.search_rounded, color: AppColors.slate400, size: 20),
              SizedBox(width: AppSpacing.sm),
              Text('Search phones, laptops, watches...', style: TextStyle(color: AppColors.slate400, fontSize: 13)),
            ],
          ),
        ),
      ),
    );
  }
}



class _HorizontalProducts extends StatelessWidget {
  const _HorizontalProducts({required this.products});

  final List<Product> products;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 275,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
        itemCount: products.length,
        separatorBuilder: (_, _) => const SizedBox(width: AppSpacing.md),
        itemBuilder: (context, i) {
          final product = products[i];
          return SizedBox(
            width: 160,
            child: ProductCard(
              product: product,
              onTap: () => Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => ProductDetailScreen(product: product)),
              ),
            ),
          );
        },
      ),
    );
  }
}
