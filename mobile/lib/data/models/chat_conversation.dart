class ChatMessage {
  const ChatMessage({
    required this.text,
    required this.isMe,
    required this.time,
  });

  final String text;
  final bool isMe;
  final String time;
}

class ChatConversation {
  const ChatConversation({
    required this.id,
    required this.sellerName,
    required this.avatarInitial,
    required this.lastMessage,
    required this.time,
    this.unreadCount = 0,
    this.messages = const [],
  });

  final String id;
  final String sellerName;
  final String avatarInitial;
  final String lastMessage;
  final String time;
  final int unreadCount;
  final List<ChatMessage> messages;
}
