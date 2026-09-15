<?php

namespace App\Enums;

enum AttributeType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Select = 'select';
    case MultiSelect = 'multi_select';
    case Number = 'number';
    case Boolean = 'boolean';
    case Date = 'date';
    case Color = 'color';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Text',
            self::Textarea => 'Textarea',
            self::Select => 'Select (single)',
            self::MultiSelect => 'Select (multiple)',
            self::Number => 'Number',
            self::Boolean => 'Yes / No',
            self::Date => 'Date',
            self::Color => 'Color',
        };
    }

    /**
     * Whether this attribute type is backed by attribute_values (predefined
     * options) rather than a free-form value entered per product.
     */
    public function usesValueList(): bool
    {
        return in_array($this, [self::Select, self::MultiSelect, self::Color], strict: true);
    }
}
