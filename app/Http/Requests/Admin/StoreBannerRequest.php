<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBannerRequest extends FormRequest
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
        $isUpdate = $this->route('banner') !== null || $this->isMethod('PUT') || $this->isMethod('PATCH');
        $hasVideo = $this->hasFile('video');

        return [
            'title' => ['required', 'string', 'max:150'],
            'image' => [($isUpdate || $hasVideo) ? 'nullable' : 'required', 'nullable', 'file', 'image', 'max:20480'],
            'video' => ['nullable', 'file', 'max:512000'],
            'remove_image' => ['nullable', 'boolean'],
            'remove_video' => ['nullable', 'boolean'],
            'link' => ['nullable', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }
}
