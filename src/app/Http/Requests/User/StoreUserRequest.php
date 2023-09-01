<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {

        return [
            'name'              => 'required|string|max:255',
            'last_name'         => 'string|max:255',
            'country_code'      => 'required|string|regex:/^\+\d{1,3}$/|max:4',
            'phone_number'      => 'required|string|regex:/^[0-9]+$/|max:15',
            'full_phone_number' => 'required|string|regex:/^\+\d+$/|max:255',
            'email'             => 'required|string|email|unique:users'
        ];
    }
}
