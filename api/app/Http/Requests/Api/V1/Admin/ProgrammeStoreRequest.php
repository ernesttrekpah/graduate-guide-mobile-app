<?php

// app/Http/Requests/Api/V1/Admin/ProgrammeStoreRequest.php
namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProgrammeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route middleware ('role:admin') already enforced
        return true;
    }
    public function rules(): array
    {
        return [
            'faculty_id'                   => ['required', 'integer', 'exists:faculties,id'],
            'interest_area_id'             => ['nullable', 'integer', 'exists:interest_areas,id'],
            'name'                         => ['required', 'string', 'max:255'],
            'course_type'                  => ['required', 'in:Undergraduate,Diploma,Postgraduate'],
            'aggregate_cutoff'             => ['nullable', 'integer', 'between:6,60'],
            'additional_requirements_text' => ['nullable', 'string'],
            'flag_codes'                   => ['array'], // optional: ['APTITUDE_TEST',...]
            'flag_codes.*'                 => ['string'],
        ];
    }
}
