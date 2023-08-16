<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'role'      => 'nullable|string|in:admin',
            'name'      => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'active'    => 'nullable|boolean',
        ];
    }
}
