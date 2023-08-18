<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'message'   => 'required|string',
            'source'    => 'required|string|in:Admin,API',
            'user_id'   => 'required_if:source,Admin|numeric|exists:users,id'
        ];
    }
}
