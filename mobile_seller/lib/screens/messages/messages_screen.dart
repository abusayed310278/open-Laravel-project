import 'package:flutter/material.dart';

import '../../core/network/api_client.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../core/widgets/empty_view.dart';
import '../../core/widgets/loading_view.dart';
import 'chat_detail_screen.dart';

class MessagesScreen extends StatefulWidget {
  const MessagesScreen({super.key});

  @override
  State<MessagesScreen> createState() => _MessagesScreenState();
}

class _MessagesScreenState extends State<MessagesScreen> {
  final ApiClient _apiClient = ApiClient();
  final TextEditingController _searchController = TextEditingController();

  bool _isLoading = true;
  String? _errorMessage;
  List<dynamic> _conversations = [];
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _fetchConversations();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _fetchConversations() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final response = await _apiClient.get('/messages');
      if (mounted && response is Map<String, dynamic>) {
        setState(() {
          _conversations = (response['conversations'] as List?) ?? [];
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

  Future<void> _startChatWithSupport() async {
    try {
      final response = await _apiClient.post(
        '/messages/start',
        body: {},
      );

      if (mounted && response is Map<String, dynamic> && response['conversation_id'] != null) {
        final convId = response['conversation_id'] as int;
        await Navigator.of(context).push(
          MaterialPageRoute(
            builder: (_) => ChatDetailScreen(
              conversationId: convId,
              otherUserName: 'Support Admin',
              subtitle: 'Official Support',
            ),
          ),
        );
        _fetchConversations();
      }
    } catch (e) {
      if (mounted) {
        AppSnackBar.error(context, 'Failed to start chat: $e');
      }
    }
  }

  List<dynamic> get _filteredConversations {
    if (_searchQuery.trim().isEmpty) return _conversations;
    final q = _searchQuery.toLowerCase();
    return _conversations.where((conv) {
      final other = (conv['other_user'] as Map<String, dynamic>?) ?? {};
      final name = (other['name'] ?? '').toString().toLowerCase();
      final store = (other['store_name'] ?? '').toString().toLowerCase();
      final lastMsg = (conv['latest_message']?['body'] ?? '').toString().toLowerCase();
      return name.contains(q) || store.contains(q) || lastMsg.contains(q);
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    final filtered = _filteredConversations;

    return Scaffold(
      backgroundColor: AppColors.surface,
      appBar: AppBar(
        title: const Text('Messages & Direct Chat'),
        actions: [
          IconButton(
            tooltip: 'Chat with Support',
            icon: const Icon(Icons.support_agent_rounded, color: AppColors.brand500),
            onPressed: _startChatWithSupport,
          ),
        ],
      ),
      body: Column(
        children: [
          // Search Bar
          Padding(
            padding: const EdgeInsets.all(AppSpacing.md),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Search conversations...',
                prefixIcon: const Icon(Icons.search_rounded, color: AppColors.slate400, size: 20),
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                suffixIcon: _searchQuery.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear, size: 18),
                        onPressed: () {
                          _searchController.clear();
                          setState(() => _searchQuery = '');
                        },
                      )
                    : null,
              ),
              onChanged: (val) => setState(() => _searchQuery = val),
            ),
          ),

          // Conversations List
          Expanded(
            child: _isLoading
                ? const LoadingView(message: 'Loading conversations...')
                : _errorMessage != null
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Text(_errorMessage!, style: const TextStyle(color: AppColors.danger)),
                            const SizedBox(height: AppSpacing.md),
                            ElevatedButton(onPressed: _fetchConversations, child: const Text('Retry')),
                          ],
                        ),
                      )
                    : filtered.isEmpty
                        ? RefreshIndicator(
                            onRefresh: _fetchConversations,
                            color: AppColors.brand500,
                            child: ListView(
                              children: [
                                SizedBox(height: MediaQuery.of(context).size.height * 0.15),
                                EmptyView(
                                  title: _searchQuery.isNotEmpty ? 'No conversations found' : 'No messages yet',
                                  subtitle: _searchQuery.isNotEmpty
                                      ? 'Try a different search keyword'
                                      : 'Direct buyer queries and support threads will appear here.',
                                  icon: Icons.chat_bubble_outline_rounded,
                                  actionLabel: 'Contact Support',
                                  onAction: _startChatWithSupport,
                                ),
                              ],
                            ),
                          )
                        : RefreshIndicator(
                            onRefresh: _fetchConversations,
                            color: AppColors.brand500,
                            child: ListView.separated(
                              itemCount: filtered.length,
                              separatorBuilder: (_, _) => const Divider(height: 1, indent: 76, color: AppColors.slate100),
                              itemBuilder: (context, index) {
                                final conv = filtered[index] as Map<String, dynamic>;
                                final other = (conv['other_user'] as Map<String, dynamic>?) ?? {};
                                final name = other['name']?.toString() ?? 'User';
                                final role = other['role']?.toString() ?? '';
                                final unread = (conv['unread_count'] as num?)?.toInt() ?? 0;
                                final latestMsg = conv['latest_message'] as Map<String, dynamic>?;
                                final lastText = latestMsg?['body']?.toString() ??
                                    (latestMsg?['has_attachment'] == true ? '📎 [Attachment]' : 'Conversation started');
                                final time = latestMsg?['created_at']?.toString() ?? '';
                                final product = conv['product'] as Map<String, dynamic>?;

                                return ListTile(
                                  contentPadding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: 4),
                                  onTap: () async {
                                    await Navigator.of(context).push(
                                      MaterialPageRoute(
                                        builder: (_) => ChatDetailScreen(
                                          conversationId: conv['id'] as int,
                                          otherUserName: name,
                                          subtitle: role,
                                        ),
                                      ),
                                    );
                                    _fetchConversations();
                                  },
                                  leading: Stack(
                                    clipBehavior: Clip.none,
                                    children: [
                                      CircleAvatar(
                                        radius: 24,
                                        backgroundColor: role == 'admin' ? AppColors.brand100 : AppColors.slate100,
                                        child: Text(
                                          name.isNotEmpty ? name[0].toUpperCase() : 'U',
                                          style: TextStyle(
                                            fontSize: 18,
                                            fontWeight: FontWeight.w700,
                                            color: role == 'admin' ? AppColors.brand600 : AppColors.slate700,
                                          ),
                                        ),
                                      ),
                                      if (role == 'admin')
                                        Positioned(
                                          bottom: -2,
                                          right: -2,
                                          child: Container(
                                            padding: const EdgeInsets.all(2),
                                            decoration: const BoxDecoration(color: AppColors.brand500, shape: BoxShape.circle),
                                            child: const Icon(Icons.star, size: 10, color: Colors.white),
                                          ),
                                        ),
                                    ],
                                  ),
                                  title: Row(
                                    children: [
                                      Expanded(
                                        child: Text(
                                          name,
                                          style: TextStyle(
                                            fontSize: 15,
                                            fontWeight: unread > 0 ? FontWeight.w800 : FontWeight.w600,
                                            color: AppColors.ink,
                                          ),
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      ),
                                      if (time.isNotEmpty)
                                        Text(
                                          time,
                                          style: TextStyle(
                                            fontSize: 11,
                                            color: unread > 0 ? AppColors.brand600 : AppColors.slate400,
                                            fontWeight: unread > 0 ? FontWeight.w700 : FontWeight.normal,
                                          ),
                                        ),
                                    ],
                                  ),
                                  subtitle: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      const SizedBox(height: 2),
                                      Row(
                                        children: [
                                          Expanded(
                                            child: Text(
                                              lastText,
                                              maxLines: 1,
                                              overflow: TextOverflow.ellipsis,
                                              style: TextStyle(
                                                fontSize: 13,
                                                color: unread > 0 ? AppColors.ink : AppColors.slate500,
                                                fontWeight: unread > 0 ? FontWeight.w600 : FontWeight.normal,
                                              ),
                                            ),
                                          ),
                                          if (unread > 0)
                                            Container(
                                              margin: const EdgeInsets.only(left: 6),
                                              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                              decoration: BoxDecoration(
                                                color: AppColors.brand500,
                                                borderRadius: BorderRadius.circular(10),
                                              ),
                                              child: Text(
                                                unread.toString(),
                                                style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.white),
                                              ),
                                            ),
                                        ],
                                      ),
                                      if (product != null) ...[
                                        const SizedBox(height: 4),
                                        Row(
                                          children: [
                                            const Icon(Icons.inventory_2_outlined, size: 12, color: AppColors.brand600),
                                            const SizedBox(width: 4),
                                            Expanded(
                                              child: Text(
                                                product['title']?.toString() ?? '',
                                                style: const TextStyle(fontSize: 11, color: AppColors.brand700, fontWeight: FontWeight.w600),
                                                maxLines: 1,
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ],
                                    ],
                                  ),
                                );
                              },
                            ),
                          ),
          ),
        ],
      ),
    );
  }
}
