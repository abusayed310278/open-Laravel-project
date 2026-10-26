class Address {
  const Address({
    required this.id,
    required this.label,
    required this.recipient,
    required this.line1,
    required this.city,
    required this.phone,
    this.isDefault = false,
  });

  final String id;
  final String label;
  final String recipient;
  final String line1;
  final String city;
  final String phone;
  final bool isDefault;
}
