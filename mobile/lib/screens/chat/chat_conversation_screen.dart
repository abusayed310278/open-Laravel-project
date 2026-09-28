import 'package:flutter/material.dart';

import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../data/models/chat_conversation.dart';

class ChatConversationScreen extends StatefulWidget {
  const ChatConversationScreen({super.key, required this.conversation});

  final ChatConversation conversation;

  @override
  State<ChatConversationScreen> createState() => _ChatConversationScreenState();
}

class _ChatConversationScreenState extends State<ChatConversationScreen> {
  final _controller = TextEditingController();
  late final List<ChatMessage> _messages = [...widget.conversation.messages];

  void _send() {
    final text = _controller.text.trim();
    if (text.isEmpty) return;
    setState(() {
      _messages.add(ChatMessage(text: text, isMe: true, time: 'Now'));
      _controller.clear();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        titleSpacing: 0,
        title: Row(
          children: [
            CircleAvatar(
              radius: 16,
              backgroundColor: AppColors.brand100,
              child: Text(widget.conversation.avatarInitial, style: const TextStyle(color: AppColors.brand700, fontSize: 12, fontWeight: FontWeight.w700)),
            ),
            const SizedBox(width: AppSpacing.sm),
            Text(widget.conversation.sellerName, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700)),
          ],
        ),
      ),
      body: Column(
        children: [
          Expanded(
            child: _messages.isEmpty
                ? Center(
                    child: Padding(
                      padding: const EdgeInsets.all(AppSpacing.xl),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(Icons.chat_bubble_outline_rounded, size: 48, color: AppColors.slate300),
                          const SizedBox(height: AppSpacing.md),
                          const Text('No messages yet', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 15, color: AppColors.ink)),
                          const SizedBox(height: 4),
                          Text(
                            'Send a message to start chatting with ${widget.conversation.sellerName}',
                            style: const TextStyle(fontSize: 12, color: AppColors.slate500),
                            textAlign: TextAlign.center,
                          ),
                        ],
                      ),
                    ),
                  )
                : ListView.builder(
                    reverse: true,
                    padding: const EdgeInsets.all(AppSpacing.lg),
                    itemCount: _messages.length,
                    itemBuilder: (context, i) {
                      final message = _messages[_messages.length - 1 - i];
                      return Align(
                        alignment: message.isMe ? Alignment.centerRight : Alignment.centerLeft,
                        child: Container(
                          margin: const EdgeInsets.only(bottom: AppSpacing.sm),
                          constraints: BoxConstraints(maxWidth: MediaQuery.of(context).size.width * 0.72),
                          padding: const EdgeInsets.symmetric(horizontal: AppSpacing.md, vertical: AppSpacing.sm),
                          decoration: BoxDecoration(
                            color: message.isMe ? AppColors.brand500 : AppColors.slate100,
                            borderRadius: BorderRadius.only(
                              topLeft: const Radius.circular(AppRadius.md),
                              topRight: const Radius.circular(AppRadius.md),
                              bottomLeft: Radius.circular(message.isMe ? AppRadius.md : 2),
                              bottomRight: Radius.circular(message.isMe ? 2 : AppRadius.md),
                            ),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              Text(message.text, style: TextStyle(fontSize: 13, color: message.isMe ? Colors.white : AppColors.ink, height: 1.4)),
                              const SizedBox(height: 2),
                              Text(message.time, style: TextStyle(fontSize: 10, color: message.isMe ? Colors.white70 : AppColors.slate400)),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
          ),
          Container(
            padding: const EdgeInsets.all(AppSpacing.md),
            decoration: const BoxDecoration(color: AppColors.surface, border: Border(top: BorderSide(color: AppColors.slate200))),
            child: SafeArea(
              top: false,
              child: Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _controller,
                      decoration: const InputDecoration(hintText: 'Type a message...', isDense: true),
                      onSubmitted: (_) => _send(),
                    ),
                  ),
                  const SizedBox(width: AppSpacing.sm),
                  IconButton.filled(
                    style: IconButton.styleFrom(backgroundColor: AppColors.brand500, foregroundColor: Colors.white),
                    icon: const Icon(Icons.send_rounded, size: 18),
                    onPressed: _send,
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
