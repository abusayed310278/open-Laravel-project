import 'package:flutter/foundation.dart';

import '../models/cart_item.dart';
import '../models/chat_conversation.dart';
import '../models/product.dart';

/// In-memory cart/wishlist state so the UI feels interactive while
/// designing — not a real persistence or backend layer.
class CartStore extends ChangeNotifier {
  CartStore._();
  static final CartStore instance = CartStore._();

  final List<CartItem> _items = [];
  List<CartItem> get items => List.unmodifiable(_items);

  int get itemCount => _items.fold(0, (sum, i) => sum + i.quantity);
  double get subtotal => _items.fold(0, (sum, i) => sum + i.lineTotal);

  void add(Product product, {int quantity = 1}) {
    final existingIndex = _items.indexWhere((i) => i.product.id == product.id);
    if (existingIndex >= 0) {
      _items[existingIndex].quantity += quantity;
    } else {
      _items.add(CartItem(product: product, quantity: quantity));
    }
    notifyListeners();
  }

  void updateQuantity(String productId, int quantity) {
    final index = _items.indexWhere((i) => i.product.id == productId);
    if (index < 0) return;
    if (quantity <= 0) {
      _items.removeAt(index);
    } else {
      _items[index].quantity = quantity;
    }
    notifyListeners();
  }

  void remove(String productId) {
    _items.removeWhere((i) => i.product.id == productId);
    notifyListeners();
  }

  void clear() {
    _items.clear();
    notifyListeners();
  }
}

class WishlistStore extends ChangeNotifier {
  WishlistStore._();
  static final WishlistStore instance = WishlistStore._();

  final Set<String> _productIds = {};
  Set<String> get productIds => Set.unmodifiable(_productIds);

  bool contains(String productId) => _productIds.contains(productId);

  void toggle(String productId) {
    if (_productIds.contains(productId)) {
      _productIds.remove(productId);
    } else {
      _productIds.add(productId);
    }
    notifyListeners();
  }
}

class ChatStore extends ChangeNotifier {
  ChatStore._();
  static final ChatStore instance = ChatStore._();

  final List<ChatConversation> _conversations = [];
  List<ChatConversation> get conversations => List.unmodifiable(_conversations);

  void addConversation(ChatConversation conversation) {
    _conversations.insert(0, conversation);
    notifyListeners();
  }

  void clear() {
    _conversations.clear();
    notifyListeners();
  }
}
