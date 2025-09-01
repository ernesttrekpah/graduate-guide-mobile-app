<?php

// app/Http/Requests/Api/V1/Admin/InstitutionUpdateRequest.php
namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InstitutionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route middleware ('role:admin') already enforced
        return true;
    }
    public function rules(): array
    {
        return [
            'name'       => ['sometimes', 'string', 'max:255', Rule::unique('institutions', 'name')->ignore($this->institution->id)],
            'short_name' => ['sometimes', 'nullable', 'string', 'max:100'],
            'region'     => ['sometimes', 'nullable', 'string', 'max:100'],
            'website'    => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
