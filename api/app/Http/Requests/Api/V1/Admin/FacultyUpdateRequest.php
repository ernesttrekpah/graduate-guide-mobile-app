<?php

// app/Http/Requests/Api/V1/Admin/FacultyUpdateRequest.php
namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FacultyUpdateRequest extends FormRequest
{public function authorize(): bool
    {
    // Route middleware ('role:admin') already enforced
    return true;
}
    public function rules(): array
    {
        return [
            'institution_id' => ['sometimes', 'integer', 'exists:institutions,id'],
            'name'           => ['sometimes', 'string', 'max:255'],
        ];
    }}
