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
            'data' => array_map(function ($lead) {

                return [
                    'id'                => $lead->id,
                    'name'              => $lead->first_name,
                    'last_name'         => $lead->last_name,
                    'country_code'      => $lead->country_code,
                    'phone_number'      => $lead->phone_number,
                    'full_phone_number' => $lead->full_phone_number,
                    'email'             => $lead->email,
                    'total_messages'    => [
                        'admin'     => $lead->messages->where('source', 'Admin')->count(),
                        'chat_gpt'  => $lead->messages->where('source', 'ChatGPT')->count(),
                        'api'       => $lead->messages->where('source', 'API')->count(),
                        'whatsapp'  => $lead->messages->where('source', 'WhatsApp')->count(),
                        'total'     => $lead->messages->count()
                    ],
                ];
            }, $this->all())
        ];
    }
}
