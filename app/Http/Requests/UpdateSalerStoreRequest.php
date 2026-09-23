<?php

namespace App\Http\Requests;

use App\Support\SocialLinkNormalizer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSalerStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isSaler();
    }

    protected function prepareForValidation(): void
    {
        $socialLinks = $this->input('social_links');
        if (is_array($socialLinks)) {
            $this->merge(['social_links' => SocialLinkNormalizer::prepare($socialLinks)]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'display_name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'timezone' => ['nullable', 'timezone'],
            'business_hours' => ['nullable', 'array'],
            'business_hours.*.enabled' => ['nullable', 'boolean'],
            'business_hours.*.open' => ['nullable', 'date_format:H:i'],
            'business_hours.*.close' => ['nullable', 'date_format:H:i'],
            'social_links' => ['nullable', 'array'],
            'social_links.*.platform' => ['nullable', 'string', 'max:100'],
            'social_links.*.url' => ['nullable', 'url', 'max:255'],
            'is_store_active' => ['boolean'],
        ];
    }
}
