<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'amount'       => $this->amount,
            'status'       => $this->status,
            'subscription' => $this->subscription ? [
                'id'                => $this->subscription->id,
                'dealership_id'     => $this->subscription->plan->dealership->id,
                'dealership_name'   => $this->subscription->plan->dealership->name,
                'plan_id'           => $this->subscription->plan->id,
                'plan_name'         => $this->subscription->plan->name,
                'plan_price'        => $this->subscription->plan->price
            ] : null
        ];
    }
}
