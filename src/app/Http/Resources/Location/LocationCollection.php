<?php

namespace App\Http\Resources\Location;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LocationCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => array_map(function ($location) {

                return [
                    'id'         => $location->id,
                    'dealership' => ['id' => $location->dealership->id, 'name' => $location->dealership->name],
                    'address'    => $location->address,
                    'city'       => $location->city,
                    'state'      => $location->state,
                    'zip'        => $location->zip
                ];
            }, $this->all())
        ];
    }
}
