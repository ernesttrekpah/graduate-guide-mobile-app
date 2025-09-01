<?php

// app/Http/Requests/Api/V1/Admin/InstitutionStoreRequest.php
namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class InstitutionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route middleware ('role:admin') already enforced
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255', 'unique:institutions,name'],
            'short_name' => ['nullable', 'string', 'max:100'],
            'region'     => ['nullable', 'string', 'max:100'],
            'website'    => ['nullable', 'string', 'max:255'],
        ];
    }
}
