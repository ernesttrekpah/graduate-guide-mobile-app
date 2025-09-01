<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ExamResultsUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {return (bool) $this->user();}
    public function rules(): array
    {
        return [
            'exam_type'              => ['required', 'string', 'max:50'], // "WASSCE"
            'sitting_year'           => ['nullable', 'integer', 'min:1990', 'max:' . (int) date('Y')],
            'subjects'               => ['required', 'array', 'min:1'],
            'subjects.*.subject'     => ['required', 'string', 'max:100'], // name OR alias OR canonical code
            'subjects.*.grade_label' => ['required', 'string', 'max:3'],   // e.g., A1,B3,C6
        ];
    }
}
