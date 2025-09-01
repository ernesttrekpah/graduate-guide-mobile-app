<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {return (bool) $this->user();}
    public function rules(): array
    {
        return [
            'school'          => ['nullable', 'string', 'max:255'],
            'region'          => ['nullable', 'string', 'max:255'],
            'graduation_year' => ['nullable', 'integer', 'min:1990', 'max:' . (int) date('Y')],
            'meta'            => ['nullable', 'array'],
        ];
    }
}
