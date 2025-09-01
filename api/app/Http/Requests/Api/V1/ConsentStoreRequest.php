<?php
namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ConsentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {return (bool) $this->user();}
    public function rules(): array
    {
        return ['policy_version' => ['required', 'string', 'max:100']];
    }
}
