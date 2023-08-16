<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
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
            'vin'           => 'required|string|min:3|max:40|unique:vehicles',
            'make'          => 'required|string|min:1|max:40',
            'model'         => 'required|string|min:1|max:40',
            'year'          => 'required|integer|min:1950|max:2999',
            'color'         => 'required|string|min:1|max:40',
            'mileage'       => 'required|numeric'
        ];
    }
}
