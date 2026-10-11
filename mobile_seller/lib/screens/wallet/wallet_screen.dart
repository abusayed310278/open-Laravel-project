import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/custom_button.dart';
import '../../core/widgets/custom_text_field.dart';
import '../../core/widgets/loading_view.dart';
import '../../core/widgets/status_badge.dart';
import '../../providers/seller_wallet_provider.dart';

class WalletScreen extends StatefulWidget {
  const WalletScreen({super.key});

  @override
  State<WalletScreen> createState() => _WalletScreenState();
}

class _WalletScreenState extends State<WalletScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final _currencyFormat = NumberFormat('#,##0.00', 'en_US');
  final _amountController = TextEditingController();
  String _selectedMethod = 'manual_bank';

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<SellerWalletProvider>().fetchWallet();
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    _amountController.dispose();
    super.dispose();
  }

  void _showPayoutSheet(BuildContext context, double availableBalance) {
    _amountController.clear();
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => Padding(
        padding: EdgeInsets.only(bottom: MediaQuery.of(ctx).viewInsets.bottom),
        child: Container(
          padding: const EdgeInsets.all(AppSpacing.xxl),
          decoration: const BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.vertical(top: Radius.circular(AppRadius.xl)),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Request Payout',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.ink),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close),
                    onPressed: () => Navigator.of(ctx).pop(),
                  ),
                ],
              ),
              const SizedBox(height: AppSpacing.sm),
              Container(
                padding: const EdgeInsets.all(AppSpacing.md),
                decoration: BoxDecoration(
                  color: AppColors.brand50,
                  borderRadius: BorderRadius.circular(AppRadius.md),
                  border: Border.all(color: AppColors.brand200),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.account_balance_wallet_outlined, size: 20, color: AppColors.brand600),
                    const SizedBox(width: 8),
                    Text(
                      'Available for withdrawal: ${_currencyFormat.format(availableBalance)} QAR',
                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.brand800),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppSpacing.lg),
              CustomTextField(
                controller: _amountController,
                label: 'Withdrawal Amount (QAR)',
                hint: 'e.g. 500.00',
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
              ),
              const SizedBox(height: AppSpacing.md),
              const Text('Payout Method', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.slate700)),
              const SizedBox(height: 6),
              DropdownButtonFormField<String>(
                value: _selectedMethod,
                items: const [
                  DropdownMenuItem(value: 'manual_bank', child: Text('Bank Transfer (IBAN)')),
                  DropdownMenuItem(value: 'stripe', child: Text('Stripe Connect')),
                  DropdownMenuItem(value: 'paypal', child: Text('PayPal')),
                ],
                onChanged: (val) => setState(() => _selectedMethod = val ?? 'manual_bank'),
                decoration: const InputDecoration(contentPadding: EdgeInsets.symmetric(horizontal: 14, vertical: 12)),
              ),
              const SizedBox(height: AppSpacing.xl),
              Consumer<SellerWalletProvider>(
                builder: (_, walletProvider, __) {
                  return CustomButton(
                    text: 'Submit Payout Request',
                    isLoading: walletProvider.isSubmittingPayout,
                    onPressed: () async {
                      final amount = double.tryParse(_amountController.text.trim()) ?? 0.0;
                      if (amount <= 0) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Please enter a valid amount'), backgroundColor: AppColors.danger),
                        );
                        return;
                      }
                      if (amount > availableBalance) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Amount exceeds available balance'), backgroundColor: AppColors.danger),
                        );
                        return;
                      }

                      final success = await walletProvider.requestPayout(amount, _selectedMethod);
                      if (ctx.mounted) Navigator.of(ctx).pop();

                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(success ? 'Payout request submitted!' : (walletProvider.errorMessage ?? 'Submission failed')),
                            backgroundColor: success ? AppColors.success : AppColors.danger,
                          ),
                        );
                      }
                    },
                  );
                },
              ),
              const SizedBox(height: AppSpacing.md),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final walletProvider = context.watch<SellerWalletProvider>();
    final balance = walletProvider.balance;

    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        title: const Text('Earnings & Wallet'),
      ),
      body: RefreshIndicator(
        onRefresh: () => walletProvider.fetchWallet(),
        color: AppColors.brand500,
        child: walletProvider.isLoading && balance == null
            ? const LoadingView(message: 'Loading wallet balance...')
            : Column(
                children: [
                  // 1. Balance Summary Hero Card
                  Padding(
                    padding: const EdgeInsets.all(AppSpacing.lg),
                    child: Container(
                      padding: const EdgeInsets.all(AppSpacing.xl),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [Color(0xFF1E293B), Color(0xFF0F172A)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.circular(AppRadius.xl),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.12),
                            blurRadius: 16,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Text(
                                'Available Balance',
                                style: TextStyle(color: Colors.white70, fontSize: 13, fontWeight: FontWeight.w500),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                decoration: BoxDecoration(
                                  color: Colors.white.withOpacity(0.12),
                                  borderRadius: BorderRadius.circular(AppRadius.pill),
                                ),
                                child: const Text(
                                  'QAR',
                                  style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          Text(
                            '${_currencyFormat.format(balance?.availableBalance ?? 0.0)} QAR',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 28,
                              fontWeight: FontWeight.w800,
                              letterSpacing: -0.5,
                            ),
                          ),
                          const SizedBox(height: AppSpacing.lg),
                          const Divider(height: 1, color: Colors.white12),
                          const SizedBox(height: AppSpacing.md),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text('Pending Earnings', style: TextStyle(color: Colors.white60, fontSize: 11)),
                                  const SizedBox(height: 2),
                                  Text(
                                    '${_currencyFormat.format(balance?.pendingBalance ?? 0.0)} QAR',
                                    style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.w700),
                                  ),
                                ],
                              ),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.end,
                                children: [
                                  const Text('Total Earned', style: TextStyle(color: Colors.white60, fontSize: 11)),
                                  const SizedBox(height: 2),
                                  Text(
                                    '${_currencyFormat.format(balance?.totalEarned ?? 0.0)} QAR',
                                    style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.w700),
                                  ),
                                ],
                              ),
                            ],
                          ),
                          const SizedBox(height: AppSpacing.lg),
                          SizedBox(
                            width: double.infinity,
                            child: ElevatedButton.icon(
                              onPressed: () => _showPayoutSheet(context, balance?.availableBalance ?? 0.0),
                              icon: const Icon(Icons.arrow_upward_rounded, size: 18),
                              label: const Text('Request Payout', style: TextStyle(fontWeight: FontWeight.w700)),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: AppColors.brand500,
                                foregroundColor: Colors.white,
                                padding: const EdgeInsets.symmetric(vertical: 12),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),

                  // 2. Tab Bar: Payout Requests / Ledger
                  TabBar(
                    controller: _tabController,
                    labelColor: AppColors.brand600,
                    unselectedLabelColor: AppColors.slate500,
                    indicatorColor: AppColors.brand500,
                    indicatorWeight: 2.5,
                    tabs: const [
                      Tab(text: 'Payout Requests'),
                      Tab(text: 'Transactions Ledger'),
                    ],
                  ),

                  // 3. Tab Views
                  Expanded(
                    child: TabBarView(
                      controller: _tabController,
                      children: [
                        // Payouts Tab
                        walletProvider.payouts.isEmpty
                            ? const Center(
                                child: Text('No payout requests yet', style: TextStyle(color: AppColors.slate400, fontSize: 13)),
                              )
                            : ListView.separated(
                                padding: const EdgeInsets.all(AppSpacing.lg),
                                itemCount: walletProvider.payouts.length,
                                separatorBuilder: (_, __) => const SizedBox(height: AppSpacing.md),
                                itemBuilder: (context, index) {
                                  final p = walletProvider.payouts[index];
                                  return _buildPayoutCard(p);
                                },
                              ),

                        // Transactions Tab
                        walletProvider.transactions.isEmpty
                            ? const Center(
                                child: Text('No transaction history recorded', style: TextStyle(color: AppColors.slate400, fontSize: 13)),
                              )
                            : ListView.separated(
                                padding: const EdgeInsets.all(AppSpacing.lg),
                                itemCount: walletProvider.transactions.length,
                                separatorBuilder: (_, __) => const SizedBox(height: AppSpacing.md),
                                itemBuilder: (context, index) {
                                  final tx = walletProvider.transactions[index];
                                  return _buildTransactionCard(tx);
                                },
                              ),
                      ],
                    ),
                  ),
                ],
              ),
      ),
    );
  }

  Widget _buildPayoutCard(dynamic p) {
    return Container(
      padding: const EdgeInsets.all(AppSpacing.lg),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.slate200.withOpacity(0.8)),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                '${_currencyFormat.format(p.amount)} QAR',
                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.ink),
              ),
              const SizedBox(height: 4),
              Text(
                'Via ${p.method} • ${p.createdAt != null ? DateFormat('MMM d, yyyy').format(DateTime.parse(p.createdAt)) : ''}',
                style: const TextStyle(fontSize: 12, color: AppColors.slate400),
              ),
            ],
          ),
          StatusBadge(status: p.status, isSmall: true),
        ],
      ),
    );
  }

  Widget _buildTransactionCard(dynamic tx) {
    final isCredit = tx.type == 'credit';

    return Container(
      padding: const EdgeInsets.all(AppSpacing.lg),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: AppColors.slate200.withOpacity(0.8)),
      ),
      child: Row(
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: isCredit ? AppColors.successBg : AppColors.dangerBg,
              shape: BoxShape.circle,
            ),
            child: Icon(
              isCredit ? Icons.arrow_downward_rounded : Icons.arrow_upward_rounded,
              size: 18,
              color: isCredit ? AppColors.success : AppColors.danger,
            ),
          ),
          const SizedBox(width: AppSpacing.md),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  tx.description ?? (isCredit ? 'Sale Earning' : 'Payout Debit'),
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.ink),
                ),
                if (tx.createdAt != null) ...[
                  const SizedBox(height: 2),
                  Text(
                    DateFormat('MMM d, yyyy · hh:mm a').format(DateTime.parse(tx.createdAt)),
                    style: const TextStyle(fontSize: 11, color: AppColors.slate400),
                  ),
                ],
              ],
            ),
          ),
          Text(
            '${isCredit ? '+' : '-'}${_currencyFormat.format(tx.amount)} QAR',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w700,
              color: isCredit ? AppColors.success : AppColors.danger,
            ),
          ),
        ],
      ),
    );
  }
}
