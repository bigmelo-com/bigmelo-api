<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'              => 'string|max:255',
            'last_name'         => 'string|max:255',
            'country_code'      => 'string|regex:/^\+\d{1,3}$/|max:4',
            'phone_number'      => 'string|regex:/^[0-9]+$/|max:15',
            'full_phone_number' => 'string|regex:/^\+\d+$/|max:255',
            'email'             => 'string|email|unique:users',
            'new_password'      => 'string|min:8|confirmed'
        ];
    }
}
