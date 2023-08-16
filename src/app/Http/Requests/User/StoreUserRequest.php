<?php

namespace App\Http\Requests\User;

use App\Classes\Rules\UserPassword;
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
            'role'                     => 'required|string|in:admin',
            'name'                     => 'required|string|max:255',
            'last_name'                => 'required|string|max:255',
            'email'                    => 'required|string|email|unique:users',
            'password'                 =>  ['required', 'string', UserPassword::min(6)->mixedCase()->numbers()]
        ];
    }
}
