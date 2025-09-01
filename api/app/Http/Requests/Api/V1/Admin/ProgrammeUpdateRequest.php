<?php
// app/Http/Requests/Api/V1/Admin/ProgrammeUpdateRequest.php
namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProgrammeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route middleware ('role:admin') already enforced
        return true;
    }
    public function rules(): array
    {
        return [
            'faculty_id'                   => ['sometimes', 'integer', 'exists:faculties,id'],
            'interest_area_id'             => ['sometimes', 'nullable', 'integer', 'exists:interest_areas,id'],
            'name'                         => ['sometimes', 'string', 'max:255'],
            'course_type'                  => ['sometimes', 'in:Undergraduate,Diploma,Postgraduate'],
            'aggregate_cutoff'             => ['sometimes', 'nullable', 'integer', 'between:6,60'],
            'additional_requirements_text' => ['sometimes', 'nullable', 'string'],
            'flag_codes'                   => ['sometimes', 'array'],
            'flag_codes.*'                 => ['string'],
        ];
    }
}
