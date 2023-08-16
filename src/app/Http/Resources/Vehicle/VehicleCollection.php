<?php

namespace App\Http\Resources\Vehicle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class VehicleCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => array_map(function ($vehicle) {
                return [
                    'id'         => $vehicle->id,
                    'dealership' => $vehicle->dealership ?
                                    ['id' => $vehicle->dealership->id, 'name' => $vehicle->dealership->name] :
                                    null,
                    'vin'        => $vehicle->vin,
                    'make'       => $vehicle->make,
                    'model'      => $vehicle->model,
                    'year'       => $vehicle->year,
                    'color'      => $vehicle->color,
                    'mileage'    => $vehicle->mileage
                ];
            }, $this->all())
        ];
    }
}
