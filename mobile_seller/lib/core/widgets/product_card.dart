import 'package:flutter/material.dart';

import '../../data/models/product.dart';
import '../../data/mock/app_state.dart';
import '../theme/app_colors.dart';
import '../theme/app_spacing.dart';
import '../utils/formatters.dart';
import 'badges.dart';
import 'rating_stars.dart';

class ProductCard extends StatelessWidget {
  const ProductCard({super.key, required this.product, required this.onTap});

  final Product product;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(AppRadius.lg),
          border: Border.all(color: AppColors.slate200),
        ),
        clipBehavior: Clip.antiAlias,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            AspectRatio(
              aspectRatio: 1,
              child: Stack(
                children: [
                  Positioned.fill(
                    child: Image.network(
                      product.imageUrl,
                      fit: BoxFit.cover,
                      errorBuilder: (_, _, _) => Container(
                        color: AppColors.slate100,
                        child: const Icon(Icons.image_outlined, color: AppColors.slate400, size: 32),
                      ),
                    ),
                  ),
                  Positioned(
                    top: AppSpacing.sm,
                    left: AppSpacing.sm,
                    child: Wrap(
                      spacing: 4,
                      runSpacing: 4,
                      children: [
                        if (product.discountPercent != null) DiscountBadge(percent: product.discountPercent!),
                        if (product.grade != null && !product.isNewCondition) GradeBadge(grade: product.grade!),
                      ],
                    ),
                  ),
                  Positioned(
                    top: AppSpacing.xs,
                    right: AppSpacing.xs,
                    child: AnimatedBuilder(
                      animation: WishlistStore.instance,
                      builder: (context, _) {
                        final saved = WishlistStore.instance.contains(product.id);
                        return _CircleIconButton(
                          icon: saved ? Icons.favorite_rounded : Icons.favorite_border_rounded,
                          color: saved ? AppColors.danger : AppColors.slate500,
                          onTap: () => WishlistStore.instance.toggle(product.id),
                        );
                      },
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(AppSpacing.sm, 6, AppSpacing.sm, 6),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          crossAxisAlignment: CrossAxisAlignment.center,
                          children: [
                            if (product.effectiveBrandName.isNotEmpty)
                              Expanded(
                                child: Text(
                                  product.effectiveBrandName.toUpperCase(),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(
                                    fontSize: 9.5,
                                    fontWeight: FontWeight.w700,
                                    color: AppColors.slate400,
                                    letterSpacing: 0.4,
                                  ),
                                ),
                              )
                            else
                              const Spacer(),
                            const SizedBox(width: 6),
                            ConditionBadge(product: product, compact: true),
                          ],
                        ),
                        const SizedBox(height: 3),
                        Text(
                          product.title,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.ink, height: 1.25),
                        ),
                      ],
                    ),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        RatingStars(rating: product.rating, size: 11),
                        _StockBadge(isInStock: product.isInStock),
                      ],
                    ),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      crossAxisAlignment: CrossAxisAlignment.center,
                      children: [
                        Flexible(
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Text(
                                formatPrice(product.price),
                                style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800, color: AppColors.ink),
                              ),
                              if (product.compareAtPrice != null) ...[
                                const SizedBox(width: AppSpacing.xs),
                                Flexible(
                                  child: Text(
                                    formatPrice(product.compareAtPrice!),
                                    overflow: TextOverflow.ellipsis,
                                    style: const TextStyle(
                                      fontSize: 10.5,
                                      color: AppColors.slate400,
                                      decoration: TextDecoration.lineThrough,
                                    ),
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ),
                        if (product.sellerFirstWord.isNotEmpty)
                          Padding(
                            padding: const EdgeInsets.only(left: 4),
                            child: ConstrainedBox(
                              constraints: const BoxConstraints(maxWidth: 65),
                              child: Text(
                                product.sellerFirstWord,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  fontSize: 9.5,
                                  fontWeight: FontWeight.w600,
                                  color: AppColors.slate500,
                                ),
                              ),
                            ),
                          ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _StockBadge extends StatelessWidget {
  const _StockBadge({required this.isInStock});

  final bool isInStock;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
      decoration: BoxDecoration(
        color: isInStock ? const Color(0xFFECFDF5) : const Color(0xFFFEF2F2),
        borderRadius: BorderRadius.circular(4),
        border: Border.all(
          color: isInStock ? const Color(0xFFA7F3D0) : const Color(0xFFFECACA),
          width: 0.8,
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 4.5,
            height: 4.5,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: isInStock ? const Color(0xFF059669) : AppColors.danger,
            ),
          ),
          const SizedBox(width: 3),
          Text(
            isInStock ? 'In Stock' : 'Out of Stock',
            style: TextStyle(
              fontSize: 8.5,
              fontWeight: FontWeight.w700,
              color: isInStock ? const Color(0xFF059669) : AppColors.danger,
            ),
          ),
        ],
      ),
    );
  }
}

class _CircleIconButton extends StatelessWidget {
  const _CircleIconButton({required this.icon, required this.color, required this.onTap});

  final IconData icon;
  final Color color;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(6),
        decoration: const BoxDecoration(color: Colors.white, shape: BoxShape.circle, boxShadow: [
          BoxShadow(color: Color(0x1A000000), blurRadius: 4, offset: Offset(0, 1)),
        ]),
        child: Icon(icon, size: 16, color: color),
      ),
    );
  }
}
