<?php

namespace App\Http\Requests\Verifier;

use App\Enums\ProductGrade;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isVerifier();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $decision = $this->input('decision');

        return [
            'decision' => ['required', Rule::in(['pass', 'fail'])],
            'results' => ['required', 'array'],
            'results.*' => ['required', Rule::in(['pass', 'fail', 'na'])],
            'grade' => [Rule::requiredIf($decision === 'pass'), Rule::in([ProductGrade::A->value, ProductGrade::B->value, ProductGrade::C->value])],
            'battery_health' => ['nullable', 'integer', 'between:0,100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'reason' => [Rule::requiredIf($decision === 'fail'), 'nullable', 'string', 'max:500'],
        ];
    }
}
