<?php

namespace App\Http\Requests\Plan;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'dealership_id' => 'required|integer|exists:dealerships,id',
            'name'          => 'required|string|min:1|max:100',
            'price'         => 'required|numeric'
        ];
    }
}
