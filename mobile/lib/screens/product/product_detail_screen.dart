import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:share_plus/share_plus.dart';

import '../../core/network/api_config.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/utils/formatters.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../core/widgets/badges.dart';
import '../../core/widgets/product_card.dart';
import '../../core/widgets/rating_stars.dart';
import '../../data/mock/app_state.dart';
import '../../data/mock/mock_data.dart';
import '../../data/models/chat_conversation.dart';
import '../../data/models/product.dart';
import '../../data/models/seller.dart';
import '../../providers/auth_provider.dart';
import '../../providers/cart_provider.dart';
import '../../providers/product_provider.dart';
import '../auth/login_screen.dart';
import '../cart/cart_screen.dart';
import '../chat/chat_conversation_screen.dart';
import '../store/store_screen.dart';

class _ProductTabItem {
  final String title;
  final Widget content;

  const _ProductTabItem({required this.title, required this.content});
}

class ProductDetailScreen extends StatefulWidget {
  const ProductDetailScreen({super.key, required this.product});

  final Product product;

  @override
  State<ProductDetailScreen> createState() => _ProductDetailScreenState();
}

class _ProductDetailScreenState extends State<ProductDetailScreen> {
  int _galleryIndex = 0;
  int _quantity = 1;
  Product? _detailedProduct;

  @override
  void initState() {
    super.initState();
    _detailedProduct = widget.product;
    Future.microtask(_loadFullDetail);
  }

  Future<void> _loadFullDetail() async {
    final slug = widget.product.slug;
    if (slug.isEmpty) return;
    try {
      final productProvider = context.read<ProductProvider>();
      final full = await productProvider.fetchProductBySlug(slug);
      if (full != null && mounted) {
        setState(() {
          _detailedProduct = full;
        });
      }
    } catch (_) {}
  }

  Future<void> _addToCart({bool checkout = false}) async {
    final productToUse = _detailedProduct ?? widget.product;
    if (checkout) {
      final authProvider = context.read<AuthProvider>();
      if (!authProvider.isAuthenticated) {
        AppSnackbar.info(context, 'Please sign in to complete your purchase');
        Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => const LoginScreen()),
        );
        return;
      }
    }

    final cartProvider = context.read<CartProvider>();
    await cartProvider.addToCart(productToUse, quantity: _quantity);
    CartStore.instance.add(productToUse, quantity: _quantity);

    if (!mounted) return;

    if (checkout) {
      Navigator.of(context).push(MaterialPageRoute(builder: (_) => const CartScreen()));
      return;
    }
    AppSnackbar.showSuccess(
      context,
      '${productToUse.title} added to cart',
    );
  }

  @override
  Widget build(BuildContext context) {
    final product = _detailedProduct ?? widget.product;
    final seller = product.seller ?? MockData.sellerFor(product.sellerId);
    final gallery = product.gallery.isEmpty ? (product.imageUrl.isNotEmpty ? [product.imageUrl] : <String>[]) : product.gallery;
    final similar = MockData.products.where((p) => p.categoryId == product.categoryId && p.id != product.id).toList();

    final shortDesc = product.cleanShortDescription;

    final List<_ProductTabItem> activeTabs = [];

    if (product.specs.isNotEmpty) {
      activeTabs.add(_ProductTabItem(
        title: 'Specifications',
        content: _SpecsTab(product: product),
      ));
    }

    if (product.description.trim().isNotEmpty && product.cleanDescription != 'No description provided.') {
      activeTabs.add(_ProductTabItem(
        title: 'Description',
        content: _DescriptionTab(product: product),
      ));
    }

    if (product.reviews.isNotEmpty || product.reviewCount > 0) {
      activeTabs.add(_ProductTabItem(
        title: 'Reviews',
        content: _ReviewsTab(product: product),
      ));
    }

    if (gallery.isNotEmpty) {
      activeTabs.add(_ProductTabItem(
        title: 'Gallery',
        content: _GalleryTab(images: gallery),
      ));
    }

    return Scaffold(
      body: SafeArea(
        child: Column(
          children: [
            Expanded(
              child: SingleChildScrollView(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _Gallery(
                      images: gallery,
                      index: _galleryIndex,
                      onChanged: (i) => setState(() => _galleryIndex = i),
                      product: product,
                    ),
                    Padding(
                      padding: const EdgeInsets.all(AppSpacing.lg),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Wrap(
                            spacing: 6,
                            runSpacing: 6,
                            children: [
                              if (product.grade != null) GradeBadge(grade: product.grade!),
                              if (product.isVerified) const VerifiedBadge(),
                              if (product.isWarehoused) const WarehouseBadge(),
                              if (product.isNew) const NewBadge(),
                            ],
                          ),
                          const SizedBox(height: AppSpacing.sm),
                          Text(product.title, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: AppColors.ink, height: 1.3)),
                          const SizedBox(height: AppSpacing.sm),
                          RatingStars(rating: product.rating, reviewCount: product.reviewCount, size: 14),
                          const SizedBox(height: AppSpacing.md),
                          Row(
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              Text(formatPrice(product.price), style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: AppColors.ink)),
                              if (product.compareAtPrice != null) ...[
                                const SizedBox(width: AppSpacing.sm),
                                Padding(
                                  padding: const EdgeInsets.only(bottom: 4),
                                  child: Text(
                                    formatPrice(product.compareAtPrice!),
                                    style: const TextStyle(fontSize: 14, color: AppColors.slate400, decoration: TextDecoration.lineThrough),
                                  ),
                                ),
                              ],
                              if (product.discountPercent != null) ...[
                                const SizedBox(width: AppSpacing.sm),
                                Padding(padding: const EdgeInsets.only(bottom: 4), child: DiscountBadge(percent: product.discountPercent!)),
                              ],
                            ],
                          ),
                          const SizedBox(height: AppSpacing.md),
                          Wrap(
                            spacing: 8,
                            runSpacing: 8,
                            children: [
                              _InfoChip(label: 'Price', value: formatPrice(product.price)),
                              if (product.compareAtPrice != null)
                                _InfoChip(label: 'Regular Price', value: formatPrice(product.compareAtPrice!)),
                              _InfoChip(
                                label: 'Status',
                                value: product.stock > 0 ? 'In Stock' : 'Out of Stock',
                                valueColor: product.stock > 0 ? const Color(0xFF059669) : AppColors.danger,
                              ),
                              if (product.sku.isNotEmpty)
                                _InfoChip(label: 'Product Code', value: product.sku),
                              if (product.brandName.isNotEmpty)
                                _InfoChip(label: 'Brand', value: product.brandName),
                            ],
                          ),
                          if (shortDesc.isNotEmpty) ...[
                            const SizedBox(height: AppSpacing.lg),
                            Row(
                              children: const [
                                Icon(Icons.short_text_rounded, size: 20, color: AppColors.brand700),
                                SizedBox(width: 6),
                                Text(
                                  'Short Description',
                                  style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: AppColors.ink),
                                ),
                              ],
                            ),
                            const SizedBox(height: AppSpacing.xs),
                            Container(
                              width: double.infinity,
                              padding: const EdgeInsets.all(AppSpacing.md),
                              decoration: BoxDecoration(
                                color: AppColors.slate50,
                                border: Border.all(color: AppColors.slate200),
                                borderRadius: BorderRadius.circular(AppRadius.md),
                              ),
                              child: Text(
                                shortDesc,
                                style: const TextStyle(fontSize: 13, color: AppColors.slate700, height: 1.55),
                              ),
                            ),
                          ],
                          const SizedBox(height: AppSpacing.lg),
                          Row(
                            children: [
                              const Text('Quantity', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.slate700)),
                              const Spacer(),
                              _QuantityStepper(
                                quantity: _quantity,
                                max: product.stock > 0 ? product.stock : 99,
                                onChanged: (q) => setState(() => _quantity = q),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    const Divider(height: 1),
                    Padding(
                      padding: const EdgeInsets.all(AppSpacing.lg),
                      child: _SellerCard(seller: seller),
                    ),
                    const Divider(height: 1),
                    _TrustRow(),
                    const Divider(height: 1),
                    if (activeTabs.isNotEmpty) ...[
                      DefaultTabController(
                        key: ValueKey('${product.id}_${activeTabs.length}'),
                        length: activeTabs.length,
                        child: Column(
                          children: [
                            TabBar(
                              labelColor: AppColors.brand700,
                              unselectedLabelColor: AppColors.slate500,
                              indicatorColor: AppColors.brand600,
                              labelStyle: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700),
                              tabs: activeTabs.map((tab) => Tab(text: tab.title)).toList(),
                            ),
                            SizedBox(
                              height: 260,
                              child: TabBarView(
                                children: activeTabs.map((tab) => tab.content).toList(),
                              ),
                            ),
                          ],
                        ),
                      ),
                      const Divider(height: 1),
                    ],
                    const Divider(height: 1),
                    if (similar.isNotEmpty) ...[
                      const Padding(
                        padding: EdgeInsets.fromLTRB(AppSpacing.lg, AppSpacing.xl, AppSpacing.lg, AppSpacing.md),
                        child: Text('Similar products', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.ink)),
                      ),
                      SizedBox(
                        height: 260,
                        child: ListView.separated(
                          scrollDirection: Axis.horizontal,
                          padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg),
                          itemCount: similar.length,
                          separatorBuilder: (_, _) => const SizedBox(width: AppSpacing.md),
                          itemBuilder: (context, i) => SizedBox(
                            width: 160,
                            child: ProductCard(
                              product: similar[i],
                              onTap: () => Navigator.of(context).pushReplacement(
                                MaterialPageRoute(builder: (_) => ProductDetailScreen(product: similar[i])),
                              ),
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: AppSpacing.xxl),
                    ],
                  ],
                ),
              ),
            ),
            _BottomActionBar(onAddToCart: () => _addToCart(), onBuyNow: () => _addToCart(checkout: true)),
          ],
        ),
      ),
    );
  }
}

class _Gallery extends StatefulWidget {
  const _Gallery({
    required this.images,
    required this.index,
    required this.onChanged,
    required this.product,
  });

  final List<String> images;
  final int index;
  final ValueChanged<int> onChanged;
  final Product product;

  @override
  State<_Gallery> createState() => _GalleryState();
}

class _GalleryState extends State<_Gallery> {
  late PageController _pageController;

  @override
  void initState() {
    super.initState();
    _pageController = PageController(initialPage: widget.index);
  }

  @override
  void didUpdateWidget(covariant _Gallery oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.index != oldWidget.index && _pageController.hasClients && _pageController.page?.round() != widget.index) {
      _pageController.animateToPage(
        widget.index,
        duration: const Duration(milliseconds: 250),
        curve: Curves.easeInOut,
      );
    }
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  void _share(BuildContext context, Rect? origin) {
    final link = '${ApiConfig.backendHost}/products/${widget.product.slug}';
    SharePlus.instance.share(
      ShareParams(
        subject: widget.product.title,
        text: '${widget.product.title}\n${formatPrice(widget.product.price)}\n$link',
        sharePositionOrigin: origin,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final images = widget.images;

    return Column(
      children: [
        SizedBox(
          width: double.infinity,
          child: AspectRatio(
            aspectRatio: 1.0,
            child: Stack(
              fit: StackFit.expand,
              children: [
                Container(
                  color: AppColors.slate50,
                  child: images.isEmpty
                      ? const Center(child: Icon(Icons.image_outlined, size: 48, color: AppColors.slate400))
                      : PageView.builder(
                          controller: _pageController,
                          itemCount: images.length,
                          onPageChanged: widget.onChanged,
                          itemBuilder: (context, i) => Image.network(
                            images[i],
                            fit: BoxFit.cover,
                            width: double.infinity,
                            height: double.infinity,
                            errorBuilder: (_, _, _) => Container(
                              color: AppColors.slate100,
                              child: const Icon(Icons.image_outlined, size: 48, color: AppColors.slate400),
                            ),
                          ),
                        ),
                ),
                Positioned(
                  top: AppSpacing.sm,
                  left: AppSpacing.sm,
                  child: _CircleIconButton(
                    icon: Icons.arrow_back_rounded,
                    onTap: () => Navigator.of(context).pop(),
                  ),
                ),
                Positioned(
                  top: AppSpacing.sm,
                  right: AppSpacing.sm,
                  child: Row(
                    children: [
                      AnimatedBuilder(
                        animation: WishlistStore.instance,
                        builder: (context, _) {
                          final saved = WishlistStore.instance.contains(widget.product.id);
                          return _CircleIconButton(
                            icon: saved ? Icons.favorite_rounded : Icons.favorite_border_rounded,
                            iconColor: saved ? AppColors.danger : AppColors.ink,
                            onTap: () {
                              WishlistStore.instance.toggle(widget.product.id);
                              final nowSaved = WishlistStore.instance.contains(widget.product.id);
                              AppSnackbar.show(
                                context,
                                nowSaved ? 'Added to wishlist' : 'Removed from wishlist',
                                icon: nowSaved ? Icons.favorite_rounded : Icons.favorite_border_rounded,
                              );
                            },
                          );
                        },
                      ),
                      const SizedBox(width: AppSpacing.sm),
                      Builder(
                        builder: (context) => _CircleIconButton(
                          icon: Icons.share_rounded,
                          onTap: () {
                            final box = context.findRenderObject() as RenderBox?;
                            final origin = box == null ? null : box.localToGlobal(Offset.zero) & box.size;
                            _share(context, origin);
                          },
                        ),
                      ),
                    ],
                  ),
                ),
                if (images.length > 1)
                  Positioned(
                    bottom: AppSpacing.md,
                    right: AppSpacing.md,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                      decoration: BoxDecoration(
                        color: Colors.black.withValues(alpha: 0.6),
                        borderRadius: BorderRadius.circular(AppRadius.pill),
                      ),
                      child: Text(
                        '${widget.index + 1} / ${images.length}',
                        style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w600),
                      ),
                    ),
                  ),
              ],
            ),
          ),
        ),
        if (images.length > 1)
          SizedBox(
            height: 64,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.sm),
              itemCount: images.length,
              separatorBuilder: (_, _) => const SizedBox(width: AppSpacing.sm),
              itemBuilder: (context, i) => GestureDetector(
                onTap: () => widget.onChanged(i),
                child: Container(
                  width: 48,
                  height: 48,
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(AppRadius.sm),
                    border: Border.all(color: i == widget.index ? AppColors.brand500 : AppColors.slate200, width: i == widget.index ? 2 : 1),
                  ),
                  clipBehavior: Clip.antiAlias,
                  child: Image.network(
                    images[i],
                    fit: BoxFit.cover,
                    errorBuilder: (_, _, _) => Container(
                      color: AppColors.slate100,
                      child: const Icon(Icons.image_outlined, size: 20, color: AppColors.slate400),
                    ),
                  ),
                ),
              ),
            ),
          ),
      ],
    );
  }
}

class _CircleIconButton extends StatelessWidget {
  const _CircleIconButton({required this.icon, required this.onTap, this.iconColor});

  final IconData icon;
  final VoidCallback onTap;
  final Color? iconColor;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white.withValues(alpha: 0.92),
      shape: const CircleBorder(),
      elevation: 2,
      shadowColor: Colors.black.withValues(alpha: 0.2),
      child: InkWell(
        customBorder: const CircleBorder(),
        onTap: onTap,
        child: SizedBox(
          width: 36,
          height: 36,
          child: Icon(icon, size: 18, color: iconColor ?? AppColors.ink),
        ),
      ),
    );
  }
}

class _QuantityStepper extends StatelessWidget {
  const _QuantityStepper({required this.quantity, required this.max, required this.onChanged});

  final int quantity;
  final int max;
  final ValueChanged<int> onChanged;

  @override
  Widget build(BuildContext context) {
    final effectiveMax = max > 1 ? max : 99;
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border.all(color: AppColors.slate300),
        borderRadius: BorderRadius.circular(AppRadius.md),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          IconButton(
            icon: const Icon(Icons.remove_rounded, size: 18),
            color: quantity > 1 ? AppColors.ink : AppColors.slate300,
            onPressed: quantity > 1 ? () => onChanged(quantity - 1) : null,
            constraints: const BoxConstraints(minWidth: 40, minHeight: 40),
            padding: EdgeInsets.zero,
          ),
          Container(
            width: 36,
            alignment: Alignment.center,
            child: Text(
              '$quantity',
              style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15, color: AppColors.ink),
            ),
          ),
          IconButton(
            icon: const Icon(Icons.add_rounded, size: 18),
            color: quantity < effectiveMax ? AppColors.ink : AppColors.slate300,
            onPressed: quantity < effectiveMax ? () => onChanged(quantity + 1) : null,
            constraints: const BoxConstraints(minWidth: 40, minHeight: 40),
            padding: EdgeInsets.zero,
          ),
        ],
      ),
    );
  }
}

class _SellerCard extends StatelessWidget {
  const _SellerCard({required this.seller});
  final Seller seller;

  @override
  Widget build(BuildContext context) {
    final avatar = seller.avatarUrl.isNotEmpty
        ? seller.avatarUrl
        : (seller.name.toLowerCase().contains('admin') ? ApiConfig.appIconUrl : '');



    return Row(
      children: [
        ClipOval(
          child: Container(
            width: 44,
            height: 44,
            color: AppColors.brand100,
            child: avatar.isNotEmpty
                ? Image.network(
                    avatar,
                    fit: BoxFit.cover,
                    errorBuilder: (_, _, _) => Center(
                      child: Text(
                        seller.avatarInitial,
                        style: const TextStyle(color: AppColors.brand700, fontWeight: FontWeight.w700, fontSize: 15),
                      ),
                    ),
                  )
                : Center(
                    child: Text(
                      seller.avatarInitial,
                      style: const TextStyle(color: AppColors.brand700, fontWeight: FontWeight.w700, fontSize: 15),
                    ),
                  ),
          ),
        ),
        const SizedBox(width: AppSpacing.md),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Flexible(child: Text(seller.name, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14), overflow: TextOverflow.ellipsis)),
                  if (seller.isVerified) ...[
                    const SizedBox(width: 4),
                    const Icon(Icons.verified_rounded, size: 14, color: AppColors.verified),
                  ],
                ],
              ),
              const SizedBox(height: 2),
              Row(
                children: [
                  RatingStars(rating: seller.rating, size: 12),
                  const SizedBox(width: 6),
                  Text('· ${seller.city}', style: const TextStyle(fontSize: 12, color: AppColors.slate500)),
                ],
              ),
            ],
          ),
        ),
        OutlinedButton(
          onPressed: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => StoreScreen(seller: seller))),
          style: OutlinedButton.styleFrom(minimumSize: const Size(0, 36), padding: const EdgeInsets.symmetric(horizontal: AppSpacing.md)),
          child: const Text('Visit store', style: TextStyle(fontSize: 12)),
        ),
        const SizedBox(width: AppSpacing.sm),
        _MessageSellerButton(
          onPressed: () => Navigator.of(context).push(
            MaterialPageRoute(
              builder: (_) => ChatConversationScreen(
                conversation: ChatConversation(
                  id: 'c_${seller.id}',
                  sellerName: seller.name,
                  avatarInitial: seller.avatarInitial,
                  lastMessage: '',
                  time: 'Now',
                  messages: const [],
                ),
              ),
            ),
          ),
        ),
      ],
    );
  }
}

class _MessageSellerButton extends StatelessWidget {
  const _MessageSellerButton({required this.onPressed});
  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      shape: const CircleBorder(),
      child: Ink(
        width: 40,
        height: 40,
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          gradient: const LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [AppColors.brand400, AppColors.brand700],
          ),
          boxShadow: [
            BoxShadow(color: AppColors.brand500.withValues(alpha: 0.35), blurRadius: 10, offset: const Offset(0, 4)),
          ],
        ),
        child: InkWell(
          customBorder: const CircleBorder(),
          onTap: onPressed,
          child: const Icon(Icons.chat_bubble_rounded, size: 19, color: Colors.white),
        ),
      ),
    );
  }
}

class _TrustRow extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.md),
      child: Row(
        children: const [
          Expanded(child: _TrustItem(icon: Icons.shield_outlined, label: 'Buyer Protection')),
          Expanded(child: _TrustItem(icon: Icons.lock_outline_rounded, label: 'Secure Payment')),
          Expanded(child: _TrustItem(icon: Icons.local_shipping_outlined, label: 'Fast Shipping')),
        ],
      ),
    );
  }
}

class _TrustItem extends StatelessWidget {
  const _TrustItem({required this.icon, required this.label});
  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Icon(icon, size: 20, color: AppColors.slate500),
        const SizedBox(height: 4),
        Text(label, textAlign: TextAlign.center, style: const TextStyle(fontSize: 10, color: AppColors.slate500)),
      ],
    );
  }
}

class _SpecsTab extends StatelessWidget {
  const _SpecsTab({required this.product});
  final Product product;

  @override
  Widget build(BuildContext context) {
    if (product.specs.isEmpty) {
      return const Center(child: Text('No specifications available', style: TextStyle(color: AppColors.slate400, fontSize: 13)));
    }
    return ListView.separated(
      padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.md),
      itemCount: product.specs.length,
      separatorBuilder: (_, _) => const Divider(height: AppSpacing.xl),
      itemBuilder: (context, i) {
        final entry = product.specs.entries.elementAt(i);
        return Row(
          children: [
            Expanded(child: Text(entry.key, style: const TextStyle(fontSize: 13, color: AppColors.slate500))),
            Expanded(child: Text(entry.value, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.ink))),
          ],
        );
      },
    );
  }
}

class _DescriptionTab extends StatelessWidget {
  const _DescriptionTab({required this.product});
  final Product product;

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppSpacing.lg),
      child: Text(
        product.cleanDescription,
        style: const TextStyle(fontSize: 13, color: AppColors.slate700, height: 1.6),
      ),
    );
  }
}

class _ReviewsTab extends StatelessWidget {
  const _ReviewsTab({required this.product});
  final Product product;

  @override
  Widget build(BuildContext context) {
    final reviews = product.reviews;
    if (reviews.isEmpty) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(AppSpacing.lg),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.rate_review_outlined, size: 40, color: AppColors.slate300),
              SizedBox(height: AppSpacing.sm),
              Text('No reviews yet for this product', style: TextStyle(color: AppColors.slate500, fontSize: 13)),
            ],
          ),
        ),
      );
    }

    return ListView.separated(
      padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.md),
      itemCount: reviews.length,
      separatorBuilder: (_, _) => const Divider(height: AppSpacing.xl),
      itemBuilder: (context, i) {
        final review = reviews[i];
        return Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            CircleAvatar(
              radius: 16,
              backgroundColor: AppColors.brand100,
              child: Text(
                review.avatarInitial,
                style: const TextStyle(fontSize: 12, color: AppColors.brand700, fontWeight: FontWeight.bold),
              ),
            ),
            const SizedBox(width: AppSpacing.sm),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(review.author, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                      if (review.date.isNotEmpty)
                        Text(review.date, style: const TextStyle(fontSize: 11, color: AppColors.slate400)),
                    ],
                  ),
                  const SizedBox(height: 2),
                  RatingStars(rating: review.rating, size: 11),
                  if (review.title.isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Text(review.title, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.ink)),
                  ],
                  if (review.comment.isNotEmpty) ...[
                    const SizedBox(height: 2),
                    Text(review.comment, style: const TextStyle(fontSize: 12, color: AppColors.slate700, height: 1.4)),
                  ],
                ],
              ),
            ),
          ],
        );
      },
    );
  }
}

class _GalleryTab extends StatelessWidget {
  const _GalleryTab({required this.images});
  final List<String> images;

  @override
  Widget build(BuildContext context) {
    if (images.isEmpty) {
      return const Center(child: Text('No gallery images available', style: TextStyle(color: AppColors.slate400, fontSize: 13)));
    }
    return GridView.builder(
      padding: const EdgeInsets.all(AppSpacing.lg),
      itemCount: images.length,
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 3,
        crossAxisSpacing: AppSpacing.sm,
        mainAxisSpacing: AppSpacing.sm,
      ),
      itemBuilder: (context, i) => GestureDetector(
        onTap: () => Navigator.of(context).push(
          MaterialPageRoute(
            builder: (_) => _FullScreenGalleryViewer(images: images, initialIndex: i),
            fullscreenDialog: true,
          ),
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(AppRadius.sm),
          child: Container(
            color: AppColors.slate50,
            child: Image.network(
              images[i],
              fit: BoxFit.cover,
              errorBuilder: (_, _, _) => Container(
                color: AppColors.slate100,
                child: const Icon(Icons.image_outlined, size: 24, color: AppColors.slate400),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _FullScreenGalleryViewer extends StatefulWidget {
  const _FullScreenGalleryViewer({required this.images, required this.initialIndex});
  final List<String> images;
  final int initialIndex;

  @override
  State<_FullScreenGalleryViewer> createState() => _FullScreenGalleryViewerState();
}

class _FullScreenGalleryViewerState extends State<_FullScreenGalleryViewer> {
  late final PageController _pageController;
  late int _index;

  @override
  void initState() {
    super.initState();
    _index = widget.initialIndex;
    _pageController = PageController(initialPage: widget.initialIndex);
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: SafeArea(
        child: Stack(
          children: [
            PageView.builder(
              controller: _pageController,
              itemCount: widget.images.length,
              onPageChanged: (i) => setState(() => _index = i),
              itemBuilder: (context, i) => InteractiveViewer(
                minScale: 1,
                maxScale: 4,
                child: Center(
                  child: Image.network(
                    widget.images[i],
                    fit: BoxFit.contain,
                    errorBuilder: (_, _, _) => const Icon(Icons.image_outlined, size: 64, color: AppColors.slate500),
                  ),
                ),
              ),
            ),
            Positioned(
              top: AppSpacing.sm,
              left: AppSpacing.sm,
              child: _CircleIconButton(
                icon: Icons.close_rounded,
                onTap: () => Navigator.of(context).pop(),
              ),
            ),
            if (widget.images.length > 1)
              Positioned(
                top: AppSpacing.md,
                right: AppSpacing.lg,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  decoration: BoxDecoration(
                    color: Colors.black.withValues(alpha: 0.5),
                    borderRadius: BorderRadius.circular(AppRadius.pill),
                  ),
                  child: Text(
                    '${_index + 1} / ${widget.images.length}',
                    style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}

class _BottomActionBar extends StatelessWidget {
  const _BottomActionBar({required this.onAddToCart, required this.onBuyNow});
  final VoidCallback onAddToCart;
  final VoidCallback onBuyNow;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(AppSpacing.lg),
      decoration: const BoxDecoration(
        color: AppColors.surface,
        border: Border(top: BorderSide(color: AppColors.slate200)),
      ),
      child: Row(
        children: [
          Expanded(
            child: OutlinedButton.icon(
              onPressed: onAddToCart,
              icon: const Icon(Icons.add_shopping_cart_rounded, size: 18),
              label: const Text('Add to Cart'),
            ),
          ),
          const SizedBox(width: AppSpacing.md),
          Expanded(
            child: ElevatedButton(onPressed: onBuyNow, child: const Text('Buy Now')),
          ),
        ],
      ),
    );
  }
}

class _InfoChip extends StatelessWidget {
  const _InfoChip({
    required this.label,
    required this.value,
    this.valueColor,
  });

  final String label;
  final String value;
  final Color? valueColor;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border.all(color: AppColors.slate200),
        borderRadius: BorderRadius.circular(AppRadius.sm),
      ),
      child: RichText(
        text: TextSpan(
          style: const TextStyle(fontSize: 12, fontFamily: 'Montserrat'),
          children: [
            TextSpan(
              text: '$label: ',
              style: const TextStyle(color: AppColors.slate400, fontWeight: FontWeight.w500),
            ),
            TextSpan(
              text: value,
              style: TextStyle(
                color: valueColor ?? AppColors.ink,
                fontWeight: FontWeight.w700,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
