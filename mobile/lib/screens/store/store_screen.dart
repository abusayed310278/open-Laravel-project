import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/network/api_config.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/product_card.dart';
import '../../data/models/chat_conversation.dart';
import '../../data/models/product.dart';
import '../../data/models/seller.dart';
import '../../providers/product_provider.dart';
import '../chat/chat_conversation_screen.dart';
import '../product/product_detail_screen.dart';

class StoreScreen extends StatefulWidget {
  const StoreScreen({super.key, required this.seller});

  final Seller seller;

  @override
  State<StoreScreen> createState() => _StoreScreenState();
}

class _StoreScreenState extends State<StoreScreen> {
  late Seller _seller;
  List<Product> _products = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _seller = widget.seller;
    Future.microtask(_loadStoreData);
  }

  Future<void> _loadStoreData() async {
    final sellerId = widget.seller.id;
    if (sellerId.isEmpty) {
      if (mounted) setState(() => _isLoading = false);
      return;
    }

    final productProvider = context.read<ProductProvider>();

    try {
      final productsFuture = productProvider.fetchProductsBySeller(sellerId);
      final sellerFuture = productProvider.fetchSellerDetail(sellerId);

      final products = await productsFuture.catchError((_) => <Product>[]);
      final updatedSeller = await sellerFuture.catchError((_) => null);

      if (!mounted) return;

      setState(() {
        _products = products;
        if (updatedSeller != null) {
          _seller = updatedSeller.bannerUrl.isEmpty && _seller.bannerUrl.isNotEmpty
              ? updatedSeller.copyWith(bannerUrl: _seller.bannerUrl)
              : updatedSeller;
        } else if (products.isNotEmpty && products.first.seller != null) {
          final pSeller = products.first.seller!;
          _seller = pSeller.bannerUrl.isNotEmpty || _seller.bannerUrl.isEmpty ? pSeller : _seller;
        }
        _isLoading = false;
      });
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final avatar = _seller.avatarUrl.isNotEmpty
        ? _seller.avatarUrl
        : (_seller.name.toLowerCase().contains('admin') ? ApiConfig.appIconUrl : '');

    final effectiveBanner = _seller.bannerUrl.isNotEmpty
        ? _seller.bannerUrl
        : ApiConfig.appBannerUrl;

    final locationText = _seller.city.isNotEmpty ? _seller.city : 'Doha, Qatar';
    final memberText = _seller.memberSince.isNotEmpty ? ' · Member since ${_seller.memberSince}' : '';
    final subtitle = '$locationText$memberText';

    final effectiveProductCount = _products.isNotEmpty ? _products.length : _seller.productCount;
    final effectiveReviewCount = _seller.reviewCount > 0 ? _seller.reviewCount : (_products.length * 3);

    return Scaffold(
      body: CustomScrollView(
        slivers: [
          SliverAppBar(
            pinned: true,
            expandedHeight: 200,
            backgroundColor: AppColors.brand600,
            flexibleSpace: FlexibleSpaceBar(
              background: Stack(
                fit: StackFit.expand,
                children: [
                  if (effectiveBanner.isNotEmpty)
                    Image.network(
                      effectiveBanner,
                      fit: BoxFit.cover,
                      errorBuilder: (context, error, stackTrace) {
                        if (effectiveBanner != ApiConfig.appBannerUrl && ApiConfig.appBannerUrl.isNotEmpty) {
                          return Image.network(
                            ApiConfig.appBannerUrl,
                            fit: BoxFit.cover,
                            errorBuilder: (_, _, _) => Container(
                              decoration: const BoxDecoration(
                                gradient: LinearGradient(
                                  colors: [AppColors.brand600, AppColors.brand400],
                                  begin: Alignment.topLeft,
                                  end: Alignment.bottomRight,
                                ),
                              ),
                            ),
                          );
                        }
                        return Container(
                          decoration: const BoxDecoration(
                            gradient: LinearGradient(
                              colors: [AppColors.brand600, AppColors.brand400],
                              begin: Alignment.topLeft,
                              end: Alignment.bottomRight,
                            ),
                          ),
                        );
                      },
                    )
                  else
                    Container(
                      decoration: const BoxDecoration(
                        gradient: LinearGradient(
                          colors: [AppColors.brand600, AppColors.brand400],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                      ),
                    ),
                  // Gradient overlay for crisp readability of text & avatar
                  Container(
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [
                          effectiveBanner.isNotEmpty ? const Color(0x59000000) : const Color(0x00000000),
                          effectiveBanner.isNotEmpty ? const Color(0xBF000000) : const Color(0x40000000),
                        ],
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                      ),
                    ),
                  ),
                  SafeArea(
                    child: Padding(
                      padding: const EdgeInsets.all(AppSpacing.lg),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.end,
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          CircleAvatar(
                            radius: 28,
                            backgroundColor: Colors.white,
                            backgroundImage: avatar.isNotEmpty ? NetworkImage(avatar) : null,
                            child: avatar.isEmpty
                                ? Text(
                                    _seller.avatarInitial,
                                    style: const TextStyle(
                                      color: AppColors.brand700,
                                      fontWeight: FontWeight.w800,
                                      fontSize: 18,
                                    ),
                                  )
                                : null,
                          ),
                          const SizedBox(height: AppSpacing.sm),
                          Row(
                            children: [
                              Text(
                                _seller.name,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 18,
                                  fontWeight: FontWeight.w800,
                                ),
                              ),
                              if (_seller.isVerified) ...[
                                const SizedBox(width: 6),
                                const Icon(Icons.verified_rounded, color: Colors.white, size: 18),
                              ],
                            ],
                          ),
                          Text(
                            subtitle,
                            style: const TextStyle(color: Colors.white70, fontSize: 12),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
            actions: [
              IconButton(
                icon: const Icon(Icons.chat_bubble_outline_rounded, color: Colors.white),
                onPressed: () => Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (_) => ChatConversationScreen(
                      conversation: ChatConversation(
                        id: 'c_${_seller.id}',
                        sellerName: _seller.name,
                        avatarInitial: _seller.avatarInitial,
                        lastMessage: '',
                        time: 'Now',
                        messages: const [],
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.all(AppSpacing.lg),
              child: Row(
                children: [
                  Expanded(
                    child: _StoreStat(
                      label: 'Rating',
                      value: _seller.rating.toStringAsFixed(1),
                      icon: Icons.star_rounded,
                    ),
                  ),
                  Expanded(
                    child: _StoreStat(
                      label: 'Reviews',
                      value: '$effectiveReviewCount',
                      icon: Icons.reviews_outlined,
                    ),
                  ),
                  Expanded(
                    child: _StoreStat(
                      label: 'Products',
                      value: '$effectiveProductCount',
                      icon: Icons.inventory_2_outlined,
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SliverToBoxAdapter(
            child: Padding(
              padding: EdgeInsets.symmetric(horizontal: AppSpacing.lg),
              child: Text(
                'Products',
                style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.ink),
              ),
            ),
          ),
          const SliverToBoxAdapter(child: SizedBox(height: AppSpacing.md)),
          if (_isLoading)
            const SliverToBoxAdapter(
              child: Padding(
                padding: EdgeInsets.all(AppSpacing.xxl),
                child: Center(
                  child: CircularProgressIndicator(color: AppColors.brand600),
                ),
              ),
            )
          else if (_products.isEmpty)
            const SliverToBoxAdapter(
              child: Padding(
                padding: EdgeInsets.all(AppSpacing.xxl),
                child: Center(
                  child: Column(
                    children: [
                      Icon(Icons.inventory_2_outlined, size: 48, color: AppColors.slate300),
                      SizedBox(height: AppSpacing.sm),
                      Text(
                        'No products available from this store yet.',
                        style: TextStyle(color: AppColors.slate500, fontSize: 13),
                      ),
                    ],
                  ),
                ),
              ),
            )
          else
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(AppSpacing.lg, 0, AppSpacing.lg, AppSpacing.xxl),
              sliver: SliverGrid(
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 2,
                  mainAxisSpacing: AppSpacing.md,
                  crossAxisSpacing: AppSpacing.md,
                  childAspectRatio: 0.62,
                ),
                delegate: SliverChildBuilderDelegate(
                  (context, i) => ProductCard(
                    product: _products[i],
                    onTap: () => Navigator.of(context).push(
                      MaterialPageRoute(
                        builder: (_) => ProductDetailScreen(product: _products[i]),
                      ),
                    ),
                  ),
                  childCount: _products.length,
                ),
              ),
            ),
        ],
      ),
    );
  }
}

class _StoreStat extends StatelessWidget {
  const _StoreStat({required this.label, required this.value, required this.icon});
  final String label;
  final String value;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Icon(icon, color: AppColors.brand600, size: 20),
        const SizedBox(height: 4),
        Text(value, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15)),
        Text(label, style: const TextStyle(fontSize: 11, color: AppColors.slate500)),
      ],
    );
  }
}
