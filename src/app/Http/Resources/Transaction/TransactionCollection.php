<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TransactionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => array_map(function ($transaction) {
                return [
                    'id'           => $transaction->id,
                    'amount'       => $transaction->amount,
                    'status'       => $transaction->status,
                    'subscription' => $transaction->subscription ? [
                        'id'                => $transaction->subscription->id,
                        'dealership_id'     => $transaction->subscription->plan->dealership->id,
                        'dealership_name'   => $transaction->subscription->plan->dealership->name,
                        'plan_id'           => $transaction->subscription->plan->id,
                        'plan_name'         => $transaction->subscription->plan->name,
                        'plan_price'        => $transaction->subscription->plan->price
                    ] : null
                ];
            }, $this->all())
        ];
    }
}
