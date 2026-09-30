import 'package:flutter/foundation.dart';
import '../core/network/api_client.dart';
import '../core/network/api_config.dart';
import '../data/mock/mock_data.dart';
import '../data/models/address.dart';

class AddressProvider extends ChangeNotifier {
  final ApiClient _apiClient;

  List<Address> _addresses = List.from(MockData.addresses);
  bool _isLoading = false;
  String? _errorMessage;

  List<Address> get addresses => List.unmodifiable(_addresses);
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  AddressProvider({ApiClient? apiClient}) : _apiClient = apiClient ?? ApiClient();

  Address? get defaultAddress {
    if (_addresses.isEmpty) return null;
    return _addresses.firstWhere(
      (a) => a.isDefault,
      orElse: () => _addresses.first,
    );
  }

  void syncWithUser(String userName, String? userPhone) {
    if (_addresses.isEmpty && userName.trim().isNotEmpty) {
      _addresses.add(Address(
        id: 'addr_default',
        label: 'Home',
        recipient: userName,
        line1: 'Zone 45, Street 12, Villa 7',
        city: 'Doha, Qatar',
        phone: (userPhone != null && userPhone.trim().isNotEmpty) ? userPhone.trim() : '+974 5555 1234',
        isDefault: true,
      ));
      notifyListeners();
    }
  }

  Future<void> fetchAddresses() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiClient.get(ApiConfig.addresses);
      if (response is Map<String, dynamic> && response.containsKey('addresses')) {
        final list = response['addresses'] as List;
        final fetched = list.map((item) {
          return Address(
            id: item['id']?.toString() ?? '',
            label: item['label']?.toString() ?? 'Home',
            recipient: item['recipient']?.toString() ?? '',
            line1: item['line1']?.toString() ?? '',
            city: item['city']?.toString() ?? '',
            phone: item['phone']?.toString() ?? '',
            isDefault: item['is_default'] == true,
          );
        }).toList();
        if (fetched.isNotEmpty) {
          _addresses = fetched;
        }
      }
    } catch (_) {
      // Retain existing addresses if unauthenticated or offline
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> addAddress({
    required String label,
    required String recipient,
    required String line1,
    required String city,
    required String phone,
    bool isDefault = false,
  }) async {
    _isLoading = true;
    notifyListeners();

    try {
      final response = await _apiClient.post(
        ApiConfig.addresses,
        body: {
          'label': label,
          'recipient': recipient,
          'line1': line1,
          'city': city,
          'phone': phone,
          'is_default': isDefault,
        },
      );
      if (response is Map<String, dynamic> && response.containsKey('address')) {
        await fetchAddresses();
        return;
      }
    } catch (_) {}

    // Fallback local addition if offline
    final newId = 'addr_${DateTime.now().millisecondsSinceEpoch}';
    if (isDefault) {
      _addresses = _addresses.map((a) => Address(
        id: a.id,
        label: a.label,
        recipient: a.recipient,
        line1: a.line1,
        city: a.city,
        phone: a.phone,
        isDefault: false,
      )).toList();
    }

    final newAddr = Address(
      id: newId,
      label: label.isEmpty ? 'Address' : label,
      recipient: recipient,
      line1: line1,
      city: city,
      phone: phone,
      isDefault: isDefault || _addresses.isEmpty,
    );

    _addresses.add(newAddr);
    _isLoading = false;
    notifyListeners();
  }

  Future<void> updateAddress({
    required String id,
    required String label,
    required String recipient,
    required String line1,
    required String city,
    required String phone,
    bool isDefault = false,
  }) async {
    _isLoading = true;
    notifyListeners();

    if (isDefault) {
      _addresses = _addresses.map((a) => Address(
        id: a.id,
        label: a.label,
        recipient: a.recipient,
        line1: a.line1,
        city: a.city,
        phone: a.phone,
        isDefault: false,
      )).toList();
    }

    final index = _addresses.indexWhere((a) => a.id == id);
    if (index != -1) {
      _addresses[index] = Address(
        id: id,
        label: label.isEmpty ? 'Address' : label,
        recipient: recipient,
        line1: line1,
        city: city,
        phone: phone,
        isDefault: isDefault || (_addresses.length == 1),
      );
    }

    _isLoading = false;
    notifyListeners();

    final parsedId = int.tryParse(id);
    if (parsedId != null) {
      try {
        await _apiClient.put(
          ApiConfig.addressDetail(parsedId),
          body: {
            'label': label,
            'recipient': recipient,
            'line1': line1,
            'city': city,
            'phone': phone,
            'is_default': isDefault,
          },
        );
      } catch (_) {}
    }
  }

  Future<void> setDefault(String id) async {
    _addresses = _addresses.map((a) => Address(
      id: a.id,
      label: a.label,
      recipient: a.recipient,
      line1: a.line1,
      city: a.city,
      phone: a.phone,
      isDefault: a.id == id,
    )).toList();
    notifyListeners();

    final parsedId = int.tryParse(id);
    if (parsedId != null) {
      try {
        await _apiClient.post(ApiConfig.setDefaultAddress(parsedId));
      } catch (_) {}
    }
  }

  Future<void> deleteAddress(String id) async {
    _addresses.removeWhere((a) => a.id == id);
    if (_addresses.isNotEmpty && !_addresses.any((a) => a.isDefault)) {
      _addresses[0] = Address(
        id: _addresses[0].id,
        label: _addresses[0].label,
        recipient: _addresses[0].recipient,
        line1: _addresses[0].line1,
        city: _addresses[0].city,
        phone: _addresses[0].phone,
        isDefault: true,
      );
    }
    notifyListeners();

    final parsedId = int.tryParse(id);
    if (parsedId != null) {
      try {
        await _apiClient.delete(ApiConfig.addressDetail(parsedId));
      } catch (_) {}
    }
  }
}
