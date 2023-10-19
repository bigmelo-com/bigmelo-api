<?php

namespace App\Http\Resources\Organization;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
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
            'owner'         => [
                'id'    => $this->owner->id,
                'name'  => $this->owner->name
            ],
            'created_at'    => Carbon::parse($this->created_at)->toDateString()
        ];
    }
}
