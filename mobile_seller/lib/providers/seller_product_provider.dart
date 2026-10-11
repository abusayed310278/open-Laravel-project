import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import '../core/network/api_client.dart';
import '../data/models/product_meta.dart';
import '../data/models/seller_product.dart';

class SellerProductProvider extends ChangeNotifier {
  final ApiClient _apiClient;

  bool _isLoading = false;
  bool _isDetailLoading = false;
  String? _errorMessage;

  List<SellerProduct> _products = [];
  Map<String, int> _counts = {};
  String _selectedStatus = 'all';
  String _searchQuery = '';

  SellerProductDetail? _selectedProductDetail;

  // Metadata for forms
  List<CategoryOption> _categories = [];
  List<BrandOption> _brands = [];
  List<SelectOption> _conditions = [];
  List<SelectOption> _shippingTypes = [];

  bool get isLoading => _isLoading;
  bool get isDetailLoading => _isDetailLoading;
  String? get errorMessage => _errorMessage;
  List<SellerProduct> get products => _products;
  Map<String, int> get counts => _counts;
  String get selectedStatus => _selectedStatus;
  String get searchQuery => _searchQuery;
  SellerProductDetail? get selectedProductDetail => _selectedProductDetail;

  List<CategoryOption> get categories => _categories;
  List<BrandOption> get brands => _brands;
  List<SelectOption> get conditions => _conditions;
  List<SelectOption> get shippingTypes => _shippingTypes;

  SellerProductProvider({ApiClient? apiClient}) : _apiClient = apiClient ?? ApiClient();

  Future<void> fetchProducts({String? status, String? search}) async {
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

      final response = await _apiClient.get('/products', queryParams: queryParams);

      if (response is Map<String, dynamic>) {
        if (response.containsKey('products') && response['products'] is List) {
          _products = (response['products'] as List)
              .map((e) => SellerProduct.fromJson(e as Map<String, dynamic>))
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
    fetchProducts();
  }

  void searchProducts(String query) {
    _searchQuery = query;
    fetchProducts();
  }

  Future<void> fetchProductMeta() async {
    try {
      final response = await _apiClient.get('/products/meta');
      if (response is Map<String, dynamic>) {
        if (response['categories'] is List) {
          _categories = (response['categories'] as List)
              .map((e) => CategoryOption.fromJson(e as Map<String, dynamic>))
              .toList();
        }
        if (response['brands'] is List) {
          _brands = (response['brands'] as List)
              .map((e) => BrandOption.fromJson(e as Map<String, dynamic>))
              .toList();
        }
        if (response['conditions'] is List) {
          _conditions = (response['conditions'] as List)
              .map((e) => SelectOption.fromJson(e as Map<String, dynamic>))
              .toList();
        }
        if (response['shipping_types'] is List) {
          _shippingTypes = (response['shipping_types'] as List)
              .map((e) => SelectOption.fromJson(e as Map<String, dynamic>))
              .toList();
        }
        notifyListeners();
      }
    } catch (_) {}
  }

  Future<void> fetchProductDetail(int productId) async {
    _isDetailLoading = true;
    _selectedProductDetail = null;
    notifyListeners();

    try {
      final response = await _apiClient.get('/products/$productId');
      if (response is Map<String, dynamic> && response.containsKey('product')) {
        _selectedProductDetail = SellerProductDetail.fromJson(response['product'] as Map<String, dynamic>);
      }
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _isDetailLoading = false;
      notifyListeners();
    }
  }

  Future<bool> togglePublish(int productId) async {
    try {
      final response = await _apiClient.patch('/products/$productId/toggle-publish');
      await fetchProducts();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      notifyListeners();
      return false;
    }
  }

  Future<bool> deleteProduct(int productId) async {
    try {
      await _apiClient.delete('/products/$productId');
      await fetchProducts();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      notifyListeners();
      return false;
    }
  }

  Future<bool> createProduct({
    required Map<String, String> fields,
    List<http.MultipartFile>? images,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiClient.multipart(
        '/products',
        method: 'POST',
        fields: fields,
        files: images,
      );

      await fetchProducts();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
