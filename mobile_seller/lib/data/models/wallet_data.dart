class WalletBalance {
  final double availableBalance;
  final double pendingBalance;
  final double totalEarned;
  final double totalWithdrawn;
  final String currency;

  const WalletBalance({
    required this.availableBalance,
    required this.pendingBalance,
    required this.totalEarned,
    required this.totalWithdrawn,
    this.currency = 'QAR',
  });

  factory WalletBalance.fromJson(Map<String, dynamic> json) {
    return WalletBalance(
      availableBalance: (json['available_balance'] is num) ? (json['available_balance'] as num).toDouble() : 0.0,
      pendingBalance: (json['pending_balance'] is num) ? (json['pending_balance'] as num).toDouble() : 0.0,
      totalEarned: (json['total_earned'] is num) ? (json['total_earned'] as num).toDouble() : 0.0,
      totalWithdrawn: (json['total_withdrawn'] is num) ? (json['total_withdrawn'] as num).toDouble() : 0.0,
      currency: json['currency']?.toString() ?? 'QAR',
    );
  }
}

class PayoutMethodItem {
  final String value;
  final String label;

  const PayoutMethodItem({required this.value, required this.label});

  factory PayoutMethodItem.fromJson(Map<String, dynamic> json) {
    return PayoutMethodItem(
      value: json['value']?.toString() ?? '',
      label: json['label']?.toString() ?? '',
    );
  }
}

class PayoutRecord {
  final int id;
  final double amount;
  final String method;
  final String status;
  final String statusLabel;
  final String? notes;
  final String? createdAt;

  const PayoutRecord({
    required this.id,
    required this.amount,
    required this.method,
    required this.status,
    required this.statusLabel,
    this.notes,
    this.createdAt,
  });

  factory PayoutRecord.fromJson(Map<String, dynamic> json) {
    return PayoutRecord(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      amount: (json['amount'] is num) ? (json['amount'] as num).toDouble() : 0.0,
      method: json['method']?.toString() ?? '',
      status: json['status']?.toString() ?? 'requested',
      statusLabel: json['status_label']?.toString() ?? 'Requested',
      notes: json['notes']?.toString(),
      createdAt: json['created_at']?.toString(),
    );
  }
}

class TransactionRecord {
  final int id;
  final String type;
  final double amount;
  final String? description;
  final String? createdAt;

  const TransactionRecord({
    required this.id,
    required this.type,
    required this.amount,
    this.description,
    this.createdAt,
  });

  factory TransactionRecord.fromJson(Map<String, dynamic> json) {
    return TransactionRecord(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      type: json['type']?.toString() ?? 'credit',
      amount: (json['amount'] is num) ? (json['amount'] as num).toDouble() : 0.0,
      description: json['description']?.toString(),
      createdAt: json['created_at']?.toString(),
    );
  }
}
