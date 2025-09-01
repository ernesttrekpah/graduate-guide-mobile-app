<?php
namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class FeedbackStoreRequest extends FormRequest
{
    public function authorize(): bool
    {return (bool) $this->user();}
    public function rules(): array
    {
        return [
            'rating_1_5' => ['required', 'integer', 'between:1,5'],
            'comment'    => ['nullable', 'string'],
        ];
    }
}
