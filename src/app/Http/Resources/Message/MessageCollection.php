<?php

namespace App\Http\Resources\Message;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MessageCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => array_map(function ($message) {

                $result = [
                    'id'            => $message->id,
                    'user_id'       => $message->user_id,
                    'content'       => $message->content,
                    'source'        => $message->source,
                    'created_at'    => $message->created_at,
                    'updated_at'    => $message->updated_at
                ];

                if ($message->chatgpt_message) {
                    $result['chatgpt'] = [
                        'chatgpt_id'    => $message->chatgpt_message->chatgpt_id,
                        'model'         => $message->chatgpt_message->model,
                        'role'          => $message->chatgpt_message->role
                    ];
                }

                return $result;

            }, $this->all())
        ];
    }

}
