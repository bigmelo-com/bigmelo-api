<?php

namespace App\Http\Resources\Plan;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
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
            'dealership' => $this->dealership ?
                ['id' => $this->dealership->id, 'name' => $this->dealership->name] :
                null,
            'name'       => $this->name,
            'price'      => $this->price,
            'active'     => $this->active
        ];
    }
}
