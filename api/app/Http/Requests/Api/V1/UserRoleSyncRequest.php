<?php
namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UserRoleSyncRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('admin');
    }
    public function rules(): array
    {
        return ['roles' => ['array', 'min:1'], 'roles.*' => ['string', 'distinct']];
    }
}
