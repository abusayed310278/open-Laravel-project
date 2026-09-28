import 'package:flutter/foundation.dart';
import '../core/network/api_client.dart';
import '../core/network/api_config.dart';
import '../data/models/cart_item.dart';
import '../data/models/product.dart';

class CartProvider extends ChangeNotifier {
  final ApiClient _apiClient;

  List<CartItem> _items = [];
  bool _isLoading = false;
  String? _errorMessage;
  double _subtotal = 0.0;
  bool _hasClearedAfterCheckout = false;

  List<CartItem> get items => List.unmodifiable(_items);
  int get itemCount => _items.fold(0, (sum, item) => sum + item.quantity);
  double get subtotal => _subtotal > 0 ? _subtotal : _items.fold(0, (sum, item) => sum + item.lineTotal);
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  CartProvider({ApiClient? apiClient}) : _apiClient = apiClient ?? ApiClient();

  Future<void> fetchCart() async {
    if (_hasClearedAfterCheckout) {
      _items = [];
      _subtotal = 0.0;
      notifyListeners();
      return;
    }

    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiClient.get(ApiConfig.cart);
      if (response is Map<String, dynamic>) {
        if (response.containsKey('items') && response['items'] is List) {
          final list = response['items'] as List;
          final fetched = list.map((item) {
            final p = Product.fromJson(item['product']);
            final qty = (item['quantity'] is num) ? (item['quantity'] as num).toInt() : 1;
            return CartItem(product: p, quantity: qty);
          }).toList();

          if (!_hasClearedAfterCheckout) {
            _items = fetched;
          }
        }
        if (response.containsKey('subtotal') && !_hasClearedAfterCheckout) {
          _subtotal = (response['subtotal'] as num).toDouble();
        }
      }
    } catch (_) {
      if (_hasClearedAfterCheckout) {
        _items = [];
        _subtotal = 0.0;
      }
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> addToCart(Product product, {int quantity = 1}) async {
    _hasClearedAfterCheckout = false;
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await _apiClient.post(
        ApiConfig.addToCart,
        body: {
          'product_id': int.tryParse(product.id) ?? product.id,
          'quantity': quantity,
        },
      );
      await fetchCart();
      return true;
    } catch (_) {
      // Local fallback for guest cart / offline interaction
      final index = _items.indexWhere((i) => i.product.id == product.id);
      if (index >= 0) {
        _items[index].quantity += quantity;
      } else {
        _items.add(CartItem(product: product, quantity: quantity));
      }
      _isLoading = false;
      notifyListeners();
      return true;
    }
  }

  Future<void> updateQuantity(String productId, int quantity) async {
    final parsedId = int.tryParse(productId);
    if (parsedId == null) return;

    try {
      await _apiClient.put(
        ApiConfig.updateCart(parsedId),
        body: {'quantity': quantity},
      );
      await fetchCart();
    } catch (_) {
      final index = _items.indexWhere((i) => i.product.id == productId);
      if (index >= 0) {
        if (quantity <= 0) {
          _items.removeAt(index);
        } else {
          _items[index].quantity = quantity;
        }
        notifyListeners();
      }
    }
  }

  Future<void> removeFromCart(String productId) async {
    final parsedId = int.tryParse(productId);
    if (parsedId == null) return;

    try {
      await _apiClient.delete(ApiConfig.removeFromCart(parsedId));
      await fetchCart();
    } catch (_) {
      _items.removeWhere((i) => i.product.id == productId);
      notifyListeners();
    }
  }

  Future<void> clear() async {
    _hasClearedAfterCheckout = true;
    _items.clear();
    _subtotal = 0.0;
    notifyListeners();

    try {
      await _apiClient.delete(ApiConfig.cart);
    } catch (_) {
      try {
        await _apiClient.post('/cart/clear');
      } catch (_) {}
    }
  }
}
