<?php

namespace App\Http\Resources\Vehicle;

use App\Models\Dealership;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
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
            'vin'        => $this->vin,
            'make'       => $this->make,
            'model'      => $this->model,
            'year'       => $this->year,
            'color'      => $this->color,
            'mileage'    => $this->mileage
        ];
    }
}
