class AttributeValueOption {
  final int id;
  final String value;

  const AttributeValueOption({required this.id, required this.value});

  factory AttributeValueOption.fromJson(Map<String, dynamic> json) {
    return AttributeValueOption(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      value: json['value']?.toString() ?? '',
    );
  }
}

class AttributeOption {
  final int id;
  final String name;
  final String type;
  final bool isRequired;
  final String? group;
  final String? unit;
  final String? placeholder;
  final List<AttributeValueOption> values;

  const AttributeOption({
    required this.id,
    required this.name,
    this.type = 'text',
    this.isRequired = false,
    this.group,
    this.unit,
    this.placeholder,
    this.values = const [],
  });

  bool get isSelect =>
      type == 'select' ||
      type == 'multi_select' ||
      type == 'color' ||
      values.isNotEmpty;

  bool get isBoolean => type == 'boolean';
  bool get isNumber => type == 'number';

  factory AttributeOption.fromJson(Map<String, dynamic> json) {
    final vals = (json['values'] as List?)
            ?.map((e) => AttributeValueOption.fromJson(e as Map<String, dynamic>))
            .toList() ??
        [];

    return AttributeOption(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      name: json['name']?.toString() ?? '',
      type: json['type']?.toString() ?? 'text',
      isRequired: json['is_required'] == true,
      group: json['group']?.toString() ?? 'General Specifications',
      unit: json['unit']?.toString(),
      placeholder: json['placeholder']?.toString(),
      values: vals,
    );
  }
}

class CategoryOption {
  final int id;
  final String name;
  final String? slug;
  final int? parentId;
  final List<AttributeOption> attributes;

  const CategoryOption({
    required this.id,
    required this.name,
    this.slug,
    this.parentId,
    this.attributes = const [],
  });

  factory CategoryOption.fromJson(Map<String, dynamic> json) {
    final attrs = (json['attributes'] as List?)
            ?.map((e) => AttributeOption.fromJson(e as Map<String, dynamic>))
            .toList() ??
        [];

    return CategoryOption(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      name: json['name']?.toString() ?? '',
      slug: json['slug']?.toString(),
      parentId: json['parent_id'] as int?,
      attributes: attrs,
    );
  }
}

class BrandOption {
  final int id;
  final String name;
  final String? slug;
  final String? logoUrl;

  const BrandOption({
    required this.id,
    required this.name,
    this.slug,
    this.logoUrl,
  });

  factory BrandOption.fromJson(Map<String, dynamic> json) {
    return BrandOption(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      name: json['name']?.toString() ?? '',
      slug: json['slug']?.toString(),
      logoUrl: json['logo_url']?.toString(),
    );
  }
}

class SelectOption {
  final String value;
  final String label;

  const SelectOption({required this.value, required this.label});

  factory SelectOption.fromJson(Map<String, dynamic> json) {
    return SelectOption(
      value: json['value']?.toString() ?? '',
      label: json['label']?.toString() ?? '',
    );
  }
}
