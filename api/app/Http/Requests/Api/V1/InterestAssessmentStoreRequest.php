<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;



class InterestAssessmentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {return (bool) $this->user();}
    public function rules(): array
    {
        return [
            'instrument_version'    => ['nullable', 'string', 'max:50'],
            'answers'               => ['required', 'array', 'min:1'],
            'answers.*.question_id' => ['required', 'integer', 'exists:interest_questions,id'],
            'answers.*.value'       => ['required', 'integer', 'between:1,5'],
        ];
    }
}
