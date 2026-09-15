<?php

namespace App\Http\Requests;

use App\Models\SupportTicket;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:150'],
            'category' => ['required', Rule::in(SupportTicket::CATEGORIES)],
            'body' => ['required', 'string', 'max:2000'],
        ];
    }
}
