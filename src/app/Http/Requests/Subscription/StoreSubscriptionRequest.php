<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'plan_id'       => 'required|int|exists:plans,id',
            'subscriber_id' => 'required|int|exists:users,id',
            'start_date'    => 'required|date_format:Y-m-d|before_or_equal:end_date',
            'end_date'      => 'required|date_format:Y-m-d|after_or_equal:start_date',
        ];
    }
}
