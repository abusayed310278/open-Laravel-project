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
                        if (product.isNew) const NewBadge(),
                        if (product.grade != null) GradeBadge(grade: product.grade!),
                        if (product.isWarehoused) const WarehouseBadge(),
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
                padding: const EdgeInsets.fromLTRB(AppSpacing.sm, AppSpacing.sm, AppSpacing.sm, AppSpacing.sm),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      product.title,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: AppColors.ink, height: 1.25),
                    ),
                    RatingStars(rating: product.rating, size: 12),
                    Row(
                      children: [
                        Text(
                          formatPrice(product.price),
                          style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: AppColors.ink),
                        ),
                        if (product.compareAtPrice != null) ...[
                          const SizedBox(width: AppSpacing.xs),
                          Flexible(
                            child: Text(
                              formatPrice(product.compareAtPrice!),
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                fontSize: 11,
                                color: AppColors.slate400,
                                decoration: TextDecoration.lineThrough,
                              ),
                            ),
                          ),
                        ],
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
