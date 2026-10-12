import 'package:flutter/foundation.dart';
import '../core/network/api_client.dart';
import '../data/models/dashboard_data.dart';

class SellerDashboardProvider extends ChangeNotifier {
  final ApiClient _apiClient;

  bool _isLoading = false;
  String? _errorMessage;
  int _selectedDays = 30;

  DashboardMetrics? _metrics;
  ChartData? _chartData;
  OrderCounts _orderCounts = const OrderCounts();
  List<TopProductItem> _topProducts = [];
  List<RecentOrderItem> _recentOrders = [];

  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  int get selectedDays => _selectedDays;

  DashboardMetrics? get metrics => _metrics;
  ChartData? get chartData => _chartData;
  OrderCounts get orderCounts => _orderCounts;
  List<TopProductItem> get topProducts => _topProducts;
  List<RecentOrderItem> get recentOrders => _recentOrders;

  SellerDashboardProvider({ApiClient? apiClient}) : _apiClient = apiClient ?? ApiClient();

  Future<void> fetchDashboard({int? days}) async {
    if (days != null) {
      _selectedDays = days;
    }

    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiClient.get(
        '/dashboard',
        queryParams: {'days': _selectedDays},
      );

      if (response is Map<String, dynamic>) {
        if (response.containsKey('metrics') && response['metrics'] is Map<String, dynamic>) {
          _metrics = DashboardMetrics.fromJson(response['metrics'] as Map<String, dynamic>);
        }

        if (response['order_counts'] is Map<String, dynamic>) {
          _orderCounts = OrderCounts.fromJson(response['order_counts'] as Map<String, dynamic>);
        }

        if (response.containsKey('chart') && response['chart'] is Map<String, dynamic>) {
          _chartData = ChartData.fromJson(response['chart'] as Map<String, dynamic>);
        }

        if (response.containsKey('top_products') && response['top_products'] is List) {
          _topProducts = (response['top_products'] as List)
              .map((e) => TopProductItem.fromJson(e as Map<String, dynamic>))
              .toList();
        }

        if (response.containsKey('recent_orders') && response['recent_orders'] is List) {
          _recentOrders = (response['recent_orders'] as List)
              .map((e) => RecentOrderItem.fromJson(e as Map<String, dynamic>))
              .toList();
        }
      }
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  void setDays(int days) {
    if (_selectedDays == days) return;
    fetchDashboard(days: days);
  }
}
