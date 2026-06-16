<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentClubLookupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'student';
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'admission_number' => ['required', 'string', 'max:8'],
            'club_code' => ['required', 'string', 'max:8'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'admission_number.required' => 'Enter the admission number your teacher gave you.',
            'club_code.required' => 'Enter your school club code.',
        ];
    }
}
