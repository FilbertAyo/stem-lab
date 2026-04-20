<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\TanzaniaPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isClientRole = in_array($this->user()?->role, ['teacher', 'student', 'parent'], true);

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone' => [
                Rule::requiredIf($isClientRole),
                'nullable',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    if (! TanzaniaPhone::normalize((string) $value)) {
                        $fail('Enter a valid Tanzania phone number (e.g. +255765890921 or 0765890921).');
                    }
                },
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $phone = $this->input('phone');
        if ($phone === null || $phone === '') {
            return;
        }

        $normalizedPhone = TanzaniaPhone::normalize((string) $phone);
        if ($normalizedPhone) {
            $this->merge(['phone' => $normalizedPhone]);
        }
    }
}
