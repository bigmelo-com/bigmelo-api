<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class RecoveryPasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' =>  'required|email',
        ];
    }
}
