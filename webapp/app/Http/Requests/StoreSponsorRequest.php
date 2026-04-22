<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSponsorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'firstname'           => 'required|string|max:255',
            'lastname'            => 'required|string|max:255',
            'street'              => 'required|string|max:255',
            'housenumber'         => 'required|string|max:31',
            'postcode'            => 'required|string|size:5',
            'city'                => 'required|string|max:255',
            'phone'               => 'nullable|string|max:255',
            'email'               => 'nullable|email|max:255',
            'donation_per_lap'    => ['nullable', 'regex:/^\d+[,.]?\d{0,2}$/', $this->donationAmountRule()],
            'donation_static_max' => ['nullable', 'regex:/^\d+[,.]?\d{0,2}$/', $this->donationAmountRule()],
            'wants_newsletter'    => 'nullable|boolean',
            'ext_personnel_no'    => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'postcode.size' => __('Die Postleitzahl muss 5 Ziffern haben.'),
        ];
    }

    private function donationAmountRule(): ValidationRule
    {
        return new class implements ValidationRule
        {
            public function validate(string $attribute, mixed $value, Closure $fail): void
            {
                $perLap  = (float) str_replace(',', '.', (string) request('donation_per_lap', '0'));
                $staticMax = (float) str_replace(',', '.', (string) request('donation_static_max', '0'));

                if ($perLap <= 0 && $staticMax <= 0) {
                    $fail(__('Mindestens eine Spende (pro Runde oder Maximalbetrag) muss größer als 0 sein.'));
                }
            }
        };
    }
}
