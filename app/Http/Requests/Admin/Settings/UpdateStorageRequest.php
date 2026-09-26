<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStorageRequest extends FormRequest
{
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
            'r2_access_key_id' => ['nullable', 'required_if:storage_disk,r2', 'string', 'max:255'],
            'r2_secret_access_key' => ['nullable', 'string', 'max:255'],
            'r2_bucket' => ['nullable', 'required_if:storage_disk,r2', 'string', 'max:255'],
            'r2_endpoint' => ['nullable', 'required_if:storage_disk,r2', 'url', 'max:255'],
            'r2_url' => ['nullable', 'url', 'max:255'],
            'r2_region' => ['nullable', 'string', 'max:50'],
            'cloudinary_cloud_name' => ['nullable', 'required_if:storage_disk,cloudinary', 'string', 'max:255'],
            'cloudinary_api_key' => ['nullable', 'required_if:storage_disk,cloudinary', 'string', 'max:255'],
            'cloudinary_api_secret' => ['nullable', 'string', 'max:255'],
            'cloudinary_url' => ['nullable', 'url', 'max:255'],
            'storage_disk' => ['required', Rule::in(['public', 'r2', 'cloudinary'])],
        ];
    }
}
