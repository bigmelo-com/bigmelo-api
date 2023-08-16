<?php

namespace App\Http\Requests\Dealership;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDealershipRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }
}
