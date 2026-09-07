<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\TanzaniaPhone;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
        $isStudent = $this->user()?->role === 'student';
        $isPinUser = $this->user()?->usesPinLogin() === true;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                $isPinUser ? 'nullable' : 'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone' => [
                Rule::requiredIf($isClientRole && ! $isPinUser),
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

        if ($isStudent) {
            $rules['date_of_birth'] = ['required', 'date_format:Y-m-d', 'before_or_equal:today'];
            $rules['gender'] = ['required', 'string', 'in:male,female'];
            $rules['school_class_id'] = ['nullable', 'exists:school_classes,id'];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->user()?->role !== 'student' || ! $this->filled('date_of_birth')) {
                return;
            }

            $age = Carbon::parse($this->input('date_of_birth'))->age;
            if ($age < 1 || $age > 150) {
                $validator->errors()->add('date_of_birth', 'Date of birth must result in age between 1 and 150.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $phone = $this->input('phone');
        $email = $this->input('email');

        $merge = [];
        if ($email === '') {
            $merge['email'] = null;
        }
        if ($phone === null || $phone === '') {
            if ($merge !== []) {
                $this->merge($merge);
            }

            return;
        }

        $normalizedPhone = TanzaniaPhone::normalize((string) $phone);
        if ($normalizedPhone) {
            $merge['phone'] = $normalizedPhone;
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }
}
