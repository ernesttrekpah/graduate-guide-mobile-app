<?php
namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class SavedProgrammeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {return (bool) $this->user();}
    public function rules(): array
    {
        return [
            'programme_id' => ['required', 'integer', 'exists:programmes,id'],
            'note'         => ['nullable', 'string'],
        ];
    }
}
