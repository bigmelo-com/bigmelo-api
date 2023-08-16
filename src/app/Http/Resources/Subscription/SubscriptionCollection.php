<?php

namespace App\Http\Resources\Subscription;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SubscriptionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => array_map(function ($subscription) {

                return [
                    'id'         => $subscription->id,
                    'plan'       => ['id' => $subscription->plan->id, 'name' => $subscription->plan->name],
                    'subscriber' => ['id' => $subscription->subscriber->id, 'name' => $subscription->subscriber->name],
                    'start_date' => $subscription->start_date,
                    'end_date'   => $subscription->end_date,
                ];
            }, $this->all())
        ];
    }
}
