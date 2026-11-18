import 'package:flutter/foundation.dart';
import '../core/network/api_client.dart';
import '../data/models/customer_order.dart';
import '../data/models/vendor_order.dart';

enum OrderViewMode { purchases, sales }

class SellerOrderProvider extends ChangeNotifier {
  final ApiClient _apiClient;

  bool _isLoading = false;
  bool _isDetailLoading = false;
  bool _isCustomerLoading = false;
  String? _errorMessage;

  OrderViewMode _viewMode = OrderViewMode.purchases;

  List<CustomerOrder> _customerOrders = [];
  List<VendorOrderSummary> _orders = [];
  Map<String, int> _counts = {};
  String _selectedStatus = 'all';
  String _searchQuery = '';

  VendorOrderDetail? _selectedOrderDetail;

  bool get isLoading => _isLoading || _isCustomerLoading;
  bool get isDetailLoading => _isDetailLoading;
  bool get isCustomerLoading => _isCustomerLoading;
  String? get errorMessage => _errorMessage;

  OrderViewMode get viewMode => _viewMode;
  List<CustomerOrder> get customerOrders => _customerOrders;
  List<VendorOrderSummary> get orders => _orders;
  Map<String, int> get counts => _counts;
  String get selectedStatus => _selectedStatus;
  String get searchQuery => _searchQuery;
  VendorOrderDetail? get selectedOrderDetail => _selectedOrderDetail;

  SellerOrderProvider({ApiClient? apiClient}) : _apiClient = apiClient ?? ApiClient();

  void setViewMode(OrderViewMode mode) {
    if (_viewMode == mode) return;
    _viewMode = mode;
    notifyListeners();
  }

  Future<void> fetchCustomerOrders() async {
    _isCustomerLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiClient.get('/customer-orders');
      if (response is Map<String, dynamic> && response.containsKey('data')) {
        final list = response['data'] as List? ?? [];
        _customerOrders = list
            .map((e) => CustomerOrder.fromJson(e as Map<String, dynamic>))
            .toList();
      }
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _isCustomerLoading = false;
      notifyListeners();
    }
  }

  Future<void> fetchAll() async {
    await Future.wait([
      fetchCustomerOrders(),
      fetchOrders(),
    ]);
  }

  Future<void> fetchOrders({String? status, String? search}) async {
    if (status != null) {
      _selectedStatus = status;
    }
    if (search != null) {
      _searchQuery = search;
    }

    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final queryParams = <String, dynamic>{};
      if (_selectedStatus != 'all') {
        queryParams['status'] = _selectedStatus;
      }
      if (_searchQuery.trim().isNotEmpty) {
        queryParams['search'] = _searchQuery.trim();
      }

      final response = await _apiClient.get('/orders', queryParams: queryParams);

      if (response is Map<String, dynamic>) {
        if (response.containsKey('orders') && response['orders'] is List) {
          _orders = (response['orders'] as List)
              .map((e) => VendorOrderSummary.fromJson(e as Map<String, dynamic>))
              .toList();
        }

        if (response.containsKey('counts') && response['counts'] is Map<String, dynamic>) {
          _counts = (response['counts'] as Map<String, dynamic>).map(
            (k, v) => MapEntry(k, v is int ? v : int.tryParse(v.toString()) ?? 0),
          );
        }
      }
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  void setStatus(String status) {
    if (_selectedStatus == status) return;
    _selectedStatus = status;
    fetchOrders();
  }

  void searchOrders(String query) {
    _searchQuery = query;
    fetchOrders();
  }

  Future<void> fetchOrderDetail(int vendorOrderId) async {
    _isDetailLoading = true;
    _selectedOrderDetail = null;
    notifyListeners();

    try {
      final response = await _apiClient.get('/orders/$vendorOrderId');
      if (response is Map<String, dynamic> && response.containsKey('order')) {
        _selectedOrderDetail = VendorOrderDetail.fromJson(response['order'] as Map<String, dynamic>);
      }
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _isDetailLoading = false;
      notifyListeners();
    }
  }

  Future<bool> updateOrderStatus(int vendorOrderId, String status, {String? trackingNumber}) async {
    try {
      final body = <String, dynamic>{'status': status};
      if (trackingNumber != null && trackingNumber.trim().isNotEmpty) {
        body['tracking_number'] = trackingNumber.trim();
      }

      await _apiClient.post('/orders/$vendorOrderId/status', body: body);

      // Refresh current detail and orders list
      await fetchOrderDetail(vendorOrderId);
      await fetchOrders();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      notifyListeners();
      return false;
    }
  }
}
