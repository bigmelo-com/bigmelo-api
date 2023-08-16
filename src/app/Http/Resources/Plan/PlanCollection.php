<?php

namespace App\Http\Resources\Plan;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PlanCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => array_map(function ($plan) {
                return [
                    'id'         => $plan->id,
                    'dealership' => $plan->dealership ?
                        ['id' => $plan->dealership->id, 'name' => $plan->dealership->name] :
                        null,
                    'name'       => $plan->name,
                    'price'      => $plan->price,
                    'active'     => $plan->active
                ];
            }, $this->all())
        ];
    }
}
