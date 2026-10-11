import 'package:flutter/material.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_spacing.dart';
import '../../messages/messages_screen.dart';
import 'support_tickets_screen.dart';

class HelpCenterScreen extends StatefulWidget {
  const HelpCenterScreen({super.key});

  @override
  State<HelpCenterScreen> createState() => _HelpCenterScreenState();
}

class _HelpCenterScreenState extends State<HelpCenterScreen> {
  final _searchController = TextEditingController();
  String _searchQuery = '';

  static const List<Map<String, dynamic>> _categories = [
    {
      'icon': Icons.storefront_outlined,
      'title': 'Store & KYC',
      'subtitle': 'Profile, verification & document submission',
    },
    {
      'icon': Icons.inventory_2_outlined,
      'title': 'Listings & Stock',
      'subtitle': 'Adding items, specs, pricing & approval',
    },
    {
      'icon': Icons.local_shipping_outlined,
      'title': 'Orders & Shipping',
      'subtitle': 'Fulfillment SLAs, packaging & delivery',
    },
    {
      'icon': Icons.account_balance_wallet_outlined,
      'title': 'Payouts & Wallet',
      'subtitle': 'Settlements, bank accounts & commissions',
    },
  ];

  static const List<Map<String, String>> _faqs = [
    {
      'question': 'How long does product listing review take?',
      'answer':
          'Most listings are inspected and approved by our catalog team within 2 to 6 business hours. High-value electronics requiring physical hub inspection may take up to 24 hours.',
    },
    {
      'question': 'When are sales funds released to my wallet?',
      'answer':
          'Funds are credited to your seller wallet as pending immediately upon order payment. Once the customer receives the item and the 48-hour buyer inspection window passes, the funds become fully withdrawable.',
    },
    {
      'question': 'What are the required documents for Merchant Verification (KYC)?',
      'answer':
          'You need a valid Qatar ID (QID) or passport for individual sellers, or a Commercial Registration (CR) and Computer Card for registered corporate merchants.',
    },
    {
      'question': 'Can I offer both Free Shipping and Flat Rate delivery?',
      'answer':
          'Yes! You can configure default shipping per listing or in your Store Settings. Flat rate allows specifying a fixed fee (e.g. QAR 25), or you can choose Free Shipping to maximize buyer conversions.',
    },
    {
      'question': 'How do I handle returns or buyer disputes?',
      'answer':
          'If a customer files a return request, you will receive an alert in Messages & Support Tickets. Our platform escrow protects honest merchants against fraudulent return claims.',
    },
  ];

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final filteredFaqs = _searchQuery.isEmpty
        ? _faqs
        : _faqs.where((f) {
            final q = f['question']!.toLowerCase();
            final a = f['answer']!.toLowerCase();
            return q.contains(_searchQuery.toLowerCase()) || a.contains(_searchQuery.toLowerCase());
          }).toList();

    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        title: const Text('Help Center'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppSpacing.lg),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Search Bar
            TextField(
              controller: _searchController,
              onChanged: (val) => setState(() => _searchQuery = val.trim()),
              decoration: InputDecoration(
                hintText: 'Search help articles, policies, FAQs...',
                prefixIcon: const Icon(Icons.search, size: 20, color: AppColors.slate400),
                suffixIcon: _searchQuery.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear, size: 18, color: AppColors.slate400),
                        onPressed: () {
                          _searchController.clear();
                          setState(() => _searchQuery = '');
                        },
                      )
                    : null,
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                fillColor: AppColors.surface,
              ),
            ),
            const SizedBox(height: AppSpacing.lg),

            // Topics Grid
            const Text(
              'Browse Topics',
              style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: AppColors.ink),
            ),
            const SizedBox(height: AppSpacing.sm),

            GridView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: _categories.length,
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 2,
                crossAxisSpacing: 10,
                mainAxisSpacing: 10,
                childAspectRatio: 1.35,
              ),
              itemBuilder: (context, index) {
                final cat = _categories[index];
                return Container(
                  padding: const EdgeInsets.all(AppSpacing.md),
                  decoration: BoxDecoration(
                    color: AppColors.surface,
                    borderRadius: BorderRadius.circular(AppRadius.lg),
                    border: Border.all(color: AppColors.slate200),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Container(
                        padding: const EdgeInsets.all(6),
                        decoration: BoxDecoration(
                          color: AppColors.brand50,
                          borderRadius: BorderRadius.circular(AppRadius.sm),
                        ),
                        child: Icon(cat['icon'] as IconData, size: 20, color: AppColors.brand600),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        cat['title'] as String,
                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.ink),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        cat['subtitle'] as String,
                        style: const TextStyle(fontSize: 10, color: AppColors.slate500),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                );
              },
            ),
            const SizedBox(height: AppSpacing.xl),

            // Frequently Asked Questions
            const Text(
              'Frequently Asked Questions',
              style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: AppColors.ink),
            ),
            const SizedBox(height: AppSpacing.sm),

            Container(
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(AppRadius.lg),
                border: Border.all(color: AppColors.slate200),
              ),
              child: filteredFaqs.isEmpty
                  ? const Padding(
                      padding: EdgeInsets.all(AppSpacing.xl),
                      child: Center(
                        child: Text(
                          'No articles matched your search query.',
                          style: TextStyle(fontSize: 13, color: AppColors.slate400),
                        ),
                      ),
                    )
                  : Column(
                      children: filteredFaqs.asMap().entries.map((entry) {
                        final idx = entry.key;
                        final item = entry.value;
                        final isLast = idx == filteredFaqs.length - 1;

                        return Column(
                          children: [
                            ExpansionTile(
                              tilePadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                              title: Text(
                                item['question']!,
                                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.ink),
                              ),
                              children: [
                                Padding(
                                  padding: const EdgeInsets.only(left: 16, right: 16, bottom: 16),
                                  child: Text(
                                    item['answer']!,
                                    style: const TextStyle(fontSize: 12, color: AppColors.slate600, height: 1.45),
                                  ),
                                ),
                              ],
                            ),
                            if (!isLast) const Divider(height: 1, color: AppColors.slate100),
                          ],
                        );
                      }).toList(),
                    ),
            ),
            const SizedBox(height: AppSpacing.xl),

            // Still need help banner
            Container(
              padding: const EdgeInsets.all(AppSpacing.lg),
              decoration: BoxDecoration(
                color: AppColors.slate900,
                borderRadius: BorderRadius.circular(AppRadius.lg),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Still need assistance?',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: Colors.white),
                  ),
                  const SizedBox(height: 4),
                  const Text(
                    'Our merchant partner support team is available 24/7 to help resolve questions.',
                    style: TextStyle(fontSize: 12, color: AppColors.slate300),
                  ),
                  const SizedBox(height: AppSpacing.md),
                  Row(
                    children: [
                      Expanded(
                        child: ElevatedButton.icon(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.brand500,
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 12),
                          ),
                          icon: const Icon(Icons.confirmation_number_outlined, size: 18),
                          label: const Text('Open Ticket', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                          onPressed: () {
                            Navigator.of(context).push(
                              MaterialPageRoute(builder: (_) => const SupportTicketsScreen()),
                            );
                          },
                        ),
                      ),
                      const SizedBox(width: AppSpacing.md),
                      Expanded(
                        child: OutlinedButton.icon(
                          style: OutlinedButton.styleFrom(
                            foregroundColor: Colors.white,
                            side: const BorderSide(color: AppColors.slate700),
                            padding: const EdgeInsets.symmetric(vertical: 12),
                          ),
                          icon: const Icon(Icons.chat_bubble_outline_rounded, size: 18),
                          label: const Text('Live Chat', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                          onPressed: () {
                            Navigator.of(context).push(
                              MaterialPageRoute(builder: (_) => const MessagesScreen()),
                            );
                          },
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppSpacing.xxl),
          ],
        ),
      ),
    );
  }
}
