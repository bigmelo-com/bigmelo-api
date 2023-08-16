<?php

namespace App\Http\Requests\Location;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'dealership_id' => 'sometimes|int|exists:dealerships,id',
            'address'       => 'sometimes|string|max:255',
            'city'          => 'sometimes|string|max:255',
            'state'         => 'sometimes|string|max:2',
            'zip'           => 'nullable|string|max:10'
        ];
    }
}
