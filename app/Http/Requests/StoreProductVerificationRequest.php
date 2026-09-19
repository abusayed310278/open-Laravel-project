<?php

namespace App\Http\Requests;

use App\Enums\VerificationStatus;
use App\Models\ProductVerification;
use App\Services\ProductVerificationService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductVerificationRequest extends FormRequest
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
        $validSlots = array_keys(ProductVerificationService::get30MinTimeSlots());

        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'location_id' => ['required', 'integer', 'exists:verification_locations,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => [
                'required',
                Rule::in($validSlots),
                function ($attribute, $value, $fail) {
                    $locationId = $this->input('location_id');
                    $date = $this->input('appointment_date');
                    if ($locationId && $date) {
                        $dateTimeStr = "{$date} {$value}:00";
                        $alreadyBooked = ProductVerification::where('location_id', $locationId)
                            ->where('scheduled_at', $dateTimeStr)
                            ->whereIn('status', [VerificationStatus::Scheduled, VerificationStatus::Inspecting])
                            ->exists();

                        if ($alreadyBooked) {
                            $fail('The selected 30-minute physical scrutiny time slot is already booked at this location. Please choose another 30-minute time slot or date.');
                        }
                    }
                },
            ],
        ];
    }
}
