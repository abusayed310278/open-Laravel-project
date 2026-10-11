import 'package:flutter/material.dart';

import '../../../core/network/api_client.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/app_spacing.dart';
import '../../../core/widgets/custom_button.dart';
import '../../../core/widgets/custom_text_field.dart';
import '../../../core/widgets/empty_view.dart';
import '../../../core/widgets/loading_view.dart';

class SupportTicketsScreen extends StatefulWidget {
  const SupportTicketsScreen({super.key});

  @override
  State<SupportTicketsScreen> createState() => _SupportTicketsScreenState();
}

class _SupportTicketsScreenState extends State<SupportTicketsScreen> {
  final ApiClient _apiClient = ApiClient();
  bool _isLoading = true;
  String? _errorMessage;
  List<dynamic> _tickets = [];
  List<String> _categories = [];

  @override
  void initState() {
    super.initState();
    _fetchTickets();
  }

  Future<void> _fetchTickets() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final response = await _apiClient.get('/support-tickets');
      if (mounted && response is Map<String, dynamic>) {
        setState(() {
          _tickets = (response['tickets'] as List?) ?? [];
          _categories = ((response['categories'] as List?) ?? []).map((e) => e.toString()).toList();
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _errorMessage = e.toString();
          _isLoading = false;
        });
      }
    }
  }

  void _showNewTicketModal() {
    final subjectController = TextEditingController();
    final bodyController = TextEditingController();
    String selectedCategory = _categories.isNotEmpty ? _categories.first : 'General Inquiry';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) => Container(
          padding: EdgeInsets.fromLTRB(AppSpacing.xl, AppSpacing.lg, AppSpacing.xl, MediaQuery.of(context).viewInsets.bottom + AppSpacing.xl),
          decoration: const BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.vertical(top: Radius.circular(AppRadius.lg)),
          ),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('New Support Ticket', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.ink)),
                    IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.of(ctx).pop()),
                  ],
                ),
                const SizedBox(height: AppSpacing.md),
                DropdownButtonFormField<String>(
                  value: selectedCategory,
                  decoration: const InputDecoration(labelText: 'Inquiry Category'),
                  items: _categories
                      .map((cat) => DropdownMenuItem(value: cat, child: Text(cat, style: const TextStyle(fontSize: 13))))
                      .toList(),
                  onChanged: (val) {
                    if (val != null) setModalState(() => selectedCategory = val);
                  },
                ),
                const SizedBox(height: AppSpacing.md),
                CustomTextField(
                  controller: subjectController,
                  label: 'Subject',
                  hint: 'Brief summary of issue...',
                ),
                const SizedBox(height: AppSpacing.md),
                CustomTextField(
                  controller: bodyController,
                  label: 'Detailed Message',
                  hint: 'Explain your question or problem in detail...',
                  maxLines: 4,
                ),
                const SizedBox(height: AppSpacing.xl),
                CustomButton(
                  text: 'Submit Support Ticket',
                  onPressed: () async {
                    if (subjectController.text.trim().isEmpty || bodyController.text.trim().isEmpty) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('Please fill in both subject and message.'), backgroundColor: AppColors.danger),
                      );
                      return;
                    }

                    Navigator.of(ctx).pop();
                    try {
                      await _apiClient.post(
                        '/support-tickets',
                        body: {
                          'subject': subjectController.text.trim(),
                          'category': selectedCategory,
                          'body': bodyController.text.trim(),
                        },
                      );
                      _fetchTickets();
                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Support ticket created successfully!'), backgroundColor: AppColors.success),
                        );
                      }
                    } catch (e) {
                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(content: Text('Failed to submit ticket: $e'), backgroundColor: AppColors.danger),
                        );
                      }
                    }
                  },
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        title: const Text('Support & Tickets'),
        actions: [
          IconButton(
            icon: const Icon(Icons.add_circle_outline_rounded, color: AppColors.brand500),
            tooltip: 'New Ticket',
            onPressed: _showNewTicketModal,
          ),
        ],
      ),
      body: _isLoading
          ? const LoadingView(message: 'Loading support tickets...')
          : RefreshIndicator(
              onRefresh: _fetchTickets,
              color: AppColors.brand500,
              child: _tickets.isEmpty
                  ? EmptyView(
                      icon: Icons.chat_bubble_outline_rounded,
                      title: 'No Support Tickets',
                      subtitle: 'Need assistance? Create a ticket and our support team will help you.',
                      actionLabel: '+ New Support Ticket',
                      onAction: _showNewTicketModal,
                    )
                  : ListView.separated(
                      padding: const EdgeInsets.all(AppSpacing.lg),
                      itemCount: _tickets.length,
                      separatorBuilder: (_, __) => const SizedBox(height: AppSpacing.md),
                      itemBuilder: (context, index) {
                        final t = _tickets[index] as Map<String, dynamic>;
                        final status = t['status']?.toString() ?? 'open';
                        final isClosed = status == 'closed' || status == 'resolved';

                        return Container(
                          padding: const EdgeInsets.all(AppSpacing.lg),
                          decoration: BoxDecoration(
                            color: AppColors.surface,
                            borderRadius: BorderRadius.circular(AppRadius.md),
                            border: Border.all(color: AppColors.slate200),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    t['ticket_number']?.toString() ?? '',
                                    style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.brand600),
                                  ),
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                    decoration: BoxDecoration(
                                      color: isClosed ? AppColors.slate100 : AppColors.successBg,
                                      borderRadius: BorderRadius.circular(AppRadius.pill),
                                    ),
                                    child: Text(
                                      status.toUpperCase(),
                                      style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700, color: isClosed ? AppColors.slate600 : AppColors.success),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 6),
                              Text(
                                t['subject']?.toString() ?? '',
                                style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.ink),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                'Category: ${t['category'] ?? 'General'}',
                                style: const TextStyle(fontSize: 12, color: AppColors.slate500),
                              ),
                              const Divider(height: 18, color: AppColors.slate100),
                              Text(
                                'Created on ${t['created_at'] ?? 'Recently'}',
                                style: const TextStyle(fontSize: 11, color: AppColors.slate400),
                              ),
                            ],
                          ),
                        );
                      },
                    ),
            ),
      floatingActionButton: FloatingActionButton(
        backgroundColor: AppColors.brand500,
        foregroundColor: Colors.white,
        onPressed: _showNewTicketModal,
        child: const Icon(Icons.add),
      ),
    );
  }
}
