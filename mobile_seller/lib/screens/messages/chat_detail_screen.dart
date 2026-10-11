import 'dart:async';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:http/http.dart' as http;
import 'package:image_picker/image_picker.dart';

import '../../core/network/api_client.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../../core/widgets/app_snackbar.dart';
import '../../core/widgets/empty_view.dart';
import '../../core/widgets/loading_view.dart';

class ChatDetailScreen extends StatefulWidget {
  final int conversationId;
  final String otherUserName;
  final String? subtitle;

  const ChatDetailScreen({
    super.key,
    required this.conversationId,
    required this.otherUserName,
    this.subtitle,
  });

  @override
  State<ChatDetailScreen> createState() => _ChatDetailScreenState();
}

class _ChatDetailScreenState extends State<ChatDetailScreen> {
  final ApiClient _apiClient = ApiClient();
  final TextEditingController _textController = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  final ImagePicker _picker = ImagePicker();

  bool _isLoading = true;
  bool _isSending = false;
  String? _errorMessage;

  Map<String, dynamic>? _conversationData;
  List<dynamic> _messages = [];
  XFile? _selectedAttachment;
  Timer? _pollingTimer;

  @override
  void initState() {
    super.initState();
    _fetchMessages();
    // Poll for new messages every 5 seconds
    _pollingTimer = Timer.periodic(const Duration(seconds: 5), (_) => _pollNewMessages());
  }

  @override
  void dispose() {
    _pollingTimer?.cancel();
    _textController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _fetchMessages() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final response = await _apiClient.get('/messages/${widget.conversationId}');
      if (mounted && response is Map<String, dynamic>) {
        setState(() {
          _conversationData = response['conversation'] as Map<String, dynamic>?;
          _messages = (response['messages'] as List?) ?? [];
          _isLoading = false;
        });
        _scrollToBottom();
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

  Future<void> _pollNewMessages() async {
    if (!mounted || _isSending) return;
    try {
      final response = await _apiClient.get('/messages/${widget.conversationId}');
      if (mounted && response is Map<String, dynamic>) {
        final newMessages = (response['messages'] as List?) ?? [];
        if (newMessages.length != _messages.length) {
          setState(() {
            _messages = newMessages;
          });
          _scrollToBottom();
        }
      }
    } catch (_) {}
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  Future<void> _pickImage() async {
    try {
      final picked = await _picker.pickImage(source: ImageSource.gallery, imageQuality: 80);
      if (picked != null) {
        setState(() => _selectedAttachment = picked);
      }
    } catch (_) {}
  }

  Future<void> _sendMessage() async {
    final text = _textController.text.trim();
    if (text.isEmpty && _selectedAttachment == null) return;

    setState(() => _isSending = true);
    _textController.clear();
    final attachmentToSend = _selectedAttachment;
    setState(() => _selectedAttachment = null);

    try {
      if (attachmentToSend != null) {
        final file = await http.MultipartFile.fromPath('attachment', attachmentToSend.path);
        await _apiClient.multipart(
          '/messages/${widget.conversationId}',
          fields: text.isNotEmpty ? {'body': text} : {},
          files: [file],
        );
      } else {
        await _apiClient.post(
          '/messages/${widget.conversationId}',
          body: {'body': text},
        );
      }

      await _pollNewMessages();
    } catch (e) {
      if (mounted) {
        AppSnackBar.error(context, 'Failed to send: $e');
      }
    } finally {
      if (mounted) setState(() => _isSending = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final other = (_conversationData?['other_user'] as Map<String, dynamic>?) ?? {};
    final displayName = other['name']?.toString() ?? widget.otherUserName;
    final role = other['role']?.toString() ?? widget.subtitle ?? '';
    final product = _conversationData?['product'] as Map<String, dynamic>?;

    return Scaffold(
      backgroundColor: AppColors.slate50,
      appBar: AppBar(
        titleSpacing: 0,
        title: Row(
          children: [
            CircleAvatar(
              radius: 18,
              backgroundColor: AppColors.brand100,
              child: Text(
                displayName.isNotEmpty ? displayName[0].toUpperCase() : 'U',
                style: const TextStyle(fontWeight: FontWeight.w700, color: AppColors.brand700),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    displayName,
                    style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.ink),
                    overflow: TextOverflow.ellipsis,
                  ),
                  if (role.isNotEmpty)
                    Text(
                      role.toUpperCase(),
                      style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: AppColors.slate500),
                    ),
                ],
              ),
            ),
          ],
        ),
      ),
      body: Column(
        children: [
          // Optional Product Context Header
          if (product != null)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              color: AppColors.surface,
              child: Row(
                children: [
                  if (product['image_url'] != null)
                    ClipRRect(
                      borderRadius: BorderRadius.circular(6),
                      child: Image.network(
                        product['image_url'],
                        width: 40,
                        height: 40,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => const Icon(Icons.inventory_2_outlined, size: 28),
                      ),
                    )
                  else
                    const Icon(Icons.inventory_2_outlined, size: 28, color: AppColors.brand500),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          product['title']?.toString() ?? 'Product Inquiry',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.ink),
                        ),
                        if (product['price'] != null)
                          Text(
                            'QAR ${(product['price'] as num).toStringAsFixed(2)}',
                            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.brand600),
                          ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

          // Messages View
          Expanded(
            child: _isLoading
                ? const LoadingView(message: 'Loading messages...')
                : _errorMessage != null
                    ? Center(
                        child: Text(_errorMessage!, style: const TextStyle(color: AppColors.danger)),
                      )
                    : _messages.isEmpty
                        ? const EmptyView(
                            title: 'No messages yet',
                            subtitle: 'Say hello to start the conversation!',
                            icon: Icons.chat_bubble_outline_rounded,
                          )
                        : ListView.builder(
                            controller: _scrollController,
                            padding: const EdgeInsets.all(AppSpacing.md),
                            itemCount: _messages.length,
                            itemBuilder: (context, index) {
                              final msg = _messages[index] as Map<String, dynamic>;
                              final isMine = msg['is_mine'] == true;
                              return _buildMessageBubble(msg, isMine);
                            },
                          ),
          ),

          // Attachment preview thumbnail if selected
          if (_selectedAttachment != null)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              color: AppColors.surface,
              child: Row(
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(6),
                    child: Image.file(
                      File(_selectedAttachment!.path),
                      width: 50,
                      height: 50,
                      fit: BoxFit.cover,
                    ),
                  ),
                  const SizedBox(width: 12),
                  const Expanded(
                    child: Text('Image attachment ready', style: TextStyle(fontSize: 13, color: AppColors.slate700)),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, size: 20, color: AppColors.danger),
                    onPressed: () => setState(() => _selectedAttachment = null),
                  ),
                ],
              ),
            ),

          // Bottom Input Bar
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: BoxDecoration(
              color: AppColors.surface,
              border: Border(top: BorderSide(color: AppColors.slate200)),
            ),
            child: SafeArea(
              top: false,
              child: Row(
                children: [
                  IconButton(
                    icon: const Icon(Icons.image_outlined, color: AppColors.slate500),
                    onPressed: _pickImage,
                  ),
                  Expanded(
                    child: CallbackShortcuts(
                      bindings: {
                        const SingleActivator(LogicalKeyboardKey.enter): () => _sendMessage(),
                        const SingleActivator(LogicalKeyboardKey.numpadEnter): () => _sendMessage(),
                      },
                      child: TextField(
                        controller: _textController,
                        textCapitalization: TextCapitalization.sentences,
                        textInputAction: TextInputAction.send,
                        maxLines: 4,
                        minLines: 1,
                        decoration: const InputDecoration(
                          hintText: 'Type a message...',
                          border: InputBorder.none,
                          enabledBorder: InputBorder.none,
                          focusedBorder: InputBorder.none,
                          contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          fillColor: Colors.transparent,
                        ),
                        onSubmitted: (_) => _sendMessage(),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  InkWell(
                    onTap: _isSending ? null : _sendMessage,
                    borderRadius: BorderRadius.circular(AppRadius.md),
                    child: Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: _isSending ? AppColors.slate300 : AppColors.brand500,
                        borderRadius: BorderRadius.circular(AppRadius.md),
                      ),
                      child: _isSending
                          ? const SizedBox(
                              width: 18,
                              height: 18,
                              child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                            )
                          : const Icon(Icons.send_rounded, color: Colors.white, size: 20),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMessageBubble(Map<String, dynamic> msg, bool isMine) {
    final body = msg['body']?.toString();
    final time = msg['created_at']?.toString() ?? '';
    final hasAttachment = msg['has_attachment'] == true;
    final isImage = msg['is_image'] == true;
    final attachmentUrl = msg['attachment_url']?.toString();
    final attachmentName = msg['attachment_name']?.toString() ?? 'Attachment';

    return Align(
      alignment: isMine ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.only(bottom: 12),
        constraints: BoxConstraints(maxWidth: MediaQuery.of(context).size.width * 0.78),
        child: Column(
          crossAxisAlignment: isMine ? CrossAxisAlignment.end : CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: isMine ? AppColors.brand500 : AppColors.surface,
                borderRadius: BorderRadius.only(
                  topLeft: const Radius.circular(16),
                  topRight: const Radius.circular(16),
                  bottomLeft: Radius.circular(isMine ? 16 : 4),
                  bottomRight: Radius.circular(isMine ? 4 : 16),
                ),
                border: isMine ? null : Border.all(color: AppColors.slate200),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.04),
                    blurRadius: 4,
                    offset: const Offset(0, 1),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (hasAttachment) ...[
                    if (isImage && attachmentUrl != null)
                      ClipRRect(
                        borderRadius: BorderRadius.circular(8),
                        child: Image.network(
                          attachmentUrl,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => const Icon(Icons.broken_image, size: 40),
                        ),
                      )
                    else
                      Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.attach_file_rounded, size: 16, color: isMine ? Colors.white : AppColors.slate600),
                          const SizedBox(width: 4),
                          Flexible(
                            child: Text(
                              attachmentName,
                              style: TextStyle(
                                fontSize: 12,
                                color: isMine ? Colors.white : AppColors.slate700,
                                decoration: TextDecoration.underline,
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    if (body != null && body.isNotEmpty) const SizedBox(height: 6),
                  ],
                  if (body != null && body.isNotEmpty)
                    Text(
                      body,
                      style: TextStyle(
                        fontSize: 14,
                        color: isMine ? Colors.white : AppColors.ink,
                        height: 1.35,
                      ),
                    ),
                ],
              ),
            ),
            if (time.isNotEmpty) ...[
              const SizedBox(height: 3),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 4),
                child: Text(
                  time,
                  style: const TextStyle(
                    fontSize: 11,
                    color: AppColors.slate400,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
