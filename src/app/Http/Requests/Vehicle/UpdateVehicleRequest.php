<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleRequest extends FormRequest
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
            'vin'           => 'nullable|string|min:3|max:40|unique:vehicles',
            'make'          => 'nullable|string|min:1|max:40',
            'model'         => 'nullable|string|min:1|max:40',
            'year'          => 'nullable|integer|min:1950|max:2999',
            'color'         => 'nullable|string|min:1|max:40',
            'mileage'       => 'nullable|numeric'
        ];
    }
}
