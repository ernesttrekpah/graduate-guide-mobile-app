<?php

// app/Http/Requests/Api/V1/Admin/FacultyStoreRequest.php
namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FacultyStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route middleware ('role:admin') already enforced
        return true;
    }
    public function rules(): array
    {
        return [
            'institution_id' => ['required', 'integer', 'exists:institutions,id'],
            'name'           => ['required', 'string', 'max:255'],
        ];
    }
}
