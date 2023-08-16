<?php

namespace App\Http\Resources\Dealership;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DealershipCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => array_map(function ($dealership) {
                return [
                    'id'        => $dealership->id,
                    'name'      => $dealership->name,
                ];
            }, $this->all())
        ];
    }
}
