<?php

namespace App\Http\Requests\Project;

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
            'name'          => 'nullable|string|max:255',
            'description'   => 'nullable|string|max:500',
            'price'         => 'nullable|numeric',
            'message_limit' => 'nullable|numeric',
            'period'        => 'nullable|string|min:14'
        ];
    }
}
