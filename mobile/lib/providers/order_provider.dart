import 'dart:io';
import 'package:flutter/foundation.dart';
import '../core/network/api_client.dart';
import '../core/network/api_config.dart';
import '../data/models/order.dart';

class OrderProvider extends ChangeNotifier {
  final ApiClient _apiClient;

  List<Order> _orders = [];
  List<Map<String, dynamic>> _paymentMethods = [];
  bool _isMultiVendor = false;
  String _vendorName = 'Openbox Platform';
  Order? _selectedOrder;
  bool _isLoading = false;
  String? _errorMessage;

  List<Order> get orders => List.unmodifiable(_orders);
  List<Map<String, dynamic>> get paymentMethods => List.unmodifiable(_paymentMethods);
  bool get isMultiVendor => _isMultiVendor;
  String get vendorName => _vendorName;
  Order? get selectedOrder => _selectedOrder;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  OrderProvider({ApiClient? apiClient}) : _apiClient = apiClient ?? ApiClient();

  Future<List<Map<String, dynamic>>> fetchPaymentMethods({List<Map<String, dynamic>>? items}) async {
    try {
      final dynamic response;
      if (items != null && items.isNotEmpty) {
        response = await _apiClient.post(ApiConfig.paymentMethods, body: {'items': items});
      } else {
        response = await _apiClient.get(ApiConfig.paymentMethods);
      }
      if (response is Map<String, dynamic>) {
        if (response.containsKey('is_multivendor')) {
          _isMultiVendor = response['is_multivendor'] == true;
        }
        if (response.containsKey('vendor_name')) {
          _vendorName = response['vendor_name']?.toString() ?? 'Openbox Platform';
        }
        if (response.containsKey('payment_methods')) {
          final list = response['payment_methods'] as List;
          _paymentMethods = list.map((m) => Map<String, dynamic>.from(m as Map)).toList();
        }
        notifyListeners();
        return _paymentMethods;
      }
    } catch (_) {}
    return _paymentMethods;
  }

  Future<void> fetchOrders() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiClient.get(ApiConfig.orders);
      if (response is Map<String, dynamic> && response.containsKey('data')) {
        final list = response['data'] as List;
        _orders = list.map((item) => _parseOrderFromJson(item)).toList();
      }
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<Order?> fetchOrderDetail(int orderId) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiClient.get(ApiConfig.orderDetail(orderId));
      if (response is Map<String, dynamic> && response.containsKey('data')) {
        _selectedOrder = _parseOrderFromJson(response['data']);
        return _selectedOrder;
      }
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
    return null;
  }

  Future<Map<String, dynamic>?> placeOrder({
    int? addressId,
    Map<String, dynamic>? addressData,
    required String paymentMethod,
    List<Map<String, dynamic>>? items,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final body = <String, dynamic>{
        'payment_method': paymentMethod,
      };
      if (addressId != null) {
        body['address_id'] = addressId;
      } else if (addressData != null) {
        body['address'] = addressData;
      }
      if (items != null && items.isNotEmpty) {
        body['items'] = items;
      }

      final response = await _apiClient.post(
        ApiConfig.checkout,
        body: body,
      );

      _isLoading = false;
      notifyListeners();
      return response as Map<String, dynamic>?;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
      rethrow;
    }
  }

  Future<bool> confirmStripePayment(dynamic orderId, {String? token}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final body = <String, dynamic>{};
      if (token != null && token.isNotEmpty) {
        body['token'] = token;
      }

      final response = await _apiClient.post(
        ApiConfig.stripeConfirm(orderId),
        body: body,
      );

      _isLoading = false;
      notifyListeners();
      return response != null &&
          (response['success'] == true || response['order'] != null);
    } catch (e) {
      debugPrint('Error confirming Stripe payment via API: $e');
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> submitPaymentProof({
    required int vendorOrderId,
    required File proofFile,
    String? notes,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final fields = <String, String>{};
      if (notes != null && notes.isNotEmpty) {
        fields['notes'] = notes;
      }

      await _apiClient.postMultipart(
        ApiConfig.submitPaymentProof(vendorOrderId),
        file: proofFile,
        fileParamName: 'proof',
        fields: fields,
      );

      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> requestRefund({
    required int vendorOrderId,
    required String reason,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await _apiClient.post(
        ApiConfig.requestRefund(vendorOrderId),
        body: {'reason': reason},
      );

      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Order _parseOrderFromJson(Map<String, dynamic> json) {
    final statusStr = json['status']?.toString() ?? 'confirmed';
    final status = switch (statusStr) {
      'processing' => OrderStatus.processing,
      'packed' => OrderStatus.packed,
      'shipped' => OrderStatus.shipped,
      'out_for_delivery' => OrderStatus.outForDelivery,
      'delivered' => OrderStatus.delivered,
      'cancelled' => OrderStatus.cancelled,
      _ => OrderStatus.confirmed,
    };

    final items = <OrderItem>[];
    if (json.containsKey('vendor_orders') && json['vendor_orders'] is List) {
      for (final vo in json['vendor_orders']) {
        final sellerName = vo['vendor'] != null ? vo['vendor']['name'] ?? 'Vendor' : 'Vendor';
        if (vo.containsKey('items') && vo['items'] is List) {
          for (final item in vo['items']) {
            items.add(OrderItem(
              productTitle: item['title'] ?? item['product_name'] ?? 'Product',
              imageUrl: item['image_url'] ?? 'https://via.placeholder.com/150',
              price: (item['price'] is num) ? (item['price'] as num).toDouble() : 0.0,
              quantity: (item['quantity'] is num) ? (item['quantity'] as num).toInt() : 1,
              sellerName: sellerName,
            ));
          }
        }
      }
    }

    return Order(
      id: json['order_number'] ?? json['id']?.toString() ?? '#ORD',
      placedAt: json['created_at'] != null ? DateTime.tryParse(json['created_at']) ?? DateTime.now() : DateTime.now(),
      status: status,
      total: (json['total'] is num) ? (json['total'] as num).toDouble() : 0.0,
      items: items,
    );
  }
}
