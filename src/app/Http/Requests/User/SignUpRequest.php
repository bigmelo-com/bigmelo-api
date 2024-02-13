<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class SignUpRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules()
    {
        return [
            'name'              => 'required|string',
            'last_name'         => 'required|string',
            'email'             => 'required|string|email',
            'password'          => 'required|string|min:8|confirmed',
            'country_code'      => 'required|string',
            'phone_number'      => 'required|string',
            'full_phone_number' => 'required|string',
        ];
    }
}