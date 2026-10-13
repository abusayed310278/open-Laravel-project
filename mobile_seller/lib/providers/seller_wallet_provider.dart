import 'package:flutter/foundation.dart';
import '../core/network/api_client.dart';
import '../data/models/wallet_data.dart';

class SellerWalletProvider extends ChangeNotifier {
  final ApiClient _apiClient;

  bool _isLoading = false;
  bool _isSubmittingPayout = false;
  String? _errorMessage;

  WalletBalance? _balance;
  List<PayoutMethodItem> _payoutMethods = [];
  List<PayoutRecord> _payouts = [];
  List<TransactionRecord> _transactions = [];

  bool get isLoading => _isLoading;
  bool get isSubmittingPayout => _isSubmittingPayout;
  String? get errorMessage => _errorMessage;
  WalletBalance? get balance => _balance;
  List<PayoutMethodItem> get payoutMethods => _payoutMethods;
  List<PayoutRecord> get payouts => _payouts;
  List<TransactionRecord> get transactions => _transactions;

  SellerWalletProvider({ApiClient? apiClient}) : _apiClient = apiClient ?? ApiClient();

  Future<void> fetchWallet() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiClient.get('/wallet');

      if (response is Map<String, dynamic>) {
        if (response.containsKey('wallet') && response['wallet'] is Map<String, dynamic>) {
          _balance = WalletBalance.fromJson(response['wallet'] as Map<String, dynamic>);
        }

        if (response.containsKey('payout_methods') && response['payout_methods'] is List) {
          _payoutMethods = (response['payout_methods'] as List)
              .map((e) => PayoutMethodItem.fromJson(e as Map<String, dynamic>))
              .toList();
        }

        if (response.containsKey('payouts') && response['payouts'] is List) {
          _payouts = (response['payouts'] as List)
              .map((e) => PayoutRecord.fromJson(e as Map<String, dynamic>))
              .toList();
        }

        if (response.containsKey('transactions') && response['transactions'] is List) {
          _transactions = (response['transactions'] as List)
              .map((e) => TransactionRecord.fromJson(e as Map<String, dynamic>))
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

  Future<bool> requestPayout(double amount, String method) async {
    _isSubmittingPayout = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await _apiClient.post(
        '/wallet/payout-request',
        body: {
          'amount': amount,
          'method': method,
        },
      );

      await fetchWallet();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      return false;
    } finally {
      _isSubmittingPayout = false;
      notifyListeners();
    }
  }
}
