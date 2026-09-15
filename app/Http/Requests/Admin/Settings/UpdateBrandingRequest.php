<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBrandingRequest extends FormRequest
{
    public const array FONTS = ['Montserrat', 'Inter', 'Roboto', 'Poppins', 'Outfit', 'Plus Jakarta Sans', 'Nunito Sans', 'Source Sans 3', 'Manrope', 'Work Sans'];

    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'logo' => ['nullable', 'image', 'max:1024'],
            'favicon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,ico,svg', 'max:256'],
            'brand_font' => ['nullable', Rule::in(self::FONTS)],
            'brand_color_primary' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ];
    }
}
