import 'package:flutter/foundation.dart';
import '../core/network/api_client.dart';
import '../core/network/api_config.dart';
import '../data/models/category.dart';
import '../data/models/product.dart';
import '../data/models/seller.dart';

class ProductProvider extends ChangeNotifier {
  final ApiClient _apiClient;

  List<Product> _products = [];
  List<ProductCategory> _categories = [];
  Product? _selectedProduct;
  bool _isLoading = false;
  String? _errorMessage;

  List<Product> get products => List.unmodifiable(_products);
  List<ProductCategory> get categories => List.unmodifiable(_categories);
  Product? get selectedProduct => _selectedProduct;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  ProductProvider({ApiClient? apiClient}) : _apiClient = apiClient ?? ApiClient() {
    fetchCategories();
    fetchProducts();
  }

  Future<void> fetchProducts({
    String? search,
    String? categoryId,
    String? brandId,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final queryParams = <String>[];
      if (search != null && search.isNotEmpty) {
        queryParams.add('search=${Uri.encodeComponent(search)}');
      }
      if (categoryId != null && categoryId.isNotEmpty) {
        queryParams.add('category_id=$categoryId');
      }
      if (brandId != null && brandId.isNotEmpty) {
        queryParams.add('brand_id=$brandId');
      }

      final queryString = queryParams.isNotEmpty ? '?${queryParams.join('&')}' : '';
      final url = '${ApiConfig.products}$queryString';

      final response = await _apiClient.get(url);

      if (response is Map<String, dynamic> && response.containsKey('data')) {
        final list = response['data'] as List;
        _products = list.map((item) => Product.fromJson(item)).toList();
      }
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<Product?> fetchProductBySlug(String slug) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiClient.get(ApiConfig.productDetail(slug));
      if (response is Map<String, dynamic> && response.containsKey('data')) {
        _selectedProduct = Product.fromJson(response['data']);
        return _selectedProduct;
      }
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
    return null;
  }

  Future<void> fetchCategories() async {
    try {
      final response = await _apiClient.get(ApiConfig.categories);
      if (response is Map<String, dynamic> && response.containsKey('data')) {
        final list = response['data'] as List;
        _categories = list.map((item) => ProductCategory.fromJson(item)).toList();
        notifyListeners();
      }
    } catch (_) {}
  }

  Future<List<Product>> fetchProductsBySeller(String sellerId) async {
    try {
      final response = await _apiClient.get('${ApiConfig.products}?seller_id=$sellerId');
      if (response is Map<String, dynamic> && response.containsKey('data')) {
        final list = response['data'] as List;
        return list.map((item) => Product.fromJson(item)).toList();
      }
    } catch (_) {}
    return [];
  }

  Future<Seller?> fetchSellerDetail(String sellerId) async {
    try {
      final response = await _apiClient.get(ApiConfig.sellerDetail(sellerId));
      if (response is Map<String, dynamic> && response.containsKey('seller')) {
        return Seller.fromJson(response['seller']);
      }
    } catch (_) {}
    return null;
  }
}
