import 'package:flutter/material.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/empty_state.dart';

enum _ReturnStatus { requested, approved, rejected, refunded }

class _ReturnRequest {
  const _ReturnRequest({required this.orderId, required this.product, required this.reason, required this.status, required this.date});
  final String orderId;
  final String product;
  final String reason;
  final _ReturnStatus status;
  final String date;
}

const _returns = [
  _ReturnRequest(orderId: 'OB-10052', product: 'Samsung Galaxy Watch 6 44mm', reason: 'Changed my mind', status: _ReturnStatus.refunded, date: '25 Aug 2026'),
];

class ReturnsScreen extends StatelessWidget {
  const ReturnsScreen({super.key});

  Color _colorFor(_ReturnStatus status) => switch (status) {
        _ReturnStatus.requested => AppColors.info,
        _ReturnStatus.approved => AppColors.success,
        _ReturnStatus.rejected => AppColors.danger,
        _ReturnStatus.refunded => AppColors.brand600,
      };

  String _labelFor(_ReturnStatus status) => switch (status) {
        _ReturnStatus.requested => 'Requested',
        _ReturnStatus.approved => 'Approved',
        _ReturnStatus.rejected => 'Rejected',
        _ReturnStatus.refunded => 'Refunded',
      };

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Returns & Refunds')),
      body: _returns.isEmpty
          ? const EmptyState(icon: Icons.undo_rounded, title: 'No return requests', message: 'Your refund and return requests will appear here.')
          : ListView.separated(
              padding: const EdgeInsets.all(AppSpacing.lg),
              itemCount: _returns.length,
              separatorBuilder: (_, _) => const SizedBox(height: AppSpacing.md),
              itemBuilder: (context, i) {
                final r = _returns[i];
                return Container(
                  padding: const EdgeInsets.all(AppSpacing.md),
                  decoration: BoxDecoration(color: AppColors.surface, border: Border.all(color: AppColors.slate200), borderRadius: BorderRadius.circular(AppRadius.md)),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Text('#${r.orderId}', style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
                          const Spacer(),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(color: _colorFor(r.status).withValues(alpha: 0.12), borderRadius: BorderRadius.circular(6)),
                            child: Text(_labelFor(r.status), style: TextStyle(fontSize: 11, color: _colorFor(r.status), fontWeight: FontWeight.w700)),
                          ),
                        ],
                      ),
                      const SizedBox(height: AppSpacing.xs),
                      Text(r.product, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                      Text('Reason: ${r.reason}', style: const TextStyle(fontSize: 12, color: AppColors.slate500)),
                      Text(r.date, style: const TextStyle(fontSize: 11, color: AppColors.slate400)),
                    ],
                  ),
                );
              },
            ),
    );
  }
}
