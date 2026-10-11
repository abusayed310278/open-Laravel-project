<?php

namespace App\Http\Requests;

use App\Support\SocialLinkNormalizer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isBusiness();
    }

    protected function prepareForValidation(): void
    {
        $website = trim((string) $this->input('website'));
        if ($website !== '' && !preg_match('~^https?://~i', $website)) {
            $this->merge(['website' => 'https://' . $website]);
        }

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
            'business_name' => ['required', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'logo' => ['nullable', 'image', 'max:5120'],
            'cover_image' => ['nullable', 'image', 'max:6144'],
            'avatar' => ['nullable', 'image', 'max:5120'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'website' => ['nullable', 'url', 'max:255'],
            'timezone' => ['nullable', 'timezone'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:255'],
            'iban' => ['nullable', 'string', 'max:255'],
            'swift_code' => ['nullable', 'string', 'max:255'],
            'bank_branch' => ['nullable', 'string', 'max:255'],
            'cod_enabled' => ['nullable', 'boolean'],
            'manual_bank_enabled' => ['nullable', 'boolean'],
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
