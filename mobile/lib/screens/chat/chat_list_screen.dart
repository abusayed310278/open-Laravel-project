import 'package:flutter/material.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/empty_state.dart';
import '../../data/mock/app_state.dart';
import 'chat_conversation_screen.dart';

class ChatListScreen extends StatelessWidget {
  const ChatListScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Messages')),
      body: ListenableBuilder(
        listenable: ChatStore.instance,
        builder: (context, _) {
          final conversations = ChatStore.instance.conversations;

          if (conversations.isEmpty) {
            return const EmptyState(
              icon: Icons.chat_bubble_outline_rounded,
              title: 'No messages yet',
              message: 'Your conversations with sellers will appear here when you send a message.',
            );
          }

          return ListView.separated(
            itemCount: conversations.length,
            separatorBuilder: (_, _) => const Divider(height: 1, indent: AppSpacing.lg, endIndent: AppSpacing.lg),
            itemBuilder: (context, i) {
              final conversation = conversations[i];
              return ListTile(
                contentPadding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.xs),
                leading: CircleAvatar(
                  radius: 22,
                  backgroundColor: AppColors.brand100,
                  child: Text(conversation.avatarInitial, style: const TextStyle(color: AppColors.brand700, fontWeight: FontWeight.w700)),
                ),
                title: Text(conversation.sellerName, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14)),
                subtitle: Text(conversation.lastMessage, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 12, color: AppColors.slate500)),
                trailing: Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(conversation.time, style: const TextStyle(fontSize: 11, color: AppColors.slate400)),
                    if (conversation.unreadCount > 0) ...[
                      const SizedBox(height: 4),
                      Container(
                        padding: const EdgeInsets.all(5),
                        decoration: const BoxDecoration(color: AppColors.brand500, shape: BoxShape.circle),
                        child: Text('${conversation.unreadCount}', style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.w700)),
                      ),
                    ],
                  ],
                ),
                onTap: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => ChatConversationScreen(conversation: conversation)),
                ),
              );
            },
          );
        },
      ),
    );
  }
}
