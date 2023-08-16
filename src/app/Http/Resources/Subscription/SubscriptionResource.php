<?php

namespace App\Http\Resources\Subscription;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'plan'       => ['id' => $this->plan->id, 'name' => $this->plan->name],
            'subscriber' => ['id' => $this->subscriber->id, 'name' => $this->subscriber->name],
            'start_date' => $this->start_date,
            'end_date'   => $this->end_date
        ];
    }
}
