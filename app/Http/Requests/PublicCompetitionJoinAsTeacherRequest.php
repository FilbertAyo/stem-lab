<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\TanzaniaPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublicCompetitionJoinAsTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'phone' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.min' => 'The password must be at least 8 characters.',
            'email.unique' => 'An account with this email already exists. Please log in to continue.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $normalizedPhone = TanzaniaPhone::normalize($this->input('phone'));
        if ($normalizedPhone) {
            $this->merge(['phone' => $normalizedPhone]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $password = (string) $this->input('password', '');

            if ($password === '') {
                return;
            }

            if (! preg_match('/[a-z]/', $password)) {
                $validator->errors()->add('password', 'The password must contain at least one lowercase letter.');
            }
            if (! preg_match('/[A-Z]/', $password)) {
                $validator->errors()->add('password', 'The password must contain at least one uppercase letter.');
            }
            if (! preg_match('/[0-9]/', $password)) {
                $validator->errors()->add('password', 'The password must contain at least one number.');
            }

            if (! TanzaniaPhone::normalize($this->input('phone'))) {
                $validator->errors()->add('phone', 'Enter a valid Tanzania phone number (e.g. +255765890921 or 0765890921).');
            }
        });
    }
}
