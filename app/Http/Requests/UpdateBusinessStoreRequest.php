<?php

namespace App\Http\Requests;

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
            if (!empty($socialLinks['instagram']) && is_string($socialLinks['instagram'])) {
                $val = trim($socialLinks['instagram']);
                if ($val !== '') {
                    if (str_starts_with($val, '@')) {
                        $val = substr($val, 1);
                    }
                    if (!preg_match('~^https?://~i', $val)) {
                        $socialLinks['instagram'] = str_contains($val, '/') ? 'https://' . $val : 'https://instagram.com/' . $val;
                    } else {
                        $socialLinks['instagram'] = $val;
                    }
                }
            }

            if (!empty($socialLinks['twitter']) && is_string($socialLinks['twitter'])) {
                $val = trim($socialLinks['twitter']);
                if ($val !== '') {
                    if (str_starts_with($val, '@')) {
                        $val = substr($val, 1);
                    }
                    if (!preg_match('~^https?://~i', $val)) {
                        $socialLinks['twitter'] = str_contains($val, '/') ? 'https://' . $val : 'https://x.com/' . $val;
                    } else {
                        $socialLinks['twitter'] = $val;
                    }
                }
            }

            $this->merge(['social_links' => $socialLinks]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'website' => ['nullable', 'url', 'max:255'],
            'social_links.instagram' => ['nullable', 'url', 'max:255'],
            'social_links.twitter' => ['nullable', 'url', 'max:255'],
            'social_links.whatsapp' => ['nullable', 'string', 'max:50'],
            'is_store_active' => ['boolean'],
        ];
    }
}
