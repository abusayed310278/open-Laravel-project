import 'package:flutter/material.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/product_card.dart';
import '../../data/mock/mock_data.dart';
import '../../data/models/chat_conversation.dart';
import '../../data/models/seller.dart';
import '../chat/chat_conversation_screen.dart';
import '../product/product_detail_screen.dart';

class StoreScreen extends StatelessWidget {
  const StoreScreen({super.key, required this.seller});

  final Seller seller;

  @override
  Widget build(BuildContext context) {
    final products = MockData.products.where((p) => p.sellerId == seller.id).toList();

    return Scaffold(
      body: CustomScrollView(
        slivers: [
          SliverAppBar(
            pinned: true,
            expandedHeight: 200,
            backgroundColor: AppColors.brand600,
            flexibleSpace: FlexibleSpaceBar(
              background: Container(
                decoration: const BoxDecoration(
                  gradient: LinearGradient(colors: [AppColors.brand600, AppColors.brand400], begin: Alignment.topLeft, end: Alignment.bottomRight),
                ),
                child: SafeArea(
                  child: Padding(
                    padding: const EdgeInsets.all(AppSpacing.lg),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.end,
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        CircleAvatar(
                          radius: 28,
                          backgroundColor: Colors.white,
                          child: Text(seller.avatarInitial, style: const TextStyle(color: AppColors.brand700, fontWeight: FontWeight.w800, fontSize: 18)),
                        ),
                        const SizedBox(height: AppSpacing.sm),
                        Row(
                          children: [
                            Text(seller.name, style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w800)),
                            if (seller.isVerified) ...[
                              const SizedBox(width: 6),
                              const Icon(Icons.verified_rounded, color: Colors.white, size: 18),
                            ],
                          ],
                        ),
                        Text('${seller.city} · Member since ${seller.memberSince}', style: const TextStyle(color: Colors.white70, fontSize: 12)),
                      ],
                    ),
                  ),
                ),
              ),
            ),
            actions: [
              IconButton(
                icon: const Icon(Icons.chat_bubble_outline_rounded, color: Colors.white),
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
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.all(AppSpacing.lg),
              child: Row(
                children: [
                  Expanded(child: _StoreStat(label: 'Rating', value: seller.rating.toStringAsFixed(1), icon: Icons.star_rounded)),
                  Expanded(child: _StoreStat(label: 'Reviews', value: '${seller.reviewCount}', icon: Icons.reviews_outlined)),
                  Expanded(child: _StoreStat(label: 'Products', value: '${seller.productCount}', icon: Icons.inventory_2_outlined)),
                ],
              ),
            ),
          ),
          const SliverToBoxAdapter(
            child: Padding(
              padding: EdgeInsets.symmetric(horizontal: AppSpacing.lg),
              child: Text('Products', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.ink)),
            ),
          ),
          const SliverToBoxAdapter(child: SizedBox(height: AppSpacing.md)),
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
                  product: products[i],
                  onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => ProductDetailScreen(product: products[i]))),
                ),
                childCount: products.length,
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
