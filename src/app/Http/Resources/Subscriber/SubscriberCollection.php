<?php

namespace App\Http\Resources\Subscriber;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SubscriberCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => array_map(function ($subscriber) {

                return [
                    'id'    => $subscriber->id,
                    'user'  => ['id' => $subscriber->user->id, 'name' => $subscriber->user->name],
                ];
            }, $this->all())
        ];
    }
}
