<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => array_map(function ($user) {
                return [
                    'id'        => $user->id,
                    'role'      => $user->role,
                    'name'      => $user->name,
                    'last_name' => $user->last_name,
                    'email'     => $user->email,
                    'active'    => (boolean)$user->active
                ];
            }, $this->all())
        ];
    }
}
