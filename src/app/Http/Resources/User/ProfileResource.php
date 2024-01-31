<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $remaining_messages = $this->lead->remaining_messages;
        $message_limit = $this->lead->plan ? $this->lead->plan->message_limit : $this->lead->projects()->first()->message_limit;
        
        $used_messages = $message_limit - $remaining_messages;

        return [
            'first_name'            => $this->name,
            'last_name'             => $this->last_name,
            'phone_number'          => $this->phone_number,
            'email'                 => $this->email,
            'remaining_messages'    => $remaining_messages == -1 ? 'Ilimitado' : $remaining_messages,
            'message_limit'         => $message_limit == -1 ? 'Ilimitado' : $message_limit,
            'used_messages'         => $used_messages
        ];
    }
}
