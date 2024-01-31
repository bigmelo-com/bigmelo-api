<?php

namespace App\Http\Resources\Plan;

use Carbon\Carbon;
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
            'id'            => $this->id,
            'name'          => $this->name,
            'description'   => $this->description,
            'price'         => $this->price,
            'message_limit' => $this->message_limit,
            'period'        => $this->period,
            'created_at'    => Carbon::parse($this->created_at)->toDateString()
        ];
    }
}
