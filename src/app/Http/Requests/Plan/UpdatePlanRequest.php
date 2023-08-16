<?php

namespace App\Http\Requests\Plan;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'dealership_id' => 'nullable|integer|exists:dealerships,id',
            'name'          => 'nullable|string|min:1|max:100',
            'price'         => 'nullable|numeric',
            'active'        => 'nullable|boolean'
        ];
    }
}
