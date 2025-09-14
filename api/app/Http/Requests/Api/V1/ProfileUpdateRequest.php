<?php
namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    // Allow both "school_name" and legacy "school"
    protected function prepareForValidation(): void
    {
        if ($this->has('school') && ! $this->has('school_name')) {
            $this->merge(['school_name' => $this->input('school')]);
        }
        // normalize gender to lowercase
        if ($this->has('gender') && is_string($this->gender)) {
            $this->merge(['gender' => strtolower($this->gender)]);
        }
    }

    public function rules(): array
    {
        return [
            'full_name'       => ['nullable', 'string', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:30'],
            'gender'          => ['nullable', 'in:male,female,other'],
            'graduation_year' => ['nullable', 'integer', 'min:1990', 'max:' . (int) date('Y')],
            'school_name'     => ['nullable', 'string', 'max:255'],
            'region'          => ['nullable', 'string', 'max:255'],
            'meta'            => ['nullable', 'array'],
        ];
    }
}
