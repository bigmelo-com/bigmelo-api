<?php

namespace App\Http\Resources\Chat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ChatCollection extends ResourceCollection
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
                    'id'                => $user->id,
                    'name'              => $user->name,
                    'last_name'         => $user->last_name,
                    'country_code'      => $user->country_code,
                    'phone_number'      => $user->phone_number,
                    'full_phone_number' => $user->full_phone_number,
                    'email'             => $user->email,
                    'total_messages'    => [
                        'admin'     => $user->messages->where('source', 'Admin')->count(),
                        'chat_gpt'  => $user->messages->where('source', 'ChatGPT')->count(),
                        'api'       => $user->messages->where('source', 'API')->count(),
                        'whatsapp'  => $user->messages->where('source', 'WhatsApp')->count(),
                        'total'     => $user->messages->count()
                    ],
                ];
            }, $this->all())
        ];
    }
}
