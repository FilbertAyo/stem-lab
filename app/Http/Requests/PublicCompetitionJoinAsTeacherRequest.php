<?php

namespace App\Http\Requests;

use App\Models\User;
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
        });
    }
}
