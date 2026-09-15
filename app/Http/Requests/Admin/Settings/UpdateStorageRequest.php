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
            'r2_access_key_id' => ['required', 'string', 'max:255'],
            'r2_secret_access_key' => ['nullable', 'string', 'max:255'],
            'r2_bucket' => ['required', 'string', 'max:255'],
            'r2_endpoint' => ['required', 'url', 'max:255'],
            'r2_url' => ['nullable', 'url', 'max:255'],
            'r2_region' => ['nullable', 'string', 'max:50'],
            'storage_disk' => ['required', Rule::in(['public', 'r2'])],
        ];
    }
}
